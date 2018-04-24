<?php

use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testAllMethods()
    {

        $sessionInstance = \ZXC\Classes\Session::getInstance();
        $sessionConfig = ['prefix' => 'zxc_', 'time' => 6200, 'path' => '/', 'domain' => 'example.com'];
        $sessionInstance->initialize($sessionConfig);

        $this->assertFalse($sessionInstance->set(null, null));

        $this->assertSame($sessionInstance->set('testKey', 'testValue'), 'testValue');

        $this->assertFalse($sessionInstance->get('zxc_'));

        $this->assertSame($sessionInstance->get('testKey'), 'testValue');

        $this->assertTrue($sessionInstance->delete('testKey'));

        $this->assertFalse($sessionInstance->get('testKey'));

        $this->assertSame($sessionInstance->set('testKey', 'testValue'), 'testValue');

        $this->assertSame($_SESSION, ['zxc_' => ['testKey' => 'testValue']]);

        $sessionInstance->clear();

        $this->assertSame($_SESSION['zxc_'], []);

        $sessionInstance->destroy();

        $this->assertEmpty($_SESSION);

        $this->expectException(\InvalidArgumentException::class);

        unset($sessionConfig['domain']);

        $sessionInstance->initialize($sessionConfig);
    }
}