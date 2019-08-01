<?php

namespace ZXC\Native;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use ZXC\ZXC;
use ReflectionException;
use ZXC\Interfaces\IModule;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Route
{
    private $requestMethod;
    private $routePath;
    private $regex;
    private $class;
    private $classMethod;
    private $callback;
    private $routeURIParams;
    private $before;
    private $children;
    private $commonClassInstance;
    private $middleware = [];
    private $middlewareStack;

    public function __construct(array $routeParams = [])
    {
        if (!$routeParams) {
            throw new InvalidArgumentException('Empty route params');
        }
        $this->parseRouteParams($routeParams);
    }

    public function parseRouteParams($routeParams)
    {
        $parsedParams = $this->parseRouteString($routeParams);

        $classAndMethod = ['class' => null, 'method' => null];
        if ($parsedParams['classAndMethod']) {
            $classAndMethod = $this->parseRouteClassString($parsedParams['classAndMethod']);
        }

        if (isset($routeParams['before'])) {
            if (is_callable($routeParams['before'])) {
                $this->before = $routeParams['before'];
            } elseif (is_array($routeParams['before'])) {
                $this->before = $routeParams['before'];
                foreach ($this->before as $name) {

                    $this->middleware[] = Router::getInstance()->getMiddlewareHandler($name);

//                    $this->middlewares[] = function (ServerRequestInterface $request, ResponseInterface $response, Closure $next) use ($name) {
//                        $md = Router::getInstance()->getMiddlewareHandler($name);
//                        $nr = Helper::callCallback($md, $request, $response, $next);
//                        return $nr;
//                    };
                }
            } else {
                $this->before = $this->parseRouteClassString($routeParams['before']);
            }
        }

        $this->regex = $this->createRegex($parsedParams['routePath']);
        $this->requestMethod = $parsedParams['requestMethod'];
        $this->routePath = $parsedParams['routePath'];
        $this->callback = $parsedParams['callback'];
        $this->class = $classAndMethod['class'];
        $this->classMethod = $classAndMethod['method'];

        if (isset($routeParams['children'])) {
            $this->children = $this->prepareParsedChildrenRouteParams($routeParams['children']);
        }
    }

    public function prepareParsedChildrenRouteParams($childrenParams)
    {
        if (!isset($childrenParams['route'])) {
            throw new InvalidArgumentException('Invalid $childrenParams');
        }

        $parsedRoute = $this->parseRouteString($childrenParams);
        $parsedRoute['routePath'] = $this->routePath . '/' . $parsedRoute['routePath'];
        if (!$parsedRoute['callback']) {
            $resultRoute = implode('|', $parsedRoute);
        } else {
            $resultRoute = $parsedRoute['routePath'];
        }
        $childrenParams['route'] = rtrim($resultRoute, '|');
        return $childrenParams;
    }

    /**
     * @param $routeParams
     * @return array [
     * 'requestMethod' => '',
     * 'routePath' => '',
     * 'classAndMethod' => '',
     * 'callback' => ''
     * ]
     */
    public function parseRouteString($routeParams)
    {
        $routeParams['route'] = preg_replace('!\s+!', '', $routeParams['route']);
        $params = explode('|', $routeParams['route']);
        $paramsCount = count($params);
        if (!$params || $paramsCount < 2) {
            throw new InvalidArgumentException('Invalid parameters for route ' . $routeParams['route']);
        }

        if ($paramsCount === 2 && !$routeParams['callback']) {
            throw new InvalidArgumentException('Invalid parameters for route ' . $routeParams['route']);
        }

        if ($params[1] !== '/') {
            $params[1] = rtrim($params[1], '/');
        }

        $callback = null;
        $classAndMethod = null;

        if ($paramsCount === 2 && $routeParams['callback']) {
            $callback = $routeParams['callback'];
        } elseif ($paramsCount > 2) {
            $classAndMethod = $params[2];
        }

        if (!$callback && !$classAndMethod) {
            throw new InvalidArgumentException('Invalid parameters for route ' . $routeParams['route']);
        }

        $result = [
            'requestMethod' => $params[0],
            'routePath' => $params[1],
            'classAndMethod' => $classAndMethod,
            'callback' => $callback
        ];

        return $result;
    }

    public function parseRouteClassString($classString)
    {
        if (!$classString) {
            ZXC::getInstance()->writeLog('Undefined $classString in before hook', ['classString' => $classString]);
            throw new InvalidArgumentException('Undefined $classString');
        }
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
    public function createRegex($pattern)
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
    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    /**
     * @return string
     */
    public function getRoutePath()
    {
        return $this->routePath;
    }

    /**
     * @return string
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getClassMethod()
    {
        return $this->classMethod;
    }

    /**
     * @return callable|bool
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @return mixed
     */
    public function getRouteURIParams()
    {
        return $this->routeURIParams;
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
        $ZXC = ZXC::getInstance();
        $request = $ZXC->getRequest();
        $response = $ZXC->getResponse();
        $mainHandlerParams = ['routeParams' => $this->routeURIParams];

        $mainHandler = function (RequestInterface $requestI, ResponseInterface $responseI) use ($mainHandlerParams) {
            if ($this->class && class_exists($this->class)) {
                if ($this->commonClassInstance && get_class($this->commonClassInstance) !== $this->class) {
                    $userClass = $this->createInstanceOfClass($this->class);
                } elseif ($this->commonClassInstance) {
                    $userClass = $this->commonClassInstance;
                } else {
                    $userClass = $this->createInstanceOfClass($this->class);
                }

                if (method_exists($userClass, $this->classMethod)) {
                    return call_user_func_array(
                        [$userClass, $this->classMethod],
                        [$requestI, $responseI, $mainHandlerParams]
                    );
                } else {
                    throw new InvalidArgumentException('Method ' . $this->classMethod . ' not exist in class ' . get_class($userClass));
                }
            } elseif (is_callable($this->callback)) {
                return call_user_func_array(
                    $this->callback, [$requestI, $responseI, $mainHandlerParams]
                );
            } else {
                throw new InvalidArgumentException('Main function or method is not defined for the route');
            }
        };

        $beforeHandlersArgs = [$request, $response, $mainHandler];

        if ($this->before) {
            if (is_array($this->before)) {
                if (isset($this->before['class'])) {
                    if (class_exists($this->before['class'])) {
                        if ($this->commonClassInstance && get_class($this->commonClassInstance) === $this->before['class']) {
                            $userClassBefore = $this->commonClassInstance;
                        } else {
                            $userClassBefore = $this->createInstanceOfClass($this->before['class']);
                        }
                        return call_user_func_array([$userClassBefore, $this->before['method']], $beforeHandlersArgs);
                    } else {
                        throw new InvalidArgumentException('Class ' . $this->before['class'] . ' is not defined');
                    }
                } else {
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
                    $response = $firstMiddleware($request, $response);
                    return $response;
                }
            } else {
                return call_user_func_array($this->before, $beforeHandlersArgs);
            }
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
            return ModulesManager::getModuleByClassName($className);
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
     * @return mixed
     */
    public function getBefore()
    {
        return $this->before;
    }
}