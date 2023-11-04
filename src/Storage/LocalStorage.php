<?php

/**
 * Platine Session
 *
 * Platine Session is the lightweight implementation of php native
 * session handler interface
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2020 Platine Session
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 *  @file LocalStorage.php
 *
 *  The Cache Driver using file system to manage the cache data
 *
 *  @package    Platine\Session\Storage
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Session\Storage;

use Platine\Filesystem\DirectoryInterface;
use Platine\Filesystem\FileInterface;
use Platine\Filesystem\Filesystem;
use Platine\Session\Configuration;
use Platine\Session\Exception\FileSessionHandlerException;
use Platine\Stdlib\Helper\Path;
use Platine\Stdlib\Helper\Str;
use SessionHandlerInterface;

/**
 * Class LocalStorage
 * @package Platine\Session\Storage
 */
class LocalStorage extends AbstractStorage
{
     /**
     * The directory to use to save files
     * @var DirectoryInterface
     */
    protected DirectoryInterface $directory;

    /**
     * The file system instance
     * @var Filesystem
     */
    protected Filesystem $filesystem;

    /**
     * Create new instance
     * @param Filesystem $filesystem
     * @param Configuration $config
     * @throws FileSessionHandlerException
     */
    public function __construct(Filesystem $filesystem, ?Configuration $config = null)
    {
        parent::__construct($config);

        $path = $this->config->get('storages.file.path');
        $filePath = Path::normalizePath($path, true);
        $directory = $filesystem->directory($filePath);

        if (!$directory->exists()) {
            throw new FileSessionHandlerException(sprintf(
                'The directory [%s] does not exist',
                $directory->getPath()
            ));
        }

        $this->filesystem = $filesystem;
        $this->directory = $directory;
    }

    /**
     * {@inheritdoc}
     * @see SessionHandlerInterface
     */
    public function read($sid): string
    {
        $file = $this->getSessionFile($sid);

        if (!$file->exists()) {
            return '';
        }

        return $file->read();
    }

    /**
     * {@inheritdoc}
     * @see SessionHandlerInterface
     */
    public function write($sid, $data): bool
    {
        $file = $this->getSessionFile($sid);
        $file->write($data);

        /** @var int */
        $expireAt = time() + (int) $this->config->get('ttl');

        $file->touch($expireAt);

        return true;
    }

    /**
     * {@inheritdoc}
     * @see SessionHandlerInterface
     */
    public function destroy($sid): bool
    {
        $file = $this->getSessionFile($sid);

        if ($file->exists()) {
            $file->delete();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @see SessionHandlerInterface
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @see SessionHandlerInterface
     */
    public function gc($maxLifetime): bool
    {
        $files = $this->directory->read(DirectoryInterface::FILE);
        foreach ($files as /** @var FileInterface $file */ $file) {
            if (
                Str::startsWith(
                    $this->config->get('storages.file.prefix'),
                    $file->getName()
                )
            ) {
                if ($file->getMtime() + $maxLifetime < time()) {
                    $file->delete();
                }
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     * @see SessionUpdateTimestampHandlerInterface
     */
    public function updateTimestamp($sid, $data): bool
    {
        $file = $this->getSessionFile($sid);
        /** @var int */
        $expireAt = time() + (int) $this->config->get('ttl');

        $file->touch($expireAt);

        return true;
    }

    /**
     * {@inheritdoc}
     * @see SessionUpdateTimestampHandlerInterface
     */
    public function validateId($sid): bool
    {
        return $this->getSessionFile($sid)
                    ->exists();
    }

    /**
     * Return the file session
     * @param string $sid
     * @return FileInterface
     */
    protected function getSessionFile(string $sid): FileInterface
    {
        $filename = $this->getFileName($sid);
        $file = $this->filesystem->file(
            $this->directory->getPath() . $filename
        );

        return $file;
    }

    /**
     * Get session file name for given key
     * @param  string $sid
     * @return string      the filename
     */
    private function getFileName(string $sid): string
    {
        return sprintf('%s%s', $this->config->get('storages.file.prefix'), $sid);
    }
}
