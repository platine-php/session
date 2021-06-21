<?php

declare(strict_types=1);

namespace Platine\Session;

$mock_session_start = false;
$mock_session_status = false;
$mock_glob = false;
$mock_filemtime = false;
$mock_time = false;
$mock_file_exists = false;
$mock_unlink = false;

function unlink(string $filename, $context = null)
{
    global $mock_unlink;
    if ($mock_unlink) {
        return true;
    } else {
        return \unlink($filename);
    }
}

function file_exists(string $filename)
{
    global $mock_file_exists;
    if ($mock_file_exists) {
        return true;
    } else {
        return \file_exists($filename);
    }
}

function time()
{
    global $mock_time;
    if ($mock_time) {
        return 10000000;
    } else {
        return \time();
    }
}

function filemtime(string $filename)
{
    global $mock_filemtime;
    if ($mock_filemtime) {
        return 1000;
    } else {
        return \filemtime($filename);
    }
}

function glob(string $pattern, int $flags = 0)
{
    global $mock_glob;
    if ($mock_glob) {
        return array('file1', 'file2');
    } else {
        return \glob($pattern, $flags);
    }
}

function session_status()
{
    global $mock_session_status;
    if ($mock_session_status) {
        return PHP_SESSION_ACTIVE;
    } else {
        return \session_status();
    }
}

function session_start()
{
    global $mock_session_start;
    if ($mock_session_start) {
        //do nothing
    } else {
        \session_start();
    }
}

namespace Platine\Session\Storage;

$mock_extension_loaded_to_false = false;
$mock_extension_loaded_to_true = false;
$mock_ini_get_to_false = false;
$mock_ini_get_to_true = false;
$mock_apcu_fetch_to_false = false;
$mock_apcu_store_to_false = false;
$mock_apcu_store_to_true = false;
$mock_apcu_delete_to_false = false;
$mock_apcu_delete_to_true = false;
$mock_apcu_clear_cache_to_false = false;
$mock_apcu_clear_cache_to_true = false;
$mock_apcu_exists_to_false = false;
$mock_apcu_exists_to_true = false;
$mock_time_to_big = false;

function apcu_exists($key): bool
{
    global $mock_apcu_exists_to_false, $mock_apcu_exists_to_true;
    if ($mock_apcu_exists_to_false) {
        return false;
    } elseif ($mock_apcu_exists_to_true) {
        return true;
    }

    return false;
}

function apcu_clear_cache(): bool
{
    global $mock_apcu_clear_cache_to_false, $mock_apcu_clear_cache_to_true;
    if ($mock_apcu_clear_cache_to_false) {
        return false;
    } elseif ($mock_apcu_clear_cache_to_true) {
        return true;
    }

    return false;
}

/**
 * @return null|string
 */
function apcu_fetch($key, bool &$success)
{
    global $mock_apcu_fetch_to_false;
    if ($mock_apcu_fetch_to_false) {
        $success = false;
    } else {
        $success = true;
        return md5($key);
    }
}

function apcu_store($key, $var, int $ttl = 0): bool
{
    global $mock_apcu_store_to_false, $mock_apcu_store_to_true;
    if ($mock_apcu_store_to_false) {
        return false;
    } elseif ($mock_apcu_store_to_true) {
        return true;
    }

    return false;
}

function apcu_delete($key): bool
{
    global $mock_apcu_delete_to_false, $mock_apcu_delete_to_true;
    if ($mock_apcu_delete_to_false) {
        return false;
    } elseif ($mock_apcu_delete_to_true) {
        return true;
    }

    return false;
}

function extension_loaded(string $name): bool
{
    global $mock_extension_loaded_to_false, $mock_extension_loaded_to_true;
    if ($mock_extension_loaded_to_false) {
        return false;
    } elseif ($mock_extension_loaded_to_true) {
        return true;
    } else {
        return \extension_loaded($name);
    }
}

/**
 * @return bool|string
 */
function ini_get(string $option)
{
    global $mock_ini_get_to_true, $mock_ini_get_to_false;
    if ($mock_ini_get_to_false) {
        return false;
    } elseif ($mock_ini_get_to_true) {
        return true;
    } else {
        return \ini_get($option);
    }
}

function time()
{
    global $mock_time_to_big;
    if ($mock_time_to_big) {
        return 9999999;
    } else {
        return \time();
    }
}

namespace Platine\Stdlib\Helper;
$mock_realpath_to_same = false;



function realpath(string $name)
{
    global $mock_realpath_to_same;

    if ($mock_realpath_to_same) {
        return $name;
    }

    return \realpath($name);
}
