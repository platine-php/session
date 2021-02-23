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
 *  @link   http://www.iacademy.cf
 *  @version 1.0.0
 *  @filesource
 */

declare(strict_types=1);

namespace Platine\Session;

use Platine\Session\Exception\SessionException;
use SessionHandlerInterface;

class Session
{

    /**
     * The session driver to use
     * @var SessionHandlerInterface
     */
    protected SessionHandlerInterface $handler;

    /**
     * The session flash key name to use
     * @var string
     */
    protected string $flashKey = 'session_flash';

    /**
     * Create new Session instance
     * @param SessionHandlerInterface $handler the session driver to use
     * @param string $flashKey the session flash key
     */
    public function __construct(SessionHandlerInterface $handler, string $flashKey = 'session_flash')
    {
        if (session_status() == PHP_SESSION_ACTIVE) {
            throw new SessionException('You don\'t need to manually start the session');
        }
        $this->handler = $handler;
        $this->flashKey = $flashKey;

        session_set_save_handler($handler);

        //now start the session
        session_start();
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
        return array_key_exists($key, $_SESSION);
    }

    /**
     * Set the session data
     * @param string $key   the key name
     * @param mixed $value the session data value
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get the session data
     * @param string $key   the key name
     * @param mixed $default the default value to return if can not find session data
     */
    public function get(string $key, $default = null)
    {
        return $this->has($key) ? $_SESSION[$key] : $default;
    }

    /**
     * Return all session data
     * @param  bool $includeFlash whether to include flash data
     * @return array
     */
    public function all(bool $includeFlash = false): array
    {
        $session = $_SESSION;
        if (!$includeFlash) {
            if (array_key_exists($this->flashKey, $session)) {
                unset($session[$this->flashKey]);
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
        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
        }
        return true;
    }

    /**
     * Get session flash key
     * @return string
     */
    public function getFlashKey(): string
    {
        return $this->flashKey;
    }

    /**
     * Set session flash key to use
     *
     * @param string $flashKey
     * @return void
     */
    public function setFlashKey(string $flashKey): void
    {
        $this->flashKey = $flashKey;
    }

    /**
     * Check whether the session flash data for given key exists
     * @param  string  $key
     * @return boolean
     */
    public function hasFlash(string $key): bool
    {
        $session = $this->get($this->flashKey, []);
        return array_key_exists($key, $session);
    }

    /**
     * Get the session flash data
     * @param string $key   the key name
     * @param mixed $default the default value to return if can not find session data
     */
    public function getFlash(string $key, $default = null)
    {
        $session = $this->get($this->flashKey, []);
        $value = $default;
        if (array_key_exists($key, $session)) {
            $value = $session[$key];
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
        $session = $this->get($this->flashKey, []);
        $session[$key] = $value;
        $this->set($this->flashKey, $session);
    }

    /**
     * Remove the session data for the given key
     * @param string $key   the key name
     *
     * @return bool
     */
    public function removeFlash(string $key): bool
    {
        $session = $this->get($this->flashKey, []);
        if (array_key_exists($key, $session)) {
            unset($session[$key]);
        }
        $this->set($this->flashKey, $session);
        return true;
    }
}
