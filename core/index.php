<?php
define('ZXC_ROOT', __DIR__);
require_once 'ZXC/ZXC.php';
$zxc = \ZXC\ZXC::getInstance();
$zxc->initialize($config);
return $zxc;