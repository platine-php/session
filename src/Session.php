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
 *  @file Session.php
 *
 *  The Session class used to manage the session
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

use Platine\Session\Configuration;
use Platine\Session\Storage\NullStorage;
use Platine\Stdlib\Helper\Arr;
use SessionHandlerInterface;

class Session
{
    /**
     * The session driver to use
     * @var SessionHandlerInterface
     */
    protected SessionHandlerInterface $handler;

    /**
     * The configuration instance
     * @var Configuration
     */
    protected Configuration $config;

    /**
     * Create new Session instance
     * @param SessionHandlerInterface|null $handler the handler to use
     * @param Configuration|null $config the configuration to use
     */
    public function __construct(
        ?SessionHandlerInterface $handler = null,
        ?Configuration $config = null
    ) {
        $this->config = $config ?? new Configuration([]);

        $this->handler = $handler ?? new NullStorage($config);

        if ((session_status() !== PHP_SESSION_ACTIVE)) {
            $this->init();
            session_set_save_handler($this->handler, true);

            //now start the session
            session_start();
        }
    }

    /**
     * Return the instance of session handler
     * @return SessionHandlerInterface
     */
    public function getHandler(): SessionHandlerInterface
    {
        return $this->handler;
    }

    /**
     * Check whether the session data for given key exists
     * @param  string  $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return Arr::has($_SESSION, $key);
    }

    /**
     * Set the session data
     * @param string $key   the key name
     * @param mixed $value the session data value
     */
    public function set(string $key, $value): void
    {
        Arr::set($_SESSION, $key, $value);
    }

    /**
     * Get the session data
     * @param string $key   the key name
     * @param mixed $default the default value to return if can
     *  not find session data
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($_SESSION, $key, $default);
    }

    /**
     * Return all session data
     * @param  bool $includeFlash whether to include flash data
     * @return array<string, mixed>
     */
    public function all(bool $includeFlash = false): array
    {
        $session = $_SESSION;
        $flashKey = $this->config->get('flash_key');
        if (!$includeFlash) {
            if (array_key_exists($flashKey, $session)) {
                unset($session[$flashKey]);
            }
        }

        return $session;
    }

    /**
     * Remove the session data for the given key
     * @param string $key   the key name
     *
     * @return bool
     */
    public function remove(string $key): bool
    {
        Arr::forget($_SESSION, $key);

        return true;
    }

    /**
     * Check whether the session flash data for given key exists
     * @param  string  $key
     * @return boolean
     */
    public function hasFlash(string $key): bool
    {
        $flashKey = $this->config->get('flash_key');
        $name = sprintf('%s.%s', $flashKey, $key);

        return $this->has($name);
    }

    /**
     * Get the session flash data
     * @param string $key   the key name
     * @param mixed $default the default value to return if can
     *  not find session data
     * @return mixed
     */
    public function getFlash(string $key, $default = null)
    {
        $flashKey = $this->config->get('flash_key');
        $name = sprintf('%s.%s', $flashKey, $key);

        $value = $default;
        if ($this->has($name)) {
            $value = $this->get($name);
            $this->removeFlash($key);
        }

        return $value;
    }

    /**
     * Set the session flash data
     * @param string $key   the key name
     * @param mixed $value the session data value
     */
    public function setFlash(string $key, $value): void
    {
        $flashKey = $this->config->get('flash_key');
        $name = sprintf('%s.%s', $flashKey, $key);

        $this->set($name, $value);
    }

    /**
     * Remove the session data for the given key
     * @param string $key   the key name
     *
     * @return bool
     */
    public function removeFlash(string $key): bool
    {
        $flashKey = $this->config->get('flash_key');
        $name = sprintf('%s.%s', $flashKey, $key);
        $this->remove($name);

        return true;
    }

    /**
     * Set the session information
     * @return void
     */
    protected function init(): void
    {
        $sessionName = $this->config->get('name');
        if ($sessionName) {
            session_name($sessionName);
        }

        $ttl = (int)$this->config->get('ttl');
        $lifetime = (int)$this->config->get('cookie.lifetime');
        $path = $this->config->get('cookie.path');
        $domain = $this->config->get('cookie.domain');
        $secure = $this->config->get('cookie.secure');

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => true, // for security for access to cookie via javascript or XSS attack
            'samesite' => 'Lax'
        ]);

        //to prevent attack of Session Fixation
        //thank to https://www.phparch.com/2018/01/php-sessions-in-depth/
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.gc_maxlifetime', $ttl);
    }
}
