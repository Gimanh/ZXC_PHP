<?php

use PHPUnit\Framework\TestCase;
use ZXC\Native\Route;

class FakeClassForRouteTest
{
    public function getIndex()
    {
        return 'index';
    }

    public function getUser($zxc, $parameters)
    {
        return $parameters['resultBefore'] . ' user ';
    }

    public function beforeGetUser()
    {
        return 'beforeGetUser';
    }

    public function afterGetUser()
    {
        return 'afterGettingUser';
    }

    public function getUserProfile()
    {
        return ' user profile';
    }

    public function beforeGetUserProfile($zxc, $parameters)
    {
        return 'before get profile';
    }

    public function afterGetUserProfile($zxc, $parameters)
    {
        return $parameters['resultMain'] . ' after get user profile';
    }
}

class FakeClassForRouteTestSecond
{
    public function afterGetUserProfile()
    {
        return 'after get profile';
    }
}

class FakeClassForRouteTestSingleton
{
    use \ZXC\Patterns\Singleton;

    public function getUserProfile2($zxc, $parameters)
    {
        if (isset($parameters['resultMain'])) {
            return ' user profile2 ' . $parameters['resultMain'];
        }
        if (isset($parameters['resultBefore'])) {
            return ' user profile2 ' . $parameters['resultBefore'];
        }
        return ' user profile2 ';
    }

    public function beforeGetUserProfile2($zxc, $parameters)
    {
        return 'before get profile2';
    }

    public function afterGetUserProfile2($zxc, $parameters)
    {
        if (isset($parameters['resultMain'])) {
            return 'after get profile2' . $parameters['resultMain'];
        }
        return 'after get profile2';
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
            [
                'route' => 'GET|/',
                'callback' => function ($zxc, $parameters) {

                }
            ],
            [
                'route' => 'GET|/|FakeClassForRouteTest:getIndex',
            ],
            [
                'route' => 'POST|/:userParameters/:secondParameters',
                'callback' => function ($zxc) {
                },
                'before' => function ($zxc, $parameters) {
                },
                'after' => function ($zxc, $parameters) {
                }
            ],
            [
                'route' => 'POST|/:user|FakeClassForRouteTest:getUser',
                'call' => function ($zxc) {
                    $stop = $zxc;
                },
                'before' => 'FakeClassForRouteTest:beforeGetUser',
                'after' => function ($zxc, $parameters) {

                },
                'hooksResultTransfer' => true,
                'children' => [
                    'route' => 'GET|profile|FakeClassForRouteTest:getUserProfile',
                    'before' => 'FakeClassForRouteTest:beforeGetUserProfile',
                    'after' => 'FakeClassForRouteTestSecond:afterGetUserProfile',
                    'children' => [
                        'route' => 'POST|profile2|FakeClassForRouteTestSingleton:getUserProfile2',
                        'before' => 'FakeClassForRouteTestSingleton:beforeGetUserProfile2',
                        'after' => 'FakeClassForRouteTestSingleton:afterGetUserProfile2',
                    ]
                ]
            ]
        ];
        $this->router = \ZXC\Native\Router::getInstance();
        $this->router->initialize($routerConfig);
        parent::__construct($name, $data, $dataName);
    }

    public function testInitializeException()
    {
        $this->assertSame($this->router, \ZXC\Native\Router::getInstance());

        $this->expectException(\InvalidArgumentException::class);
        $this->router->initialize([]);
    }

    public function testInitializeRouteException()
    {
        $this->expectException(\InvalidArgumentException::class);
        new \ZXC\Native\Route([]);
    }

    public function testRouterStructure()
    {
        $this->assertArrayHasKey('GET', $this->router->getRoutes());
        $this->assertArrayHasKey('POST', $this->router->getRoutes());

        $routesCount = count($this->router->getRoutes());
        $this->assertTrue($routesCount >= 2);

        $routeTypes = count($this->router->getRouteTypes());
        $this->assertTrue($routeTypes >= 2);

        $this->assertArrayHasKey('/', $this->router->getRoutes()['GET']);
        $this->assertArrayHasKey('/:user/profile', $this->router->getRoutes()['GET']);

        $this->assertArrayHasKey('/:user', $this->router->getRoutes()['POST']);
        $this->assertArrayHasKey('/:user/profile/profile2', $this->router->getRoutes()['POST']);
    }

    public function testRouteIndex()
    {
        /**
         * @var $indexRoute Route
         */
        $indexRoute = $this->router->getRoutes()['GET']['/'];
        $this->assertSame($indexRoute->getRequestMethod(), 'GET');
        $this->assertSame($indexRoute->getRoutePath(), '/');
        $this->assertSame($indexRoute->getRegex(), '@^/$@D');
        $this->assertSame($indexRoute->getClass(), 'FakeClassForRouteTest');
        $this->assertSame($indexRoute->getClassMethod(), 'getIndex');
        $this->assertSame($indexRoute->getCallback(), null);
        $this->assertSame($indexRoute->getRouteURIParams(), null);
        $this->assertSame($indexRoute->getBefore(), null);
        $this->assertSame($indexRoute->getAfter(), null);
        $this->assertSame($indexRoute->getHooksResultTransfer(), null);
        $this->assertSame($indexRoute->getChildren(), null);
    }

    public function testUserProfileRoute()
    {
        /**
         * @var $userProfileRoute Route
         */
        $userProfileRoute = $this->router->getRoutes()['GET']['/:user/profile'];
        $this->assertSame($userProfileRoute->getRequestMethod(), 'GET');
        $this->assertSame($userProfileRoute->getRoutePath(), '/:user/profile');
        $this->assertSame($userProfileRoute->getRegex(), '@^/(?<user>[a-zA-Z0-9\_\-]+)/profile$@D');

        $this->assertSame($userProfileRoute->getClass(), 'FakeClassForRouteTest');
        $this->assertSame($userProfileRoute->getClassMethod(), 'getUserProfile');
        $this->assertSame($userProfileRoute->getCallback(), null);
        $this->assertSame($userProfileRoute->getRouteURIParams(), null);

        $this->assertSame($userProfileRoute->getBefore(),
            ['class' => 'FakeClassForRouteTest', 'method' => 'beforeGetUserProfile']);

        $this->assertSame($userProfileRoute->getAfter(),
            ['class' => 'FakeClassForRouteTestSecond', 'method' => 'afterGetUserProfile']);

        $this->assertSame($userProfileRoute->getHooksResultTransfer(), null);
        $this->assertSame($userProfileRoute->getChildren(), [
            'route' => 'POST|/:user/profile/profile2|FakeClassForRouteTestSingleton:getUserProfile2',
            'before' => 'FakeClassForRouteTestSingleton:beforeGetUserProfile2',
            'after' => 'FakeClassForRouteTestSingleton:afterGetUserProfile2'
        ]);
    }

    public function testUserRoute()
    {
        /**
         * @var $userRoute Route
         */
        $userRoute = $this->router->getRoutes()['POST']['/:user'];

        $this->assertSame($userRoute->getRequestMethod(), 'POST');
        $this->assertSame($userRoute->getRoutePath(), '/:user');
        $this->assertSame($userRoute->getRegex(), '@^/(?<user>[a-zA-Z0-9\_\-]+)$@D');
        $this->assertSame($userRoute->getClass(), 'FakeClassForRouteTest');
        $this->assertSame($userRoute->getClassMethod(), 'getUser');
        $this->assertSame($userRoute->getCallback(), null);
        $this->assertSame($userRoute->getRouteURIParams(), null);

        $this->assertSame($userRoute->getBefore(),
            ['class' => 'FakeClassForRouteTest', 'method' => 'beforeGetUser']);
        $this->assertTrue(is_callable($userRoute->getAfter()));

        $this->assertSame($userRoute->getHooksResultTransfer(), true);

        $this->assertSame($userRoute->getChildren(), [
            'route' => 'GET|/:user/profile|FakeClassForRouteTest:getUserProfile',
            'before' => 'FakeClassForRouteTest:beforeGetUserProfile',
            'after' => 'FakeClassForRouteTestSecond:afterGetUserProfile',
            'children' => [
                'route' => 'POST|profile2|FakeClassForRouteTestSingleton:getUserProfile2',
                'before' => 'FakeClassForRouteTestSingleton:beforeGetUserProfile2',
                'after' => 'FakeClassForRouteTestSingleton:afterGetUserProfile2',
            ]
        ]);
    }

    public function testUserProfile2()
    {
        /**
         * @var $userProfileRoute2 Route
         */
        $userProfileRoute2 = $this->router->getRoutes()['POST']['/:user/profile/profile2'];

        $this->assertSame($userProfileRoute2->getRequestMethod(), 'POST');
        $this->assertSame($userProfileRoute2->getRoutePath(), '/:user/profile/profile2');
        $this->assertSame($userProfileRoute2->getRegex(), '@^/(?<user>[a-zA-Z0-9\_\-]+)/profile/profile2$@D');
        $this->assertSame($userProfileRoute2->getClass(), 'FakeClassForRouteTestSingleton');
        $this->assertSame($userProfileRoute2->getClassMethod(), 'getUserProfile2');
        $this->assertSame($userProfileRoute2->getCallback(), null);
        $this->assertSame($userProfileRoute2->getRouteURIParams(), null);
        $this->assertSame($userProfileRoute2->getBefore(),
            ['class' => 'FakeClassForRouteTestSingleton', 'method' => 'beforeGetUserProfile2']);
        $this->assertSame($userProfileRoute2->getAfter(),
            ['class' => 'FakeClassForRouteTestSingleton', 'method' => 'afterGetUserProfile2']);
        $this->assertSame($userProfileRoute2->getHooksResultTransfer(), null);
        $this->assertSame($userProfileRoute2->getChildren(), null);
    }

    public function testDisableRouterType()
    {
        $this->assertTrue($this->router->getRouteTypes()['GET']);
        $this->router->disableRouterType('GET');
        $this->assertFalse($this->router->getRouteTypes()['GET']);
    }

    public function testEnableRouterType()
    {
        $this->assertFalse($this->router->getRouteTypes()['GET']);
        $this->router->enableRouterType('GET');
        $this->assertTrue($this->router->getRouteTypes()['GET']);
    }

    public function testGetRouteParamsFromURI()
    {
        $routeParams = $this->router->getRouteWithParamsFromURI('/userTest', 'POST')->getRouteURIParams();
        $this->assertTrue(is_array($routeParams));
        $this->assertSame($routeParams, ['user' => 'userTest']);
        $routes = $this->router->getRoutes();
        /**
         * @var $userRouteInstance Route
         */
        $userRouteInstance = $routes['POST']['/:user'];
        $this->assertSame($userRouteInstance->getRouteURIParams(), ['user' => 'userTest']);


        $routeParams = $this->router->getRouteWithParamsFromURI('/userTest/profile', 'GET')->getRouteURIParams();
        $this->assertTrue(is_array($routeParams));
        $this->assertSame($routeParams, ['user' => 'userTest']);
        /**
         * @var $userProfileRouteInstance Route
         */
        $userProfileRouteInstance = $routes['GET']['/:user/profile'];
        $this->assertSame($userProfileRouteInstance->getRouteURIParams(), ['user' => 'userTest']);


        $routeParams = $this->router->getRouteWithParamsFromURI('/userTest/secondParameters',
            'POST')->getRouteURIParams();
        $this->assertTrue(is_array($routeParams));
        $this->assertSame($routeParams, ['userParameters' => 'userTest', 'secondParameters' => 'secondParameters']);
        /**
         * @var $userTestTwoDinamicParametersRouteInstance Route
         */
        $userTestTwoDinamicParametersRouteInstance = $routes['POST']['/:userParameters/:secondParameters'];
        $this->assertSame($userTestTwoDinamicParametersRouteInstance->getRouteURIParams(),
            ['userParameters' => 'userTest', 'secondParameters' => 'secondParameters']);


        $routeParams = $this->router->getRouteWithParamsFromURI('/userTest/profile/profile2',
            'POST')->getRouteURIParams();
        $this->assertTrue(is_array($routeParams));
        $this->assertSame($routeParams, ['user' => 'userTest']);
        /**
         * @var $userProfileProfile2RouteInstance Route
         */
        $userProfileProfile2RouteInstance = $routes['POST']['/:user/profile/profile2'];
        $this->assertSame($userProfileProfile2RouteInstance->getRouteURIParams(), ['user' => 'userTest']);
    }

    public function testDifficultBaseRoute()
    {
        $routes = $this->router->getRoutes();
        $routeParams = $this->router->getRouteWithParamsFromURI('/userTest/secondParameters',
            'POST')->getRouteURIParams();
        $this->assertTrue(is_array($routeParams));
        $this->assertSame($routeParams, ['userParameters' => 'userTest', 'secondParameters' => 'secondParameters']);
        /**
         * @var $userTestTwoDinamicParametersRouteInstance Route
         */
        $userTestTwoDinamicParametersRouteInstance = $routes['POST']['/:userParameters/:secondParameters'];
        $this->assertSame($userTestTwoDinamicParametersRouteInstance->getRouteURIParams(),
            ['userParameters' => 'userTest', 'secondParameters' => 'secondParameters']);


        $routeParams = $this->router->getRouteWithParamsFromURI('/userTest/secondParameters',
            'POST')->getRouteURIParams();
        $this->assertTrue(is_array($routeParams));
        $this->assertSame($routeParams, ['userParameters' => 'userTest', 'secondParameters' => 'secondParameters']);
        /**
         * @var $userTestTwoDinamicParametersRouteInstance Route
         */
        $userTestTwoDinamicParametersRouteInstance = $routes['POST']['/:userParameters/:secondParameters'];
        $this->assertSame($userTestTwoDinamicParametersRouteInstance->getRouteURIParams(),
            ['userParameters' => 'userTest', 'secondParameters' => 'secondParameters']);


        $this->expectException(\InvalidArgumentException::class);
        $this->router->getRouteWithParamsFromURI('/example/userTest/secondParameters', 'POST');
//        $routeParams = $this->router->getRouteWithParamsFromURI('/example/userTest/secondParameters', 'POST');
//        $this->assertFalse($routeParams);
    }

    public function testExceptionInInitialize()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->router->initialize([]);
    }

    public function testConstructRouteException()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Route([]);
    }

    public function testParseRouteParams()
    {
        $params = [
            'route' => 'POST|/:userParameters/:secondParameters',
            'callback' => function ($zxc) {
            },
            'before' => function ($zxc, $parameters) {
            },
            'after' => function ($zxc, $parameters) {
            }
        ];
        $route = new Route($params);
        $route->parseRouteParams($params);
        $this->assertSame($route->getRegex(),
            '@^/(?<userParameters>[a-zA-Z0-9\_\-]+)/(?<secondParameters>[a-zA-Z0-9\_\-]+)$@D');
    }

    public function testPrepareChildrenRouteParams()
    {
        $params = [
            'route' => 'POST|/:userParameters/:secondParameters',
            'callback' => function ($zxc) {
            },
            'before' => function ($zxc, $parameters) {
            },
            'after' => function ($zxc, $parameters) {
            }
        ];

        $route = new Route($params);
        $params = $route->prepareParsedChildrenRouteParams([
            'route' => 'GET|profile|FakeClassForRouteTest:getUserProfile',
            'before' => 'FakeClassForRouteTest:beforeGetUserProfile',
            'after' => 'FakeClassForRouteTestSecond:afterGetUserProfile',
            'children' => [
                'route' => 'POST|profile2',
                'callback' => function () {

                },
                'before' => function () {

                },
                'after' => function () {

                }
            ]
        ]);
        $this->assertSame($params['route'],
            "GET|/:userParameters/:secondParameters/profile|FakeClassForRouteTest:getUserProfile");

        $paramsWithCallback = $route->prepareParsedChildrenRouteParams([
            'route' => 'POST|profile2',
            'callback' => function () {

            },
            'before' => function () {

            },
            'after' => function () {

            }
        ]);
        $this->assertSame($paramsWithCallback['route'], '/:userParameters/:secondParameters/profile2');
        $this->assertTrue(is_callable($paramsWithCallback['callback']));
        $this->assertTrue(is_callable($paramsWithCallback['before']));
        $this->assertTrue(is_callable($paramsWithCallback['after']));
    }

    public function testCallBeforeCallbackWithoutTransferResult()
    {
        $params = [
            'route' => 'POST|/:userParameters/:secondParameters',
            'callback' => function ($zxc) {
            },
            'before' => function ($zxc, $parameters) {
                $wasCalled = true;
                $this->assertTrue($wasCalled);
            },
            'after' => function ($zxc, $parameters) {
            }
        ];

        $route = new Route($params);
        $zxc = \ZXC\ZXC::getInstance();
        $route->callBefore($zxc);
    }

    public function testCallAfterCallbackWithoutTransferResult()
    {
        $params = [
            'route' => 'POST|/:userParameters/:secondParameters',
            'callback' => function ($zxc) {
            },
            'before' => function ($zxc, $parameters) {

            },
            'after' => function ($zxc, $parameters) {
                $wasCalled = true;
                $this->assertTrue($wasCalled);
            }
        ];

        $route = new Route($params);
        $zxc = \ZXC\ZXC::getInstance();
        $route->callAfter($zxc);
    }

    public function testExecuteRouteWithAllCallbackFunctionsWithoutTransferResult()
    {
        $params = [
            'route' => 'POST|/:userParameters/:secondParameters',
            'callback' => function ($zxc) {
                $this->callbackWasCalled = true;
            },
            'before' => function ($zxc, $parameters) {
                $this->beforeWasCalled = true;
            },
            'after' => function ($zxc, $parameters) {
                $this->afterWasCalled = true;
                $this->assertTrue($this->callbackWasCalled);
                $this->assertTrue($this->beforeWasCalled);
            }
        ];

        $route = new Route($params);
        $zxc = \ZXC\ZXC::getInstance();
        $route->executeRoute($zxc);
    }

    public function testExecuteRouteWithCallbackAndHookMethodsFromClassWithoutTransferResult()
    {
        $params = [
            'route' => 'POST|/:userParameters/:secondParameters',
            'callback' => function ($zxc) {
                $this->callbackWasCalled = true;
                $this->assertTrue($this->callbackWasCalled);
            },
            'before' => 'FakeClassForRouteTest:beforeGetUserProfile',
            'after' => 'FakeClassForRouteTest:afterGetUserProfile',
            'useCommonClassInstance' => true
        ];

        $route = new Route($params);
        $zxc = \ZXC\ZXC::getInstance();
        $this->assertNull($route->executeRoute($zxc));
    }

    public function testExecuteRouteWithAllCallbackFunctionsWithTransferResult()
    {
        $params = [
            'route' => 'POST|/:userParameters/:secondParameters',
            'callback' => function ($zxc, $parameters) {
                return $parameters['resultBefore'] . 'main callback';
            },
            'before' => function ($zxc, $parameters) {
                return 'before callback';
            },
            'after' => function ($zxc, $parameters) {
                $resultData = $parameters['resultMain'] . 'after callback';
                $this->assertSame($resultData, 'before callbackmain callbackafter callback');
                return $resultData;
            },
            'hooksResultTransfer' => true
        ];

        $route = new Route($params);
        $zxc = \ZXC\ZXC::getInstance();
        $route->executeRoute($zxc);
    }

    public function testExecuteRouteWithMethodsFromOneClass()
    {
        $params = [
            'route' => 'POST|/:userParameters/:secondParameters|FakeClassForRouteTest:getUser',
            'before' => 'FakeClassForRouteTest:beforeGetUserProfile',
            'after' => 'FakeClassForRouteTest:afterGetUserProfile',
            'useCommonClassInstance' => true,
            'hooksResultTransfer' => true
        ];

        $route = new Route($params);
        $zxc = \ZXC\ZXC::getInstance();
        $result = $route->executeRoute($zxc);
        $this->assertSame($result, 'before get profile user  after get user profile');
    }

    public function testExecuteRouteWithMethodsFromSomeClass()
    {
        $params = [
            'route' => 'POST|/:userParameters/:secondParameters|FakeClassForRouteTest:getUser',
            'before' => 'FakeClassForRouteTest:beforeGetUserProfile',
            'after' => 'FakeClassForRouteTestSingleton:getUserProfile2',
            'useCommonClassInstance' => true,
            'hooksResultTransfer' => true
        ];

        $route = new Route($params);
        $zxc = \ZXC\ZXC::getInstance();
        $result = $route->executeRoute($zxc);
        $this->assertSame($result, ' user profile2 before get profile user ');
    }

    public function testExecuteRouteWithMethodsFromSingletonClass()
    {
        $params = [
            'route' => 'POST|/:userParameters/:secondParameters|FakeClassForRouteTestSingleton:getUserProfile2',
            'before' => 'FakeClassForRouteTestSingleton:beforeGetUserProfile2',
            'after' => 'FakeClassForRouteTestSingleton:afterGetUserProfile2',
            'useCommonClassInstance' => true,
            'hooksResultTransfer' => true
        ];

        $route = new Route($params);
        $zxc = \ZXC\ZXC::getInstance();
        $result = $route->executeRoute($zxc);
        $this->assertSame($result, 'after get profile2 user profile2 before get profile2');
    }
}