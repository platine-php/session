<?php

declare(strict_types=1);

namespace Platine\Test\Session;

use org\bovigo\vfs\vfsStream;
use Platine\Dev\PlatineTestCase;
use Platine\Session\Configuration;
use Platine\Session\Exception\SessionException;
use Platine\Session\Session;
use Platine\Session\Storage\NullStorage;
use stdClass;

/**
 * Session class tests
 *
 * @group core
 * @group session
 */
class SessionTest extends PlatineTestCase
{

    protected $vfsRoot;
    protected $vfsSessionPath;

    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION = [];
        //need setup for each test
        $this->vfsRoot = vfsStream::setup();
        $this->vfsSessionPath = vfsStream::newDirectory('sessions')->at($this->vfsRoot);
    }

    public function testConstructorDefault(): void
    {
        $s = new Session();
        $this->assertInstanceOf(
            Configuration::class,
            $this->getPropertyValue(Session::class, $s, 'config')
        );
    }

    public function testConstructor(): void
    {
        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);
        $this->assertInstanceOf(
            NullStorage::class,
            $this->getPropertyValue(Session::class, $s, 'handler')
        );
    }

    public function testGetHandler(): void
    {
        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);
        $this->assertInstanceOf(NullStorage::class, $s->getHandler());
    }

    public function testHas(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1
        );

        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);
        $this->assertTrue($s->has('array'));
        $this->assertFalse($s->has('not_found'));
    }

    public function testGetValueWhenKeyNotExist(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1
        );

        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);
        $value = $s->get('foo');
        $this->assertNull($value);
    }

    public function testGetValueWhenKeyNotExistUsingDefaultValue(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1
        );

        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);
        $value = $s->get('foo', 'bar');
        $this->assertSame($value, 'bar');
    }

    public function testGetValueWhenKeyExist(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1
        );

        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);
        $value = 100;
        $this->assertEquals($value, $s->get('int'));
    }

    public function testSetValue(): void
    {
        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);

        //string
        $value = 'bar';
        $s->set('foo', $value);
        $this->assertSame($value, $s->get('foo'));

        //int
        $value = 1234;
        $s->set('foo', $value);
        $this->assertSame($value, $s->get('foo'));

        //double
        $value = 1234.001;
        $s->set('foo', $value);
        $this->assertSame($value, $s->get('foo'));

        //boolean
        $value = false;
        $s->set('foo', $value);
        $this->assertSame($value, $s->get('foo'));
        $this->assertFalse($s->get('foo'));

        //array 1
        $value = [];
        $s->set('foo', $value);
        $this->assertSame($value, $s->get('foo'));
        $this->assertEmpty($s->get('foo'));

        //array 2
        $value = array('bar');
        $s->set('foo', $value);
        $this->assertSame($value, $s->get('foo'));
        $this->assertSame(1, count($s->get('foo')));
        $this->assertNotEmpty($s->get('foo'));
        $this->assertContains('bar', $s->get('foo'));

        //array 3
        $value = array('key1' => 'value', 'key2' => true);
        $s->set('foo', $value);
        $this->assertSame($value, $s->get('foo'));
        $this->assertSame(2, count($s->get('foo')));
        $this->assertNotEmpty($s->get('foo'));
        $this->assertArrayHasKey('key1', $s->get('foo'));
        $this->assertArrayHasKey('key2', $s->get('foo'));

        //object 1
        $value = new stdClass();
        $s->set('foo', $value);
        $this->assertSame($value, $s->get('foo'));
        $this->assertInstanceOf('stdClass', $s->get('foo'));

        //object 2
        $value = new stdClass();
        $value->foo = 'bar';
        $s->set('foo', $value);
        $this->assertSame($value, $s->get('foo'));
        $this->assertSame('bar', $s->get('foo')->foo);
        $this->assertInstanceOf('stdClass', $s->get('foo'));
    }

    public function testReturnAll(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1,
            'session_flash' => array('fkey1' => 'foo')
        );

        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);

        //Not include flash
        $result = $s->all();
        $this->assertEquals(6, count($result));
        $this->assertArrayHasKey('int', $result);
        $this->assertEquals(100, $result['int']);

        //Included flash data
        $result = $s->all(true);
        $this->assertEquals(7, count($result));
        $this->assertArrayHasKey('session_flash', $result);
        $this->assertEquals(1, count($result['session_flash']));
    }

    public function testHasFlash(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1,
            'session_flash' => array('fkey1' => 'foo')
        );
        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);
        $this->assertTrue($s->hasFlash('fkey1'));
        $this->assertFalse($s->hasFlash('not_found'));
    }

    public function testGetFlash(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1,
            'session_flash' => array('fkey1' => 'foo')
        );
        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);
        $this->assertEquals('foo', $s->getFlash('fkey1'));
    }

    public function testGetFlashUsingDefaultValue(): void
    {
        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);
        $this->assertEquals('bar', $s->getFlash('not_found', 'bar'));
    }

    public function testSetFlashValue(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1,
            'session_flash' => array('fkey1' => 'foo')
        );

        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);

        //string
        $value = 'bar';
        $s->setFlash('foo', $value);
        $this->assertSame($value, $s->getFlash('foo'));

        //int
        $value = 1234;
        $s->setFlash('foo', $value);
        $this->assertSame($value, $s->getFlash('foo'));

        //double
        $value = 1234.001;
        $s->setFlash('foo', $value);
        $this->assertSame($value, $s->getFlash('foo'));

        //boolean
        $value = false;
        $s->setFlash('foo', $value);
        $this->assertSame($value, $s->getFlash('foo'));
        $this->assertNull($s->getFlash('foo'));

        //array 1
        $value = [];
        $s->setFlash('foo', $value);
        $this->assertSame($value, $s->getFlash('foo'));
        $this->assertEmpty($s->getFlash('foo'));

        //array 2
        $value = array('bar');
        $s->setFlash('foo', $value);
        $this->assertSame($value, $s->getFlash('foo'));
        $this->assertNull($s->getFlash('foo'));

        //array 3
        $value = array('key1' => 'value', 'key2' => true);
        $s->setFlash('foo', $value);
        $this->assertSame($value, $s->getFlash('foo'));
        $this->assertNull($s->getFlash('foo'));

        //object 1
        $value = new stdClass();
        $s->setFlash('foo', $value);
        $this->assertEquals($value, $s->getFlash('foo'));

        //object 2
        $value = new stdClass();
        $value->foo = 'bar';
        $s->setFlash('foo', $value);
        $this->assertEquals($value, $s->getFlash('foo'));
    }

    public function testRemoveKeyNotExist(): void
    {
        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);
        $result = $s->remove('test');
        $this->assertTrue($result);
    }

    public function testRemoveKeyExist(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1,
            'session_flash' => array('fkey1' => 'foo')
        );
        $cfg = new Configuration([
            'name' => 'PHPSESSID',
            'driver' => 'null',
            'ttl' => 300,
            'flash_key' => 'session_flash',
            'cookie' => [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => false,
            ],
            'storages' => [
                'null' => [
                    'class' => NullStorage::class,
                ],
            ]
        ]);
        $s = new Session($cfg);
        $this->assertEquals(true, $s->get('bool_true'));
        $s->remove('bool_true');
        $this->assertNull($s->get('bool_true'));
    }
}
