<?php

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use ZXC\Native\Route;
use ZXC\Native\Router;

class Queue
{
    private static $data = [];

    public static function push($data)
    {
        self::$data[] = $data;
    }

    public static function reset()
    {
        self::$data = [];
    }

    public static function getData()
    {
        return self::$data;
    }
}

class MdOne
{
    public static $run = false;

    public static function one(RequestInterface $request, ResponseInterface $response, Closure $next)
    {
        Queue::push('one');
        $next($request, $response, $next);
    }
}

class MdTwo
{
    public static $run = false;

    public static function two(RequestInterface $request, ResponseInterface $response, Closure $next)
    {
        Queue::push('two');
        $next($request, $response, $next);
    }
}

class MdThree
{
    public static $run = false;

    public static function three(RequestInterface $request, ResponseInterface $response, Closure $next)
    {
        Queue::push('three');
        $next($request, $response, $next);
    }
}


class RouterTest extends TestCase
{
    /**
     * @var Router
     */
    public $router;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $routerConfig = [
            'middleware' => [
                'md1' => 'MdOne:one',
                'md2' => 'MdTwo:two',
                'md3' => 'MdThree:three',
            ],
            'routes' => [
                [
                    'route' => 'GET|/',
                    'callback' => function () {
                    }
                ],
                [
                    'route' => 'GET|/|FakeClassForRouteTest:getIndex',
                ],
                [
                    'route' => 'POST|/:userParameters/:secondParameters',
                    'callback' => function () {
                    },
                    'before' => function () {
                    }
                ],
                [
                    'route' => 'POST|/:user|FakeClassForRouteTest:getUser',
                    'call' => function () {

                    },
                    'before' => 'FakeClassForRouteTest:beforeGetUser',
                    'children' => [
                        'route' => 'GET|profile|FakeClassForRouteTest:getUserProfile',
                        'before' => 'FakeClassForRouteTest:beforeGetUserProfile',
                        'children' => [
                            'route' => 'POST|profile2|FakeClassForRouteTestSingleton:getUserProfile2',
                            'before' => 'FakeClassForRouteTestSingleton:beforeGetUserProfile2',
                        ]
                    ]
                ],
                [
                    'route' => 'POST|/middleware/test/queue',
                    'before' => ['md1', 'md2', 'md3'],
                    'callback' => function (RequestInterface $request, ResponseInterface $response) {
                        Queue::push('main');
                    },
                ],
                [
                    'route' => 'POST|/middleware/test/queue/second',
                    'before' => ['md1'],
                    'callback' => function (RequestInterface $request, ResponseInterface $response) {
                        Queue::push('main');
                    },
                ],
            ],

        ];
        $this->router = Router::getInstance();
        $this->router->initialize($routerConfig);
        parent::__construct($name, $data, $dataName);
    }

    public function testRouterStructure()
    {
        $this->assertArrayHasKey('GET', $this->router->getRoutes());
        $this->assertArrayHasKey('POST', $this->router->getRoutes());

        $routesCount = count($this->router->getRoutes());
        $this->assertTrue($routesCount >= 2);

        $routeTypes = count($this->router->getAllowedMethods());
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
        $this->assertSame($userProfileRoute->getRegex(), '@^/(?<user>[a-zA-Z0-9\_\-\@\.]+)/profile$@D');

        $this->assertSame($userProfileRoute->getClass(), 'FakeClassForRouteTest');
        $this->assertSame($userProfileRoute->getClassMethod(), 'getUserProfile');
        $this->assertSame($userProfileRoute->getCallback(), null);
        $this->assertSame($userProfileRoute->getRouteURIParams(), null);

        $this->assertSame($userProfileRoute->getBefore(),
            ['class' => 'FakeClassForRouteTest', 'method' => 'beforeGetUserProfile']);

        $this->assertSame($userProfileRoute->getChildren(), [
            'route' => 'POST|/:user/profile/profile2|FakeClassForRouteTestSingleton:getUserProfile2',
            'before' => 'FakeClassForRouteTestSingleton:beforeGetUserProfile2',
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
        $this->assertSame($userRoute->getRegex(), '@^/(?<user>[a-zA-Z0-9\_\-\@\.]+)$@D');
        $this->assertSame($userRoute->getClass(), 'FakeClassForRouteTest');
        $this->assertSame($userRoute->getClassMethod(), 'getUser');
        $this->assertSame($userRoute->getCallback(), null);
        $this->assertSame($userRoute->getRouteURIParams(), null);

        $this->assertSame($userRoute->getBefore(),
            ['class' => 'FakeClassForRouteTest', 'method' => 'beforeGetUser']);

        $this->assertSame($userRoute->getChildren(), [
            'route' => 'GET|/:user/profile|FakeClassForRouteTest:getUserProfile',
            'before' => 'FakeClassForRouteTest:beforeGetUserProfile',
            'children' => [
                'route' => 'POST|profile2|FakeClassForRouteTestSingleton:getUserProfile2',
                'before' => 'FakeClassForRouteTestSingleton:beforeGetUserProfile2',
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
        $this->assertSame($userProfileRoute2->getRegex(), '@^/(?<user>[a-zA-Z0-9\_\-\@\.]+)/profile/profile2$@D');
        $this->assertSame($userProfileRoute2->getClass(), 'FakeClassForRouteTestSingleton');
        $this->assertSame($userProfileRoute2->getClassMethod(), 'getUserProfile2');
        $this->assertSame($userProfileRoute2->getCallback(), null);
        $this->assertSame($userProfileRoute2->getRouteURIParams(), null);
        $this->assertSame($userProfileRoute2->getBefore(),
            ['class' => 'FakeClassForRouteTestSingleton', 'method' => 'beforeGetUserProfile2']);
        $this->assertSame($userProfileRoute2->getChildren(), null);
    }

    public function testDisableRouterType()
    {
        $this->assertTrue($this->router->getAllowedMethods()['GET']);
        $this->router->disableRouterType('GET');
        $this->assertFalse($this->router->getAllowedMethods()['GET']);
    }

    public function testEnableRouterType()
    {
        $this->assertFalse($this->router->getAllowedMethods()['GET']);
        $this->router->enableRouterType('GET');
        $this->assertTrue($this->router->getAllowedMethods()['GET']);
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
         * @var $userTestTwoDynamicParametersRouteInstance Route
         */
        $userTestTwoDynamicParametersRouteInstance = $routes['POST']['/:userParameters/:secondParameters'];
        $this->assertSame($userTestTwoDynamicParametersRouteInstance->getRouteURIParams(),
            ['userParameters' => 'userTest', 'secondParameters' => 'secondParameters']);

        $routeParams = $this->router->getRouteWithParamsFromURI('/userTest/secondParameters',
            'POST')->getRouteURIParams();
        $this->assertTrue(is_array($routeParams));
        $this->assertSame($routeParams, ['userParameters' => 'userTest', 'secondParameters' => 'secondParameters']);
        /**
         * @var $userTestTwoDinamicParametersRouteInstance Route
         */
        $userTestTwoDynamicParametersRouteInstance = $routes['POST']['/:userParameters/:secondParameters'];
        $this->assertSame($userTestTwoDynamicParametersRouteInstance->getRouteURIParams(),
            ['userParameters' => 'userTest', 'secondParameters' => 'secondParameters']);

        $undefinedRoute = $this->router->getRouteWithParamsFromURI('/example/userTest/secondParameters', 'POST');
        $this->assertFalse($undefinedRoute);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionInInitialize()
    {
        $this->router->initialize([]);
    }

    /**
     * @method testMiddleware
     * @throws ReflectionException
     */
    public function testMiddleware()
    {
        /**
         * @var Route $route
         */
        $route = $this->router->getRoutes()['POST']['/middleware/test/queue'];
        $route->executeRoute();
        $result = Queue::getData();
        $this->assertSame(['one', 'two', 'three', 'main'], $result);
        Queue::reset();
    }
    /**
     * @method testMiddleware
     * @throws ReflectionException
     */
    public function testMiddlewareTwo()
    {
        /**
         * @var Route $route
         */
        $route = $this->router->getRoutes()['POST']['/middleware/test/queue/second'];
        $route->executeRoute();
        $result = Queue::getData();
        $this->assertSame(['one', 'main'], $result);
        Queue::reset();
    }
}