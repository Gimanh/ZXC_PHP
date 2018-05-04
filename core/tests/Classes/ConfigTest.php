<?php

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testInitialize()
    {
        $configInstance = \ZXC\Classes\Config::getInstance();
        $this->expectException(\InvalidArgumentException::class);
        $configInstance::initialize([]);
    }

    public function testGet()
    {
        $config = ['ZXC' => ['Logger' => ['level' => 'debug'], 'Test' => 'test']];
        $configInstance = \ZXC\Classes\Config::getInstance();
        $configInstance::initialize($config);

        $this->assertFalse($configInstance::get('No/Parameters/path'));

        $this->assertSame($configInstance::get('ZXC/Logger'), ['level' => 'debug']);

        $this->assertFalse($configInstance::get('zxc/logger'));

        $this->assertSame($configInstance::get('zxc/loGgeR/', false), ['level' => 'debug']);

        $this->assertSame($configInstance::get('zxc/logger', false), ['level' => 'debug']);

        $this->assertSame($configInstance::get('ZXC/Test'), 'test');

        $this->expectException(\InvalidArgumentException::class);
        $configInstance::get([]);
    }

    public function testAdd()
    {
        $config = ['ZXC' => ['Logger' => ['level' => 'debug'], 'Test' => 'test']];
        $configInstance = \ZXC\Classes\Config::getInstance();
        $configInstance::initialize($config);

        $this->assertSame($configInstance::get('ZXC'), ['Logger' => ['level' => 'debug'], 'Test' => 'test']);
        $this->assertTrue($configInstance::add(['GNL' => ['qwerty' => 1234567]]));

        $this->assertSame($configInstance::get('ZXC'), ['Logger' => ['level' => 'debug'], 'Test' => 'test']);
        $this->assertSame($configInstance::get('GNL'), ['qwerty' => 1234567]);

        $this->expectException(\InvalidArgumentException::class);
        $configInstance::add([]);

    }
}