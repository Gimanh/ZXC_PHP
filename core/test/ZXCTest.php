<?php

use \PHPUnit\Framework\TestCase;

$dir = __DIR__;
$config = [];
$file = $dir . '/../index.php';
if (file_exists($file)) {
    require_once $file;
}

class ZXCTest extends TestCase
{
    public function testZXCInitialize()
    {
        $config = require_once './../config/config.php';
        $zxc = \ZXC\ZXC::getInstance();
        $zxc->initialize($config);
        $stop = false;
    }
}