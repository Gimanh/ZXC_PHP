<?php

namespace ZXC\Native;

use ZXC\ZXC;
use ReflectionException;
use ZXC\Patterns\Singleton;
use ZXC\Native\PSR\Response;
use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;

class Router
{
    use Singleton;
    private $middleware = null;
    private $routes = [];
    protected $preflight = null;
    private $allowedMethods = ['POST' => true, 'GET' => true, 'OPTIONS' => true];

    /**
     * Initialize router
     * @param array $config [
     *      [
     *          'route' => 'GET|/api|Class:method',
     *          'callback' => function ($zxc) { },
     *          'before' => 'ClassName:beforeMethod',
     *          'after' => 'ClassName:afterMethod',
     *          'hooksResultTransfer'=> true
     *      ]
     *  ]
     * @return bool
     */
    public function initialize(array $config = [])
    {
        if (!$config || !isset($config['routes'])) {
            throw new InvalidArgumentException('Undefined routes in Router config');
        }
        if (isset($config['methods'])) {
            $this->allowedMethods = $config['methods'];
        }
        if (isset($config['middleware'])) {
            $this->middleware = $config['middleware'];
        }
        if (isset($config['preflight'])) {
            $this->preflight = $config['preflight'];
        }
        foreach ($config['routes'] as $routeParams) {
            $this->initializeRouteInstance($routeParams);
        }

        return true;
    }

    /**
     * @method callMiddleware
     * @param $name
     * @return mixed|null
     */
    public function getMiddlewareHandler($name)
    {
        if (isset($this->middleware[$name])) {
            return $this->middleware[$name];
        }
        return null;
    }

    public function initializeRouteInstance($routeParams)
    {
        $routeInstance = new Route($routeParams);
        $this->registerRouteInstance($routeInstance);
        $children = $routeInstance->getChildren();
        if ($children) {
            $this->initializeRouteInstance($children);
        }

        return true;
    }

    private function registerRouteInstance(Route $parsedRoute)
    {
        $parsedRouteType = $parsedRoute->getRequestMethod();
        if (isset($this->allowedMethods[$parsedRouteType]) && $this->allowedMethods[$parsedRouteType] === true) {
            $this->routes[$parsedRouteType][$parsedRoute->getRoutePath()] = $parsedRoute;
        }

        return $parsedRoute;
    }


    /**
     * @param string $type (GET,POST)
     * @return bool
     */
    public function disableRouterType($type)
    {
        $type = strtoupper($type);
        if (isset($this->allowedMethods[$type])) {
            $this->allowedMethods[$type] = false;

            return true;
        }

        return false;
    }

    public function enableRouterType($type)
    {
        $type = strtoupper($type);
        if (isset($this->allowedMethods[$type])) {
            $this->allowedMethods[$type] = true;

            return true;
        }

        return false;
    }

    /**
     * Returns all registered route types
     * @return array
     */
    public function getAllowedMethods()
    {
        return $this->allowedMethods;
    }

    /**
     * Returns all registered routes
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param string $requestPath
     * @param string $requestMethod http->getMethod
     * @return Route|boolean
     */
    public function getRouteWithParamsFromURI($requestPath, $requestMethod)
    {
        if (!$requestPath || !$requestMethod) {
            throw new InvalidArgumentException('Invalid  request path or request method');
        }

        if (!isset($this->routes[$requestMethod])) {
            throw new InvalidArgumentException('Invalid requestMethod ' . $requestMethod . ' set ' . $requestMethod . '=>true in config file');
        }
        /**
         * @var $route Route
         */
        foreach ($this->routes[$requestMethod] as $route) {
            $ok = preg_match($route->getRegex(), $requestPath, $matches);
            if ($ok) {
                $params = array_intersect_key(
                    $matches,
                    array_flip(
                        array_filter(
                            array_keys($matches),
                            'is_string'
                        )
                    )
                );
                if ($params) {
                    $route->setRouteURIParams($params);
                }

                return $route;
            }
        }
        return false;
    }

    public function getNormalizedPath(UriInterface $uri)
    {
        $path = $uri->getPath();
        $baseRoute = dirname($_SERVER['SCRIPT_NAME']);
        if ($path !== '/' && $baseRoute !== '/') {
            $lastSlash = substr($path, -1);
            if ($lastSlash === '/') {
                $path = rtrim($path, '/');
            }
            if ($path === $baseRoute) {
                $path = '/';
            } elseif ($path !== $baseRoute) {
                $position = strpos($path, $baseRoute);
                if ($position === 0) {
                    $path = str_replace($baseRoute, '', $path);
                }
            }
        }
        return $path;
    }

    /**
     * @return ResponseInterface
     * @throws ReflectionException
     * @method go
     */
    public function go()
    {
        $ZXC = ZXC::getInstance();
        $serverRequest = $ZXC->getRequest();
        $method = $serverRequest->getMethod();
        if ($method === 'OPTIONS') {
            if (isset($this->allowedMethods[$method])) {
                if ($this->allowedMethods[$method] === true) {
                    return $this->callPreflight();
                }
            }
        }
        $routeParams = $this->getRouteWithParamsFromURI($this->getNormalizedPath($serverRequest->getUri()), $method);
        if (!$routeParams) {
            return $this->callNotFound();
        }
        $routeHandler = $routeParams->executeRoute();
        if ($routeHandler instanceof ResponseInterface) {
            return $routeHandler;
        } else {
            return $ZXC->getResponse()->write((string)$routeHandler);
        }
    }

    /**
     * @return mixed
     * @throws ReflectionException
     * @method callNotFound
     */
    private function callNotFound()
    {
        $ZXC = ZXC::getInstance();
        $response = $ZXC->getResponse();
        if ($ZXC->getNotFoundHandler()) {
            return Helper::callCallback($ZXC->getNotFoundHandler(), $response);
        } else {
            return $response->withStatus(404)->write('Not Found');
        }
    }

    /**
     * @return ResponseInterface|Response
     * @throws ReflectionException
     * @method callPreflight
     */
    public function callPreflight()
    {
        if ($this->preflight) {
            return Helper::callCallback($this->preflight, ZXC::getInstance()->getRequest(), ZXC::getInstance()->getResponse());
        }
        return ZXC::getInstance()->getResponse()->withStatus(400);
    }
}