<?php

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private static $originConfig = [];

    public static function setUpBeforeClass()
    {
        self::$originConfig = ['ZXC' => \ZXC\Native\Config::get('ZXC')];
    }

    public static function tearDownAfterClass()
    {
        \ZXC\Native\Config::initialize(self::$originConfig);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInitialize()
    {
        \ZXC\Native\Config::initialize([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGet()
    {
        $config = ['ZXC' => ['Logger' => ['level' => 'debug'], 'Test' => 'test']];

        \ZXC\Native\Config::initialize($config);

        $this->assertNull(\ZXC\Native\Config::get('No/Parameters/path'));

        $this->assertSame(\ZXC\Native\Config::get('ZXC/Logger'), ['level' => 'debug']);

        $this->assertNull(\ZXC\Native\Config::get('zxc/logger'));

        $this->assertNull(\ZXC\Native\Config::get('ZXC/Test/undefined'));

        $this->assertSame(\ZXC\Native\Config::get('zxc/loGgeR/', false), ['level' => 'debug']);

        $this->assertSame(\ZXC\Native\Config::get('zxc/logger', false), ['level' => 'debug']);

        $this->assertSame(\ZXC\Native\Config::get('ZXC/Test'), 'test');

        \ZXC\Native\Config::get([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAdd()
    {
        $config = ['ZXC' => ['Logger' => ['level' => 'debug'], 'Test' => 'test']];
        \ZXC\Native\Config::initialize($config);

        $this->assertSame(\ZXC\Native\Config::get('ZXC'), ['Logger' => ['level' => 'debug'], 'Test' => 'test']);
        $this->assertTrue(\ZXC\Native\Config::add(['GNL' => ['qwerty' => 1234567]]));

        $this->assertSame(\ZXC\Native\Config::get('ZXC'), ['Logger' => ['level' => 'debug'], 'Test' => 'test']);
        $this->assertSame(\ZXC\Native\Config::get('GNL'), ['qwerty' => 1234567]);

        $this->assertTrue(\ZXC\Native\Config::add(['ZXC' => ['qwerty' => 'testReplace']]));
        $this->assertSame(\ZXC\Native\Config::get('ZXC/qwerty'), 'testReplace');

        \ZXC\Native\Config::add([]);
    }
}