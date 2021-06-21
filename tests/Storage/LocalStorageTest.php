<?php

declare(strict_types=1);

namespace Platine\Test\Session;

use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Filesystem\DirectoryInterface;
use Platine\Filesystem\Filesystem;
use Platine\Session\Configuration;
use Platine\Session\Exception\FileSessionHandlerException;
use Platine\Session\Storage\LocalStorage;

/**
 * LocalStorage class tests
 *
 * @group core
 * @group session
 */
class LocalStorageTest extends PlatineTestCase
{

    protected $vfsRoot;
    protected $vfsPath;

    protected function setUp(): void
    {
        parent::setUp();
        //need setup for each test
        $this->vfsRoot = vfsStream::setup();
        $this->vfsPath = vfsStream::newDirectory('tests')->at($this->vfsRoot);
    }

    public function testConstructorOne(): void
    {
        global $mock_realpath_to_same;
        $mock_realpath_to_same = true;

        $cfg = new Configuration([
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
                    'class' => LocalStorage::class,
                    'path' => $this->vfsPath->url(),
                    'prefix' => 'sess_',
                ],
            ]
        ]);




        $ls = new LocalStorage($cfg);

        $this->assertInstanceOf(
            DirectoryInterface::class,
            $this->getPropertyValue(LocalStorage::class, $ls, 'directory')
        );

        $this->assertInstanceOf(
            Filesystem::class,
            $this->getPropertyValue(LocalStorage::class, $ls, 'filesystem')
        );

        $this->assertInstanceOf(
            Configuration::class,
            $this->getPropertyValue(LocalStorage::class, $ls, 'config')
        );
    }

    public function testConstructorDirectoryNotFound(): void
    {
        global $mock_realpath_to_same;
        $mock_realpath_to_same = true;

        $this->expectException(InvalidArgumentException::class);
        $path = 'path/not/found';
        $cfg = new Configuration([
            'storages' => [
                'file' => [
                    'path' => $path,
                    'prefix' => 'cache_',
                ],
            ]
        ]);

        (new LocalStorage($cfg));
    }


    public function testGetFilename(): void
    {
        global $mock_realpath_to_same;
        $mock_realpath_to_same = true;

        $cfg = new Configuration([
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
                    'class' => LocalStorage::class,
                    'path' => $this->vfsPath->url(),
                    'prefix' => 'sess_',
                ],
            ]
        ]);
        $ls = new LocalStorage($cfg);

        $key = 'foo';
        $file = $this->runPrivateProtectedMethod($ls, 'getFileName', array($key));

        $this->assertEquals($file, 'sess_foo');
    }

    public function testOpen(): void
    {
        global $mock_realpath_to_same;
        $mock_realpath_to_same = true;

        $cfg = new Configuration([
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
                    'class' => LocalStorage::class,
                    'path' => $this->vfsPath->url(),
                    'prefix' => 'sess_',
                ],
            ]
        ]);

        $ls = new LocalStorage($cfg);
        $this->assertTrue($ls->open('foo', 'sid'));
    }

    public function testClose(): void
    {
        global $mock_realpath_to_same;
        $mock_realpath_to_same = true;

        $cfg = new Configuration([
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
                    'class' => LocalStorage::class,
                    'path' => $this->vfsPath->url(),
                    'prefix' => 'sess_',
                ],
            ]
        ]);




        $ls = new LocalStorage($cfg);
        $this->assertTrue($ls->close());
    }

    public function testDestroy(): void
    {
        global $mock_realpath_to_same;
        $mock_realpath_to_same = true;

        $sid = uniqid();
        $data = serialize(array('foo' => 'bar'));

        $cfg = new Configuration([
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
                    'class' => LocalStorage::class,
                    'path' => $this->vfsPath->url(),
                    'prefix' => 'sess_',
                ],
            ]
        ]);

        $ls = new LocalStorage($cfg);
        $ls->write($sid, $data);
        $this->assertEquals($data, $ls->read($sid));
        $ls->destroy($sid);
        $this->assertEmpty($ls->read($sid));
    }

    public function testDestroyFileNotFound(): void
    {
        global $mock_realpath_to_same;
        $mock_realpath_to_same = true;

        $sid = uniqid();

        $cfg = new Configuration([
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
                    'class' => LocalStorage::class,
                    'path' => $this->vfsPath->url(),
                    'prefix' => 'sess_',
                ],
            ]
        ]);

        $ls = new LocalStorage($cfg);
        $ls->destroy($sid);
        $this->assertEmpty($ls->read($sid));
    }

    public function testWrite(): void
    {
        global $mock_realpath_to_same;
        $mock_realpath_to_same = true;

        $sid = uniqid();
        $data = serialize(array('foo' => 'bar'));

        $cfg = new Configuration([
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
                    'class' => LocalStorage::class,
                    'path' => $this->vfsPath->url(),
                    'prefix' => 'sess_',
                ],
            ]
        ]);

        $ls = new LocalStorage($cfg);
        $result = $ls->write($sid, $data);
        $this->assertTrue($result);

        $this->assertEquals($data, $ls->read($sid));
    }

    public function testRead(): void
    {
        global $mock_realpath_to_same;
        $mock_realpath_to_same = true;

        $sid = uniqid();
        $filename = 'sess_' . $sid;
        $expectedContent = serialize(array('foo' => 'bar'));
        vfsStream::newFile($filename)
                ->at($this->vfsPath)
                ->setContent($expectedContent);

        $cfg = new Configuration([
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
                    'class' => LocalStorage::class,
                    'path' => $this->vfsPath->url(),
                    'prefix' => 'sess_',
                ],
            ]
        ]);

        $ls = new LocalStorage($cfg);
        $content = $ls->read($sid);
        $this->assertEquals($expectedContent, $content);
    }

    public function testReadFileNotFound(): void
    {
        global $mock_realpath_to_same;
        $mock_realpath_to_same = true;

        $sid = uniqid();

        $cfg = new Configuration([
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
                    'class' => LocalStorage::class,
                    'path' => $this->vfsPath->url(),
                    'prefix' => 'sess_',
                ],
            ]
        ]);

        $ls = new LocalStorage($cfg);
        $content = $ls->read($sid);
        $this->assertEmpty($content);
    }

    public function testGarbageCollector(): void
    {
        global $mock_realpath_to_same,
        $mock_time_to_big;

        $mock_time_to_big = true;
        $mock_realpath_to_same = true;

        $sid = uniqid();

        $data = serialize(array('foo' => 'bar'));

        $cfg = new Configuration([
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
                    'class' => LocalStorage::class,
                    'path' => $this->vfsPath->url(),
                    'prefix' => 'sess_',
                ],
            ]
        ]);

        $ls = new LocalStorage($cfg);
        $ls->write($sid, $data);


        $result = $ls->gc(-9999999);
        $this->assertTrue($result);
    }
}
