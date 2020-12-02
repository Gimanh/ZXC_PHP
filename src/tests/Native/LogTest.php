<?php

use ZXC\Native\Config;
use ZXC\Native\ModulesManager;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 15.11.2018
 * Time: 14:58
 */
class LogTest extends \PHPUnit\Framework\TestCase
{
    public function testLogWrite()
    {
        $dir = __DIR__;
        $config = require $dir . '/../../config/config.php';
        Config::init($config);
        \ZXC\Native\Log::debug('debug log', ['debug_parameters' => 123]);
        $logger = ModulesManager::getNewModule('Logger');
        $logger->setLogsFolder(Config::get('ZXC/Modules/Logger/options/folder'));
        $logger->setLogFileName(Config::get('ZXC/Modules/Logger/options/file'));
        $fileContent = file_get_contents(ZXC_ROOT . DIRECTORY_SEPARATOR . $logger->getFullLogFilePath());
        $position = (bool)strpos($fileContent, 'debug log');
        $this->assertTrue($position);
        $this->assertTrue(unlink(ZXC_ROOT . DIRECTORY_SEPARATOR . $logger->getFullLogFilePath()));
    }
}
