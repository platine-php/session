<?php

declare(strict_types=1);

namespace Platine\Test\Session;

use Platine\Dev\PlatineTestCase;
use Platine\Session\Configuration;
use Platine\Session\Storage\NullStorage;

/**
 * NullStorage class tests
 *
 * @group core
 * @group session
 */
class NullStorageTest extends PlatineTestCase
{
    public function testRead(): void
    {
        $cfg = $this->getMockInstance(Configuration::class);

        $ac = new NullStorage($cfg);

        $content = $ac->read('foo');
        $this->assertEmpty($content);
    }

    public function testWrite(): void
    {
        $cfg = $this->getMockInstance(Configuration::class);

        $ac = new NullStorage($cfg);
        $result = $ac->write('foo', 'data');
        $this->assertTrue($result);
    }


    public function testDestroy(): void
    {
        $cfg = $this->getMockInstance(Configuration::class);

        $ac = new NullStorage($cfg);

        $this->assertTrue($ac->destroy('ffoo'));
    }

    public function testClose(): void
    {
        $cfg = $this->getMockInstance(Configuration::class);

        $ac = new NullStorage($cfg);

        $this->assertTrue($ac->close());
    }

    public function testGc(): void
    {
        $cfg = $this->getMockInstance(Configuration::class);

        $ac = new NullStorage($cfg);

        $this->assertTrue($ac->gc(1200));
    }
}
