<?php


namespace ZXC\Native;

use ZXC\ZXC;
use ReflectionException;
use ZXC\Interfaces\IModule;
use InvalidArgumentException;
use ZXC\Interfaces\Psr\Http\Message\RequestInterface;
use ZXC\Interfaces\Psr\Http\Message\ResponseInterface;
use ZXC\Interfaces\Psr\Http\Message\ServerRequestInterface;

class Route
{
    /**
     * @var string
     */
    private $regex;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $routePath;

    /**
     * @var string
     */
    private $classMethod;

    /**
     * @var string
     */
    private $requestMethod;

    private $routeURIParams;

    private $children;

    private $middleware = [];

    private $middlewareStack;

    public function __construct(array $routeParams)
    {
        $this->parseRouteParams($routeParams);
    }

    public function parseRouteParams(array $routeParams)
    {
        $parsedParams = $this->cleanRoutePath($routeParams);
        $classAndMethod = $this->parseRouteClassString($parsedParams['handler']);
        if (isset($routeParams['middleware'])) {
            if (is_array($routeParams['middleware'])) {
                foreach ($routeParams['middleware'] as $name) {
                    $this->middleware[] = Router::instance()->getMiddlewareHandler($name) ?? $name;
                }
            }
        }
        $this->regex = $this->createRegex($parsedParams['route']);
        $this->requestMethod = $parsedParams['method'];
        $this->routePath = $parsedParams['route'];
        $this->class = $classAndMethod['class'];
        $this->classMethod = $classAndMethod['method'];

        if (isset($routeParams['children'])) {
            $this->children = $this->prepareChildren($routeParams['children']);
        }
    }

    /**
     * @param $childrenParams
     * @return array
     */
    public function prepareChildren($childrenParams)
    {
        if (!isset($childrenParams['route'])) {
            throw new InvalidArgumentException('Invalid $childrenParams');
        }

        if (!isset($childrenParams['method'])) {
            $childrenParams['method'] = $this->requestMethod;
        }

        $parsedRoute = $this->cleanRoutePath($childrenParams);

        if ($this->routePath !== '/') {
            $parsedRoute['route'] = $this->routePath . '/' . $parsedRoute['route'];
        } else {
            $parsedRoute['route'] = $this->routePath . $parsedRoute['route'];
        }

        return $parsedRoute;
    }

    public function cleanRoutePath(array $routeParams)
    {
        $routeParams['route'] = preg_replace('!\s+!', '', $routeParams['route']);
        return $routeParams;
    }

    public function parseRouteClassString(string $classString)
    {
        $classAndMethod = explode(':', $classString);
        if (!$classAndMethod || count($classAndMethod) !== 2) {
            return [
                'class' => null,
                'method' => null
            ];
        }
        return [
            'class' => $classAndMethod[0],
            'method' => $classAndMethod[1]
        ];
    }

    /**
     * @param $pattern
     *
     * @return bool|string
     * @link Thanks https://stackoverflow.com/questions/30130913/how-to-do-url-matching-regex-for-routing-framework/30359808#30359808
     */
    public function createRegex(string $pattern): string
    {
        if (preg_match('/[^-:\/_{}()a-zA-Z\d]/', $pattern)) {
            throw new InvalidArgumentException('Invalid pattern');
        } // Invalid pattern

        // Turn "(/)" into "/?"
        $pattern = preg_replace('#\(/\)#', '/?', $pattern);

        // Create capture group for ":parameter"
        $allowedParamChars = '[a-zA-Z0-9\_\-\@\.]+';
        $pattern = preg_replace(
            '/:(' . $allowedParamChars . ')/',   # Replace ":parameter"
            '(?<$1>' . $allowedParamChars . ')',
            # with "(?<parameter>[a-zA-Z0-9\_\-]+)"
            $pattern
        );

        // Create capture group for '{parameter}'
        $pattern = preg_replace(
            '/{(' . $allowedParamChars . ')}/',    # Replace "{parameter}"
            '(?<$1>' . $allowedParamChars . ')',
            # with "(?<parameter>[a-zA-Z0-9\_\-]+)"
            $pattern
        );

        // Add start and end matching
        $patternAsRegex = "@^" . $pattern . "$@D";

        return $patternAsRegex;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->requestMethod;
    }

    /**
     * @return string
     */
    public function getRoutePath(): string
    {
        return $this->routePath;
    }

    /**
     * @return string
     */
    public function getRegex(): string
    {
        return $this->regex;
    }

    /**
     * @param mixed $routeURIParams
     */
    public function setRouteURIParams($routeURIParams)
    {
        $this->routeURIParams = $routeURIParams;
    }

    /**
     * Execute route
     * @return ResponseInterface
     * @throws ReflectionException
     */
    public function executeRoute()
    {
        $ZXC = ZXC::instance();
        $request = $ZXC->getRequest();
        $response = $ZXC->getResponse();

        $mainHandlerParams = new RouteParams($this->routeURIParams);

        $mainHandler = function (RequestInterface $requestI, ResponseInterface $responseI) use ($mainHandlerParams) {
            if ($this->class && class_exists($this->class)) {
                $userClass = $this->createInstanceOfClass($this->class);
                if (method_exists($userClass, $this->classMethod)) {
                    return call_user_func_array(
                        [$userClass, $this->classMethod],
                        [$requestI, $responseI, $mainHandlerParams]
                    );
                } else {
                    throw new InvalidArgumentException('Method ' . $this->classMethod . ' not exist in class ' . get_class($userClass));
                }
            } else {
                throw new InvalidArgumentException('Main handler or method is not defined for the route');
            }
        };
        if ($this->middleware) {
            $middlewareCount = count($this->middleware);
            for ($i = $middlewareCount - 1; $i >= 0; $i--) {
                $name = $this->middleware[$i];
                if (!$this->middlewareStack) {
                    $this->middlewareStack = function (ServerRequestInterface $request, ResponseInterface $response) use ($mainHandler) {
                        return $mainHandler($request, $response);
                    };
                }
                $next = $this->middlewareStack;
                $this->middlewareStack = function (ServerRequestInterface $request, ResponseInterface $response) use ($name, $next) {
                    return Helper::callCallback($name, $request, $response, $next);
                };
            }
            $firstMiddleware = $this->middlewareStack;
            return $firstMiddleware($request, $response);
        } else {
            return $mainHandler($request, $response);
        }
    }

    /**
     * @param $className
     * @method createInstanceOfClass
     * @return mixed|IModule|null
     * @throws ReflectionException
     */
    public function createInstanceOfClass($className)
    {
        if (!$className) {
            throw new InvalidArgumentException();
        }
        if ($this->classUsesTrait($className, 'ZXC\Patterns\Singleton')) {
            return call_user_func($className . '::getInstance');
        }
        if (is_subclass_of($className, 'ZXC\Interfaces\IModule')) {
            return Modules::getByClassName($className);
        }
        return new $className;
    }

    public function classUsesTrait($className, $traitName)
    {
        $traits = class_uses($className, true);
        if ($traits) {
            return in_array($traitName, $traits, true);
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param string $path
     * @return array|false
     */
    public function isThisYourPath(string $path)
    {
        if (preg_match($this->getRegex(), $path, $matches)) {
            return array_intersect_key(
                $matches,
                array_flip(
                    array_filter(
                        array_keys($matches),
                        'is_string'
                    )
                )
            );
        }
        return false;
    }
}
