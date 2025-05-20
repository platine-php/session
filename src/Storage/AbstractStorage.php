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
 *  @file AbstractStorage.php
 *
 *  The base storage class that contains the implementation of common features
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

use Platine\Session\Configuration;
use SessionHandlerInterface;
use SessionUpdateTimestampHandlerInterface;

/**
 * @class AbstractStorage
 * @package Platine\Session\Storage
 */
abstract class AbstractStorage implements SessionHandlerInterface, SessionUpdateTimestampHandlerInterface
{
    /**
     * The cache configuration
     * @var Configuration
     */
    protected Configuration $config;

    /**
     * Create new instance
     * @param Configuration|null $config
     */
    public function __construct(?Configuration $config = null)
    {
        $this->config = $config ?? new Configuration([]);
    }

    /**
     * {@inheritdoc}
     * @see SessionHandlerInterface
     */
    public function open(string $path, string $name): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @see SessionUpdateTimestampHandlerInterface
     */
    public function updateTimestamp(string $sid, string $data): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     * @see SessionUpdateTimestampHandlerInterface
     */
    public function validateId(string $sid): bool
    {
        return true;
    }
}
