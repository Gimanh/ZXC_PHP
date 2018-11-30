<?php

namespace ZXC\Native;

use ZXC\Patterns\Singleton;
use ZXC\ZXC;

class Router
{
    use Singleton;
    private $middleware = null;
    private $routes = [];
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
            throw new \InvalidArgumentException('Undefined routes in Router config');
        }
        if (isset($config['methods'])) {
            $this->allowedMethods = $config['methods'];
        }
        if (isset($config['middleware'])) {
            $this->middleware = $config['middleware'];
        }
        foreach ($config['routes'] as $routeParams) {
            $this->initializeRouteInstance($routeParams);
        }

        return true;
    }

    public function callMiddleware()
    {
        if (!$this->middleware) {
            return false;
        }
        foreach ($this->middleware as $key => $value) {
            if (is_callable($this->middleware[$key])) {
                call_user_func_array($this->middleware[$key], [ZXC::getInstance()]);
            } else {
                $classMethod = explode(':', $this->middleware[$key]);
                $class = $classMethod[0];
                $method = $classMethod[1];
                $class = Helper::createInstanceOfClass($class);
                if (!$class) {
                    throw new \InvalidArgumentException('Can not create Instance Of Class ' . $class);
                }
                call_user_func_array([$class, $method], [ZXC::getInstance()]);
            }
        }
        return true;
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
            throw new \InvalidArgumentException('Invalid  request path or request method');
        }

        if (!isset($this->routes[$requestMethod])) {
            throw new \InvalidArgumentException('Invalid requestMethod ' . $requestMethod);
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
        throw new \InvalidArgumentException('Route "' . $requestPath . '" not found');
    }
}