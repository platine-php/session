<?php

declare(strict_types=1);

namespace Platine\Session;

$mock_session_status = false;
$mock_glob = false;
$mock_filemtime = false;
$mock_time = false;
$mock_file_exists = false;
$mock_unlink = false;

function unlink(string $filename, resource $context = null)
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
}
