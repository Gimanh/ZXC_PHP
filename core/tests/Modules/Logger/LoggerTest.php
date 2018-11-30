<?php

use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
    public function testAllLoggerMethods()
    {
        $loggerConfig = ['applevel' => 'debug', 'file' => 'zxc_test.log', 'folder' => '.' . DIRECTORY_SEPARATOR, 'root' => false];

        $loggerInstance = new \ZXC\Modules\Logger\Logger($loggerConfig);

        $file = \ZXC\Native\Helper::fixDirectorySlashes($loggerConfig['folder'] . DIRECTORY_SEPARATOR . $loggerConfig['file']);

        $this->assertSame($file, $loggerInstance->getFullLogFilePath());

        $this->assertTrue(file_exists($loggerInstance->getFullLogFilePath()));

        $loggerInstance->warning('test warning');

        $fileContent = file_get_contents($loggerInstance->getFullLogFilePath());

        $position = (bool)strpos($fileContent, 'test warning');

        $this->assertTrue($position);

        $this->assertNull($loggerInstance->contextStringify([]));

        $this->assertSame($loggerInstance->contextStringify(['testFiled' => ['1' => '2']]), '{"testFiled":{"1":"2"}}');

        $this->assertTrue(unlink($loggerInstance->getFullLogFilePath()));

        $this->assertFalse(file_exists($loggerInstance->getFullLogFilePath()));
    }
}