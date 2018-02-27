<?php

use PHPUnit\Framework\TestCase;

$dir = __DIR__;
$config = [];
$file = $dir . '/../index.php';
if (file_exists($file)) {
    require_once $file;
}

class FakeClassForRouteTest
{
    public function getValue()
    {
        return 'value';
    }

    public function getUser()
    {
        return 'user';
    }

    public function beforeGetUser()
    {
        return 'beforeGetUser';
    }

    public function afterGettingUser()
    {
        return 'afterGettingUser';
    }
}

class RouteTest extends TestCase
{
    /**
     * @var \ZXC\Native\Router
     */
    public $router;

    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        $routerConfig = [
            /*[
                'route' => 'GET|/',
                'callback' => function ($zxc, $parameters) {

                }
            ],
            [
                'route' => 'GET|/|FakeClassForRouteTest:getValue',
            ],
            [
                'route' => 'POST|/:user|FakeClassForRouteTest:getUser',
                'before' => 'FakeClassForRouteTest:beforeGetUser',
                'after' => 'FakeClassForRouteTest:afterGettingUser',
                'hooksResultTransfer' => true,
            ],*/
            [
                'route' => 'POST|/:user|FakeClassForRouteTest:getUser',
                'call' => function ($zxc) {
                    $stop = $zxc;
                },
                'before' => 'ASD\TestClass:before',
                'after' => function ($z, $p, $result) {
                    $zxc = $z;
                    $params = $p;
                    echo 'after hooks=>' . $result;
                },
                'hooksResultTransfer' => true,
                'children' => [
                    'route' => 'GET|profile|QWEQ:profile',
                    'before' => 'QWEQ:profileBefore',
                    'after' => 'QWEQ:profileAfter',
                    'children' => [
                        'route' => 'POST|profile2|QWEQ:profile2',
                        'before' => 'QWEQ:profileBefore2',
                        'after' => 'QWEQ:profileAfter2',
                    ]
                ]
            ]
        ];
        $this->router = \ZXC\Native\Router::getInstance();
        $this->router->initialize($routerConfig);
        parent::__construct($name, $data, $dataName);
    }

    public function testInitialize()
    {

    }
}