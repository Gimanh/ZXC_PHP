<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . '../Modules/Mailer/Tx/TestCaseTx.php';
$config = require_once __DIR__ . DIRECTORY_SEPARATOR . '../../config/config.php';
$server = [
    'REQUEST_URI' => '/spa/user/qwerty?param=123&data=someData',
    'REQUEST_METHOD' => 'GET',
    'SERVER_PORT' => '443',
    'SERVER_NAME' => 'localhost',
    'SERVER_PROTOCOL' => 'HTTP/1.1',
    'QUERY_STRING' => 'param=123&data=someData',
    'DOCUMENT_ROOT' => 'C:\inetpub\wwwroot',
    'HTTP_HOST' => 'localhost',
    'HTTP_ACCEPT' => 'text/html',
    'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/73.0.3683.103 Safari/537.36',
    'HTTPS' => 'on',
    'REMOTE_ADDR' => '222.222.222.22',
    'REMOTE_PORT' => '51145',
    'SCRIPT_NAME' => '/spa/index.php',
    'SCRIPT_FILENAME' => 'G:\web\index.php',
    'PHP_SELF' => '/spa/index.php'
];
$_SERVER = array_merge($_SERVER, $server);
/**
 * @var \ZXC\ZXC;
 */
$zxc = require_once __DIR__ . DIRECTORY_SEPARATOR . '../../index.php';