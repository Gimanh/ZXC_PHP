<?php

use \PHPUnit\Framework\TestCase;
use ZXC\Native\Config;
use ZXC\Native\ModulesManager;

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
        $zxc->initialize($config);

        $_SERVER['HTTP_HOST'] = 'zxc:80';
        $_SERVER['SERVER_NAME'] = 'zxcserver';
        $_SERVER['SERVER_PORT'] = '80';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

//        $this->assertSame(get_class($zxc->getLogger()), 'ZXC\Modules\Logger\Logger');
        $this->assertSame(get_class($zxc->getRequest()), 'ZXC\Native\HTTP\Request');
        $this->assertSame(get_class($zxc->getRouter()), 'ZXC\Native\Router');
        $this->assertSame(\ZXC\Native\Autoload::getAutoloadDirectories(), ['' => true, '../../' => true]);
    }

//    public function testWriteLog()
//    {
//        $dir = __DIR__;
//        $config = require $dir . '/../config/config.php';
//        $zxc = \ZXC\ZXC::getInstance();
//
//        $_SERVER['HTTP_HOST'] = 'zxc:80';
//        $_SERVER['SERVER_NAME'] = 'zxcserver';
//        $_SERVER['SERVER_PORT'] = '80';
//        $_SERVER['REQUEST_METHOD'] = 'POST';
//        $_SERVER['REQUEST_URI'] = '/';
//        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
//
//        $zxc->initialize($config);
//    }

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

    public static function tearDownAfterClass()
    {
        $logger = ModulesManager::getNewModule('Logger');
        $logger->setLogsFolder(Config::get('ZXC/Modules/Logger/options/folder'));
        $logger->setLogFileName(ZXC\ZXC::getInstance()->getLogFileName());
        unlink($logger->getFullLogFilePath());
        $logger->setLogsFolder(Config::get('ZXC/Modules/Logger/options/folder'));
        $logger->setLogFileName(Config::get('ZXC/Modules/Logger/options/file'));
        unlink($logger->getFullLogFilePath());
    }
}