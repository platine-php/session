<?php

declare(strict_types=1);

namespace Platine\Test\Session;

use Platine\Session\FileSessionHandler;
use Platine\Session\Session;
use Platine\Session\Exception\FileSessionHandlerException;
use Platine\Session\Exception\SessionException;
use org\bovigo\vfs\vfsStream;
use Platine\PlatineTestCase;

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

    public function testConstructor(): void
    {
        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();
        $s = new Session($fileHandler);
        $sr = $this->getPrivateProtectedAttribute(Session::class, 'handler');
        $this->assertInstanceOf(FileSessionHandler::class, $sr->getValue($s));
    }

    public function testConstructorSessionAlreadyStart(): void
    {
        global $mock_session_status;
        $mock_session_status = true;
        $this->expectException(SessionException::class);
        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();
        $s = new Session($fileHandler);
    }

    public function testGetHandler(): void
    {
        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();
        $s = new Session($fileHandler);
        $this->assertInstanceOf(FileSessionHandler::class, $s->getHandler());
    }

    public function testHas(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new \stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1
        );

        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();
        $s = new Session($fileHandler);
        $this->assertTrue($s->has('array'));
        $this->assertFalse($s->has('not_found'));
    }

    public function testGetValueWhenKeyNotExist(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new \stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1
        );

        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();
        $session = new Session($fileHandler);
        $value = $session->get('foo');
        $this->assertNull($value);
    }

    public function testGetValueWhenKeyNotExistUsingDefaultValue(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new \stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1
        );

        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();
        $session = new Session($fileHandler);
        $value = $session->get('foo', 'bar');
        $this->assertSame($value, 'bar');
    }

    public function testGetValueWhenKeyExist(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new \stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1
        );

        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();

        $session = new Session($fileHandler);
        $value = 100;
        $this->assertEquals($value, $session->get('int'));
    }

    public function testSetValue(): void
    {
        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();
        $fileHandler->setSavePath($this->vfsSessionPath->url());
        $session = new Session($fileHandler);

        //string
        $value = 'bar';
        $session->set('foo', $value);
        $this->assertSame($value, $session->get('foo'));

        //int
        $value = 1234;
        $session->set('foo', $value);
        $this->assertSame($value, $session->get('foo'));

        //double
        $value = 1234.001;
        $session->set('foo', $value);
        $this->assertSame($value, $session->get('foo'));

        //boolean
        $value = false;
        $session->set('foo', $value);
        $this->assertSame($value, $session->get('foo'));
        $this->assertFalse($session->get('foo'));

        //array 1
        $value = [];
        $session->set('foo', $value);
        $this->assertSame($value, $session->get('foo'));
        $this->assertEmpty($session->get('foo'));

        //array 2
        $value = array('bar');
        $session->set('foo', $value);
        $this->assertSame($value, $session->get('foo'));
        $this->assertSame(1, count($session->get('foo')));
        $this->assertNotEmpty($session->get('foo'));
        $this->assertContains('bar', $session->get('foo'));

        //array 3
        $value = array('key1' => 'value', 'key2' => true);
        $session->set('foo', $value);
        $this->assertSame($value, $session->get('foo'));
        $this->assertSame(2, count($session->get('foo')));
        $this->assertNotEmpty($session->get('foo'));
        $this->assertArrayHasKey('key1', $session->get('foo'));
        $this->assertArrayHasKey('key2', $session->get('foo'));

        //object 1
        $value = new \stdClass();
        $session->set('foo', $value);
        $this->assertSame($value, $session->get('foo'));
        $this->assertInstanceOf('stdClass', $session->get('foo'));

        //object 2
        $value = new \stdClass();
        $value->foo = 'bar';
        $session->set('foo', $value);
        $this->assertSame($value, $session->get('foo'));
        $this->assertSame('bar', $session->get('foo')->foo);
        $this->assertInstanceOf('stdClass', $session->get('foo'));
    }

    public function testReturnAll(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new \stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1,
            'session_flash' => array('fkey1' => 'foo')
        );

        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();

        $session = new Session($fileHandler);

        //Not include flash
        $result = $session->all();
        $this->assertEquals(6, count($result));
        $this->assertArrayHasKey('int', $result);
        $this->assertEquals(100, $result['int']);

        //Included flash data
        $result = $session->all(true);
        $this->assertEquals(7, count($result));
        $this->assertArrayHasKey('session_flash', $result);
        $this->assertEquals(1, count($result['session_flash']));
    }

    public function testSetGetFlashKey(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new \stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1,
            'session_flash' => array('fkey1' => 'foo')
        );
        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();

        $session = new Session($fileHandler);
        $result = $session->getFlashKey();
        $this->assertEquals($result, 'session_flash');

        $session->setFlashKey('foo_key');
        $result = $session->getFlashKey();
        $this->assertEquals($result, 'foo_key');
    }

    public function testHasFlash(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new \stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1,
            'session_flash' => array('fkey1' => 'foo')
        );
        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();

        $s = new Session($fileHandler);
        $this->assertTrue($s->hasFlash('fkey1'));
        $this->assertFalse($s->hasFlash('not_found'));
    }

    public function testGetFlash(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new \stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1,
            'session_flash' => array('fkey1' => 'foo')
        );
        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();

        $s = new Session($fileHandler);
        $this->assertEquals('foo', $s->getFlash('fkey1'));
    }

    public function testGetFlashUsingDefaultValue(): void
    {
        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();

        $s = new Session($fileHandler);
        $this->assertEquals('bar', $s->getFlash('not_found', 'bar'));
    }

    public function testSetFlashValue(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new \stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1,
            'session_flash' => array('fkey1' => 'foo')
        );

        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();
        $fileHandler->setSavePath($this->vfsSessionPath->url());

        $session = new Session($fileHandler);

        //string
        $value = 'bar';
        $session->setFlash('foo', $value);
        $this->assertSame($value, $session->getFlash('foo'));

        //int
        $value = 1234;
        $session->setFlash('foo', $value);
        $this->assertSame($value, $session->getFlash('foo'));

        //double
        $value = 1234.001;
        $session->setFlash('foo', $value);
        $this->assertSame($value, $session->getFlash('foo'));

        //boolean
        $value = false;
        $session->setFlash('foo', $value);
        $this->assertSame($value, $session->getFlash('foo'));
        $this->assertNull($session->getFlash('foo'));

        //array 1
        $value = [];
        $session->setFlash('foo', $value);
        $this->assertSame($value, $session->getFlash('foo'));
        $this->assertEmpty($session->getFlash('foo'));

        //array 2
        $value = array('bar');
        $session->setFlash('foo', $value);
        $this->assertSame($value, $session->getFlash('foo'));
        $this->assertNull($session->getFlash('foo'));

        //array 3
        $value = array('key1' => 'value', 'key2' => true);
        $session->setFlash('foo', $value);
        $this->assertSame($value, $session->getFlash('foo'));
        $this->assertNull($session->getFlash('foo'));

        //object 1
        $value = new \stdClass();
        $session->setFlash('foo', $value);
        $this->assertEquals($value, $session->getFlash('foo'));

        //object 2
        $value = new \stdClass();
        $value->foo = 'bar';
        $session->setFlash('foo', $value);
        $this->assertEquals($value, $session->getFlash('foo'));
    }

    public function testRemoveKeyNotExist(): void
    {
        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();

        $session = new Session($fileHandler);
        $result = $session->remove('test');
        $this->assertTrue($result);
    }

    public function testRemoveKeyExist(): void
    {
        $_SESSION = array(
            'array' => array('foo' => 'bar'),
            'object' => new \stdClass(),
            'bool_false' => false,
            'bool_true' => true,
            'int' => 100,
            'float' => 10.1,
            'session_flash' => array('fkey1' => 'foo')
        );
        $fileHandler = $this->getMockBuilder(FileSessionHandler::class)->getMock();

        $s = new Session($fileHandler);
        $this->assertEquals(true, $s->get('bool_true'));
        $s->remove('bool_true');
        $this->assertNull($s->get('bool_true'));
    }
}
