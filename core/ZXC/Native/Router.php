<?php

namespace ZXC\Native;

use ZXC\Patterns\Singleton;

class Router
{
    use Singleton;
    private $routes = [];
    private $routeTypes = ['POST' => true, 'GET' => true];

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
        if (!$config) {
            throw new \InvalidArgumentException('Undefined $params');
        }

        foreach ($config as $routeParams) {
            $this->initializeRouteInstance($routeParams);
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
        if (isset($this->routeTypes[$parsedRouteType]) && $this->routeTypes[$parsedRouteType] === true) {
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
        if (isset($this->routeTypes[$type])) {
            $this->routeTypes[$type] = false;

            return true;
        }

        return false;
    }

    public function enableRouterType($type)
    {
        $type = strtoupper($type);
        if (isset($this->routeTypes[$type])) {
            $this->routeTypes[$type] = true;

            return true;
        }

        return false;
    }

    /**
     * Returns all registered route types
     * @return array
     */
    public function getRouteTypes(): array
    {
        return $this->routeTypes;
    }

    /**
     * Returns all registered routes
     * @return array
     */
    public function getRoutes(): array
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