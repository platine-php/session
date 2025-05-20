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
 *  @file Configuration.php
 *
 *  The Session Configuration class
 *
 *  @package    Platine\Session
 *  @author Platine Developers Team
 *  @copyright  Copyright (c) 2020
 *  @license    http://opensource.org/licenses/MIT  MIT License
 *  @link   https://www.platine-php.com
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Session;

use Platine\Stdlib\Config\AbstractConfiguration;

/**
 * @class Configuration
 * @package Platine\Session
 */
class Configuration extends AbstractConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function getValidationRules(): array
    {
        return [
            'name' => 'string',
            'driver' => 'string',
            'ttl' => 'integer',
            'flash_key' => 'string',
            'cookie' => 'array',
            'cookie.lifetime' => 'integer',
            'cookie.path' => 'string',
            'cookie.domain' => 'string',
            'cookie.secure' => 'boolean',
            'storages' => 'array',
            'storages.file' => 'array',
            'storages.file.path' => 'string',
            'storages.file.prefix' => 'string',
            'storages.apcu' => 'array',
            'storages.null' => 'array',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault(): array
    {
        return [
            'name' => 'PHPSESSID',
            'driver' => 'file',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'file' => [
                    'path' => 'session',
                    'prefix' => 'sess_',
                ],
                'apcu' => [],
                'null' => [],
            ]
        ];
    }
}
