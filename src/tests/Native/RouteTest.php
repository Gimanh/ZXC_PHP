<?php

use ZXC\Native\Route;
use ZXC\Native\PSR\Stream;
use ZXC\Patterns\Singleton;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class FakeClassForRouteTest
{
    public function getIndex()
    {
        return 'index';
    }
}

class FakeClassForRouteTestSingleton
{
    use Singleton;

    public function handler($request, ResponseInterface $response, $params)
    {
        $body = new Stream('php://memory', 'rb+');
        $body->write('handler');
        $newResponse = $response->withBody($body);
        return $newResponse;
    }
}

class RouteTest extends TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testInitializeRouteException()
    {
        new Route([]);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructRouteException()
    {
        new Route([]);
    }

    public function testParseRouteParams()
    {
        $params = [
            'route' => 'POST|/:userParameters/:secondParameters',
            'callback' => function ($zxc) {
            },
            'before' => function ($zxc, $parameters) {
            }
        ];
        $route = new Route($params);
        $route->parseRouteParams($params);
        $this->assertSame($route->getRegex(),
            '@^/(?<userParameters>[a-zA-Z0-9\_\-\@\.]+)/(?<secondParameters>[a-zA-Z0-9\_\-\@\.]+)$@D');
    }

    public function testPrepareChildrenRouteParams()
    {
        $params = [
            'route' => 'POST|/:userParameters/:secondParameters',
            'callback' => function ($zxc) {
            },
            'before' => function ($zxc, $parameters) {
            }
        ];

        $route = new Route($params);
        $params = $route->prepareParsedChildrenRouteParams([
            'route' => 'GET|profile|FakeClassForRouteTest:getUserProfile',
            'before' => 'FakeClassForRouteTest:beforeGetUserProfile',
            'children' => [
                'route' => 'POST|profile2',
                'callback' => function () {

                },
                'before' => function () {

                },
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
        ]);
        $this->assertSame($paramsWithCallback['route'], '/:userParameters/:secondParameters/profile2');
        $this->assertTrue(is_callable($paramsWithCallback['callback']));
        $this->assertTrue(is_callable($paramsWithCallback['before']));
    }

    /**
     * @method testExecuteRouteWithMethodsFromSingletonClass
     * @throws ReflectionException
     */
    public function testExecuteRouteWithMethodsFromSingletonClass()
    {
        $params = [
            'route' => 'POST|/:userParameters/:secondParameters|FakeClassForRouteTestSingleton:getUserProfile2',
            'before' => 'FakeClassForRouteTestSingleton:handler',
            'useCommonClassInstance' => true,
        ];

        $route = new Route($params);
        /**
         * @var ResponseInterface $response
         */
        $response = $route->executeRoute();
        $response->getBody()->rewind();
        $this->assertSame($response->getBody()->getContents(), 'handler');
    }

    /**
     * @method testRouteBeforeExpectedArgs
     * @throws ReflectionException
     */
    public function testRouteBeforeExpectedArgs()
    {
        $params = [
            'route' => 'POST|/user/:name',
            'before' => function ($request, $response, $next) {
                $this->assertTrue($next instanceof Closure);
                $this->assertTrue($request instanceof RequestInterface);
                $this->assertTrue($response instanceof ResponseInterface);
                $resultResponse = $next($request, $response);
                return $resultResponse;
            },
            'callback' => function ($request, $response, $params) {
                $this->assertTrue(is_array($params));
                $this->assertTrue($request instanceof RequestInterface);
                $this->assertTrue($response instanceof ResponseInterface);
                return $response;
            }
        ];
        $route = new Route($params);
        $response = $route->executeRoute();
        $this->assertTrue($response instanceof ResponseInterface);
    }

    /**
     * @method testRouteBeforeExpectedResult
     * @throws ReflectionException
     */
    public function testRouteBeforeExpectedResult()
    {
        $params = [
            'route' => 'POST|/user/:name',
            'before' => function (RequestInterface $request, ResponseInterface $response, Closure $next) {
                $response->getBody()->write('BEFORE');
                $resultResponse = $next($request, $response);
                $response->getBody()->write('AFTER');
                return $resultResponse;
            },
            'callback' => function (RequestInterface $request, ResponseInterface $response, $params) {
                $response->getBody()->write(' CALLBACK ');
                return $response;
            }
        ];
        $route = new Route($params);
        /**
         * @var ResponseInterface $response
         */
        $response = $route->executeRoute();
        $response->getBody()->rewind();
        $content = $response->getBody()->getContents();
        $this->assertSame('BEFORE CALLBACK AFTER', $content);
    }
}