<?php

use \PHPUnit\Framework\TestCase;

class FakeClassForTest
{
    public function fakeMethod()
    {
        return 'fakeMethod';
    }
}

class ZXCTest extends TestCase
{
    public function testZXCInitialize()
    {
        $dir = __DIR__;
        $config = require $dir . '/../config/config.php';
        $zxc = \ZXC\ZXC::getInstance();

        $_SERVER['HTTP_HOST'] = 'zxc:80';
        $_SERVER['SERVER_NAME'] = 'zxcserver';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $zxc->initialize($config);
        $this->assertSame(get_class($zxc->getLogger()), 'ZXC\Native\Logger');
        $this->assertSame(get_class($zxc->getRequest()), 'ZXC\Native\HTTP\Request');
        $this->assertSame(get_class($zxc->getRouter()), 'ZXC\Native\Router');
        $this->assertSame(\ZXC\Native\Autoload::getAutoloadDirectories(), ['' => true, '../../' => true]);

        $stop = false;
    }

    public function testWriteLog()
    {
        $dir = __DIR__;
        $config = require $dir . '/../config/config.php';
        $zxc = \ZXC\ZXC::getInstance();

        $_SERVER['HTTP_HOST'] = 'zxc:80';
        $_SERVER['SERVER_NAME'] = 'zxcserver';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $zxc->initialize($config);
        $this->assertTrue($zxc->writeLog('Message', ['parameters' => 123]));
    }

    public function testHaveServerParametersForWorking()
    {
        $zxc = \ZXC\ZXC::getInstance();

        $_SERVER['HTTP_HOST'] = 'zxc:80';
        $_SERVER['SERVER_NAME'] = 'zxcserver';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = null;
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $this->assertFalse($zxc->haveServerParametersForWorking());
        $_SERVER['REQUEST_URI'] = '/';
        $this->assertTrue($zxc->haveServerParametersForWorking());
    }

    public function testGo()
    {
        $dir = __DIR__;
        $config = require $dir . '/../config/config.php';
        $zxc = \ZXC\ZXC::getInstance();

        $_SERVER['HTTP_HOST'] = 'zxc:80';
        $_SERVER['SERVER_NAME'] = 'zxcserver';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        $zxc->initialize($config);
        $this->assertTrue($zxc->go());
    }
}