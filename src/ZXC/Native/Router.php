<?php


namespace ZXC\Native;

use ZXC\ZXC;
use ReflectionException;
use ZXC\Patterns\Singleton;
use ZXC\Native\PSR\Response;
use InvalidArgumentException;
use ZXC\Interfaces\Psr\Http\Message\UriInterface;
use ZXC\Interfaces\Psr\Http\Message\ResponseInterface;


class Router
{
    use Singleton;

    /**
     * Routes from config
     * @var array
     */
    protected $routes = [];

    /**
     * Preflight request handler
     * @var null
     */
    protected $preflight = null;

    /**
     * Named middlewares
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var null|callable|string
     */
    protected $notFoundHandler = null;

    public function prepare(array $config = [])
    {
        if (!isset($config['routes'])) {
            throw new InvalidArgumentException('Undefined routes in Router config');
        }

        $this->middlewares = $config['middlewares'] ?? [];

        $this->preflight = $config['preflight'] ?? null;

        $notFound = function () {
            return ZXC::response()->withStatus(404);
        };
        $this->notFoundHandler = $config['notFoundHandler'] ?? $notFound;

        foreach ($config['routes'] as $routeParams) {
            $this->initRoute($routeParams);
        }

        return true;
    }

    /**
     * @method callMiddleware
     * @param $name
     * @return string|null
     */
    public function getMiddlewareHandler($name)
    {
        if (isset($this->middlewares[$name])) {
            return $this->middlewares[$name];
        }
        return null;
    }

    public function initRoute($routeParams)
    {
        $routeInstance = new Route($routeParams);
        $this->registerRouteInstance($routeInstance);
        $children = $routeInstance->getChildren();
        if ($children) {
            $this->initRoute($children);
        }

        return true;
    }

    private function registerRouteInstance(Route $parsedRoute)
    {
        $this->routes[$parsedRoute->getMethod()][$parsedRoute->getRoutePath()] = $parsedRoute;
        return $parsedRoute;
    }

    /**
     * @return false|Route
     */
    public function getUriRoute()
    {
        $path = $this->getNormalizedPath(ZXC::request()->getUri());
        $method = ZXC::request()->getMethod();
        if (!isset($this->routes[$method])) {
            throw new InvalidArgumentException('Invalid requestMethod ' . $method . ' set ' . $method . '=>true in config file');
        }
        /**@var $route Route */
        foreach ($this->routes[$method] as $route) {
            $isValidRouteParams = $route->isThisYourPath($path);
            if($isValidRouteParams !== false){
                $route->setRouteURIParams($isValidRouteParams ?? []);
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
     * @return mixed|ResponseInterface|Response
     * @throws ReflectionException
     */
    public function go()
    {
        $serverRequest = ZXC::request();
        $method = $serverRequest->getMethod();
        if ($method === 'OPTIONS') {
            return $this->callPreflight();
        }
        $routeParams = $this->getUriRoute();
        if (!$routeParams) {
            return $this->callNotFound();
        }
        $routeHandler = $routeParams->executeRoute();
        if ($routeHandler instanceof ResponseInterface) {
            return $routeHandler;
        } else {
            return ZXC::response()->write((string)$routeHandler);
        }
    }

    /**
     * @return mixed
     * @throws ReflectionException
     * @method callNotFound
     */
    private function callNotFound()
    {
        if ($this->notFoundHandler) {
            return Helper::callCallback(
                $this->notFoundHandler,
                ZXC::request(),
                ZXC::response()
            );
        } else {
            return ZXC::response()->withStatus(404);
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
            return Helper::callCallback($this->preflight, ZXC::request(), ZXC::response());
        }
        return ZXC::response()->withStatus(400);
    }
}
