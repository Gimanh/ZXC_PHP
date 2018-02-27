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
     */
    public function initialize(array $config = [])
    {
        if (!$config) {
            throw new \InvalidArgumentException('Undefined $params');
        }

        foreach ($config as $routeParams) {
            $this->initializeRouteInstance($routeParams);
        }
    }

    public function initializeRouteInstance($routeParams)
    {
        $routeInstance = new Route($routeParams);
        $this->setRoute($routeInstance);
        $children = $routeInstance->getChildren();
        if ($children) {
            $this->initializeRouteInstance($children);
        }
    }

    /**
     * Set parsed route in $this->routes type(GET,POST)
     * @param Route $parsedRoute
     */
    private function setRoute(Route $parsedRoute)
    {
        $parsedRouteType = $parsedRoute->getRequestMethod();
        if (isset($this->routeTypes[$parsedRouteType]) && $this->routeTypes[$parsedRouteType] === true) {
            $this->routes[$parsedRouteType][$parsedRoute->getRoutePath()] = $parsedRoute;
        }
    }


    /**
     * Disable router type
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

    /**
     * Get router parameters for URI
     * @param string $uri http->getPath
     * @param string $base http->getBaseRoute
     * @param string $method http->getMethod
     * @return bool|Route
     */
    public function getCurrentRoutParams($uri, $base, $method)
    {
        if (!$uri || !$base || !$method) {
            throw new \InvalidArgumentException('Undefined $params');
        }
        if (!isset($this->routes[$method])) {
            return false;
        }
        if ($base != '/') {
            $path = substr($uri, strlen($base));
        } else {
            $path = $uri;
        }
        /**
         * @var $route Route
         */
        foreach ($this->routes[$method] as $route) {
            $ok = preg_match($route->getRegex(), $path, $matches);
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
                    $route->setParams($params);
                }

                return $route;
            }
        }

        return false;
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
     * Returns all registered route types
     * @return array
     */
    public function getRouteTypes(): array
    {
        return $this->routeTypes;
    }
}