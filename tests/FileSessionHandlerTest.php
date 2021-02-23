<?php

declare(strict_types=1);

namespace Platine\Test\Session;

use org\bovigo\vfs\vfsStream;
use Platine\PlatineTestCase;
use Platine\Session\Exception\FileSessionHandlerException;
use Platine\Session\FileSessionHandler;

/**
 * File Session Handler class tests
 *
 * @group core
 * @group session
 */
class FileSessionHandlerTest extends PlatineTestCase
{

    protected $vfsRoot;
    protected $vfsSessionPath;

    protected function setUp(): void
    {
        parent::setUp();
        //need setup for each test
        $this->vfsRoot = vfsStream::setup();
        $this->vfsSessionPath = vfsStream::newDirectory('sessions')->at($this->vfsRoot);
    }

    public function testConstructorOne(): void
    {
        $path = $this->vfsSessionPath->url();
        $fsh = new FileSessionHandler($path);
        $sr = $this->getPrivateProtectedAttribute(FileSessionHandler::class, 'savePath');
        $fpr = $this->getPrivateProtectedAttribute(FileSessionHandler::class, 'filePrefix');
        $this->assertEquals($path . DIRECTORY_SEPARATOR, $sr->getValue($fsh));
        $this->assertEquals('sess_', $fpr->getValue($fsh));
    }

    public function testConstructorTwo(): void
    {
        $path = $this->vfsSessionPath->url();
        $fsh = new FileSessionHandler($path, 'fooPrefix');
        $sr = $this->getPrivateProtectedAttribute(FileSessionHandler::class, 'savePath');
        $fpr = $this->getPrivateProtectedAttribute(FileSessionHandler::class, 'filePrefix');
        $this->assertEquals($path . DIRECTORY_SEPARATOR, $sr->getValue($fsh));
        $this->assertEquals('fooPrefix', $fpr->getValue($fsh));
    }

    public function testConstructorDirectoryNotFound(): void
    {
        $this->expectException(FileSessionHandlerException::class);
        $fsh = new FileSessionHandler('/path/not/found/');
    }

    public function testConstructorDirectoryNotWritable(): void
    {
        $this->expectException(FileSessionHandlerException::class);
        $path = $this->vfsSessionPath->url();
        chmod($path, 0400);
        $fsh = new FileSessionHandler($path);
    }

    public function testOpen(): void
    {
        $path = $this->vfsSessionPath->url();
        $fsh = new FileSessionHandler($path);
        $this->assertTrue($fsh->open('foo', 'sid'));
    }

    public function testClose(): void
    {
        $path = $this->vfsSessionPath->url();
        $fsh = new FileSessionHandler($path);
        $this->assertTrue($fsh->close());
    }

    public function testDestroy(): void
    {
        $sid = uniqid();
        $path = $this->vfsSessionPath->url();
        $data = serialize(array('foo' => 'bar'));

        $fsh = new FileSessionHandler($path);
        $result = $fsh->write($sid, $data);
        $this->assertEquals($data, $fsh->read($sid));
        $fsh->destroy($sid);
        $this->assertEmpty($fsh->read($sid));
    }

    public function testDestroyFileNotFound(): void
    {
        $sid = uniqid();
        $path = $this->vfsSessionPath->url();

        $fsh = new FileSessionHandler($path);
        $fsh->destroy($sid);
        $this->assertEmpty($fsh->read($sid));
    }

    public function testWrite(): void
    {
        $sid = uniqid();
        $path = $this->vfsSessionPath->url();
        $data = serialize(array('foo' => 'bar'));

        $fsh = new FileSessionHandler($path);
        $result = $fsh->write($sid, $data);
        $this->assertTrue($result);

        $this->assertEquals($data, $fsh->read($sid));
    }

    public function testRead(): void
    {
        $sid = uniqid();
        $filename = 'sess_' . $sid;
        $path = $this->vfsSessionPath->url();
        $expectedContent = serialize(array('foo' => 'bar'));
        $vfsFile = vfsStream::newFile($filename)->at($this->vfsSessionPath)->setContent($expectedContent);

        $fsh = new FileSessionHandler($path);
        $content = $fsh->read($sid);
        $this->assertEquals($expectedContent, $content);
    }

    public function testReadFileNotFound(): void
    {
        $sid = uniqid();
        $path = $this->vfsSessionPath->url();

        $fsh = new FileSessionHandler($path);
        $content = $fsh->read($sid);
        $this->assertEmpty($fsh->read($sid));
    }

    public function testSetGetSavePath(): void
    {
        $path = $this->vfsSessionPath->url();

        $fsh = new FileSessionHandler();
        $fsh->setSavePath($path);
        $this->assertEquals($fsh->getSavePath(), $path . DIRECTORY_SEPARATOR);
    }

    public function testGarbageCollector(): void
    {
        global $mock_glob,
        $mock_filemtime,
        $mock_time,
        $mock_file_exists,
        $mock_unlink;

        $mock_glob = true;
        $mock_filemtime = true;
        $mock_time = true;
        $mock_file_exists = true;
        $mock_unlink = true;

        $sid = uniqid();
        $path = $this->vfsSessionPath->url();
        $data = serialize(array('foo' => 'bar'));

        $fsh = new FileSessionHandler($path);
        $result = $fsh->write($sid, $data);


        $result = $fsh->gc(10);
        $this->assertTrue($result);
    }
}
