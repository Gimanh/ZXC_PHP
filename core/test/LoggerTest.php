<?php

use PHPUnit\Framework\TestCase;

$dir = __DIR__;
$config = [];
$file = $dir . '/../index.php';
if (file_exists($file)) {
    require_once $file;
}

class LoggerTest extends TestCase
{
    public function testAllLoggerMethods()
    {
        $loggerConfig = ['applevel' => 'debug', 'settings' => ['filePath' => './logTest.log', 'root' => false]];

        $loggerInstance = new \ZXC\Native\Logger($loggerConfig);

        $this->assertSame($loggerInstance->getFilePath(), $loggerConfig['settings']['filePath']);

        $this->assertTrue(file_exists($loggerInstance->getFilePath()));

        $loggerInstance->warning('test warning');

        $fileContent = file_get_contents($loggerInstance->getFilePath());

        $position = boolval(strpos($fileContent, 'test warning'));

        $this->assertTrue($position);

        $this->assertNull($loggerInstance->contextStringify([]));

        $this->assertSame($loggerInstance->contextStringify(['testFiled' => ['1' => '2']]), '{"testFiled":{"1":"2"}}');

        $this->assertTrue(unlink($loggerInstance->getFilePath()));

        $this->assertFalse(file_exists($loggerInstance->getFilePath()));
    }
}