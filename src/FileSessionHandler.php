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
 *  @file FileSessionHandler.php
 *
 *  The Session handler using file system to store session data
 *
 *  @package    Platine\Session
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Session;

use Platine\Session\Exception\FileSessionHandlerException;
use SessionHandlerInterface;

class FileSessionHandler implements SessionHandlerInterface
{

    /**
     * The path to use to save session files
     * @var string
     */
    protected string $savePath;

    /**
     * The session file prefix
     * @var string
     */
    protected string $filePrefix;

    /**
     * Create new instance
     * @param string $savePath the path to directory to save session files
     * @param string $filePrefix the session file prefix
     */
    public function __construct(string $savePath = '', string $filePrefix = 'sess_')
    {
        if (empty($savePath)) {
            $savePath = sys_get_temp_dir();
        }
        $this->setSavePath($savePath);
        $this->filePrefix = $filePrefix;
    }

    /**
     * {@inheritdoc}
     * @see SessionHandlerInterface
     */
    public function open($path, $name): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @see SessionHandlerInterface
     */
    public function read($sid): string
    {
        $file = sprintf('%s%s', $this->savePath, $this->getFileName($sid));
        if (file_exists($file)) {
            return (string) file_get_contents($file);
        }
        return '';
    }

    /**
     * {@inheritdoc}
     * @see SessionHandlerInterface
     */
    public function write($sid, $data): bool
    {
        return file_put_contents(
            sprintf(
                '%s%s',
                $this->savePath,
                $this->getFileName($sid)
            ),
            $data
        ) === false ? false : true;
    }

    /**
     * {@inheritdoc}
     * @see SessionHandlerInterface
     */
    public function destroy($sid): bool
    {
        $file = sprintf('%s%s', $this->savePath, $this->getFileName($sid));
        if (file_exists($file)) {
            return unlink($file);
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
        foreach (glob(sprintf('%s%s*', $this->savePath, $this->filePrefix)) as $file) {
            if (filemtime($file) + $maxLifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }
        return true;
    }

    /**
     * Set save path
     *
     * @param string $savePath
     *
     * @return self
     */
    public function setSavePath(string $savePath): self
    {
        if (!is_dir($savePath)) {
            throw new FileSessionHandlerException(sprintf(
                'Cannot use file session handler, because the directory %s does not exist',
                $savePath
            ));
        }
        if (!is_writable($savePath)) {
            throw new FileSessionHandlerException(sprintf(
                'Cannot use file session handler, because the directory %s is not writable',
                $savePath
            ));
        }
        $this->savePath = rtrim($savePath, '/\\') . DIRECTORY_SEPARATOR;

        return $this;
    }

    /**
     * Get save path
     * @return string
     */
    public function getSavePath(): string
    {
        return $this->savePath;
    }

    /**
     * Get session file name for given id
     * @param  string $sid
     * @return string      the filename
     */
    private function getFileName(string $sid): string
    {
        return $this->filePrefix . $sid;
    }
}
