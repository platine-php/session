<?php

declare(strict_types=1);

namespace Platine\Test\Session;

use Platine\Dev\PlatineTestCase;
use Platine\Session\Configuration;
use Platine\Session\Exception\SessionException;
use Platine\Session\Storage\ApcuStorage;
use Platine\Session\Storage\NullStorage;

/**
 * ApcuStorage class tests
 *
 * @group core
 * @group session
 */
class ApcuStorageTest extends PlatineTestCase
{

    public function testConstructorExtensionIsNotLoaded(): void
    {
        global $mock_extension_loaded_to_false;

        $mock_extension_loaded_to_false = true;
        $this->expectException(SessionException::class);

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

        (new ApcuStorage($cfg));
    }

    public function testConstructorExtensionIstLoadedButNotEnabled(): void
    {
        global $mock_extension_loaded_to_true, $mock_ini_get_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_false = true;

        $this->expectException(SessionException::class);
        $cfg = $this->getMockInstance(Configuration::class);

        (new ApcuStorage($cfg));
    }

    public function testRead(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_fetch_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;

        $cfg = $this->getMockInstance(Configuration::class);

        $ac = new ApcuStorage($cfg);

        $mock_apcu_fetch_to_false = true;
        //Default value
        $this->assertEmpty($ac->read('not_found_key'));

        $mock_apcu_fetch_to_false = false;
        //Return correct data
        $key = uniqid();

        $content = $ac->read($key);
        $this->assertEquals(md5($key), $content);
    }

    public function testWriteSimple(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_store_to_true;

        $key = uniqid();
        $data = array('foo' => 'bar');

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_store_to_true = true;

        $cfg = new Configuration([
            'ttl' => 89,
            'storages' => []
        ]);

        $ac = new ApcuStorage($cfg);
        $result = $ac->write($key, $data);
        $this->assertTrue($result);
    }

    public function testWriteFailed(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_store_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_store_to_false = true;

        $cfg = new Configuration([
            'ttl' => 89,
            'storages' => []
        ]);

        $ac = new ApcuStorage($cfg);
        $result = $ac->write('key', 'data');
        $this->assertFalse($result);
    }

    public function testDestroySuccess(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_delete_to_true;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_delete_to_true = true;

        $key = uniqid();

        $cfg = $this->getMockInstance(Configuration::class);

        $ac = new ApcuStorage($cfg);

        $this->assertTrue($ac->destroy($key));
    }

    public function testDestroyFailed(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_delete_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_delete_to_false = true;

        $key = uniqid();

        $cfg = $this->getMockInstance(Configuration::class);

        $ac = new ApcuStorage($cfg);

        $this->assertFalse($ac->destroy($key));
    }

    public function testClose(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_clear_cache_to_false;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_clear_cache_to_false = true;

        $cfg = $this->getMockInstance(Configuration::class);

        $ac = new ApcuStorage($cfg);

        $this->assertTrue($ac->close());
    }

    public function testGc(): void
    {
        global $mock_extension_loaded_to_true,
        $mock_ini_get_to_true,
        $mock_apcu_clear_cache_to_true;

        $mock_extension_loaded_to_true = true;
        $mock_ini_get_to_true = true;
        $mock_apcu_clear_cache_to_true = true;

        $cfg = $this->getMockInstance(Configuration::class);

        $ac = new ApcuStorage($cfg);

        $this->assertTrue($ac->gc(1200));
    }
}
