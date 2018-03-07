<?php

namespace ZXC\Native;

use ZXC\ZXC;

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
    private $after;
    private $hooksResultTransfer;
    private $children;

    public function __construct(array $routeParams = [])
    {
        if (!$routeParams) {
            throw new \InvalidArgumentException('Empty route params');
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
            } else {
                $this->before = $this->parseRouteClassString($routeParams['before']);
            }
        }

        if (isset($routeParams['after'])) {
            if (is_callable($routeParams['after'])) {
                $this->after = $routeParams['after'];
            } else {
                $this->after = $this->parseRouteClassString($routeParams['after']);
            }
        }

        $this->regex = $this->createRegex($parsedParams['routePath']);
        $this->requestMethod = $parsedParams['requestMethod'];
        $this->routePath = $parsedParams['routePath'];
        $this->callback = $parsedParams['callback'];
        $this->class = $classAndMethod['class'];
        $this->classMethod = $classAndMethod['method'];
        $this->hooksResultTransfer = isset($routeParams['hooksResultTransfer']) ? $routeParams['hooksResultTransfer'] : null;

        if (isset($routeParams['children'])) {
            $this->children = $this->prepareParsedChildrenRouteParams($routeParams['children']);
        }
    }

    public function prepareParsedChildrenRouteParams($childrenParams)
    {
        if (!isset($childrenParams['route'])) {
            throw new \InvalidArgumentException('Invalid $childrenParams');
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
        $params = explode('|', $routeParams['route']);
        $paramsCount = count($params);
        if (!$params || $paramsCount < 2) {
            throw new \InvalidArgumentException('Invalid parameters for route ' . $routeParams['route']);
        }

        if ($paramsCount === 2 && !$routeParams['callback']) {
            throw new \InvalidArgumentException('Invalid parameters for route ' . $routeParams['route']);
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
            throw new \InvalidArgumentException('Invalid parameters for route ' . $routeParams['route']);
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
            throw new \InvalidArgumentException('Undefined $classString');
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
            throw new \InvalidArgumentException('Invalid pattern');
        } // Invalid pattern

        // Turn "(/)" into "/?"
        $pattern = preg_replace('#\(/\)#', '/?', $pattern);

        // Create capture group for ":parameter"
        $allowedParamChars = '[a-zA-Z0-9\_\-]+';
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
     * @param mixed $routeURIParams //TODO add real type
     */
    public function setRouteURIParams($routeURIParams)
    {
        $this->routeURIParams = $routeURIParams;
    }

    /**
     * Execute route
     * @param ZXC $zxc
     * @return bool|mixed
     */
    public function executeRoute($zxc)
    {
        $outputFunctionsResult = false;
        $resultFromMainMethod = null;
        $resultFromBeforeMethod = null;
        $resultFromAfterMethod = null;
        $paramsForSecondRouteArguments = [];
        $paramsForSecondRouteArguments['routeParams'] = $this->routeURIParams;
        if ($this->class) {
            if (!class_exists($this->class)) {
                $zxc = ZXC::getInstance();
                $logger = $zxc->getLogger();
                $logger->critical('Class ' . $this->class . ' not exist');
            }
            if (is_subclass_of($this->class, 'ZXC\Factory', true) ||
                $this->classUsesTrait($this->class, 'ZXC\Patterns\Singleton')) {
                $userClass = call_user_func(
                    $this->class . '::getInstance'
                );
                $outputFunctionsResult = call_user_func_array(
                    [$userClass, $this->classMethod],
                    [$zxc, $paramsForSecondRouteArguments]
                );
            } else {
                if (class_exists($this->class)) {
                    $userClass = new $this->class;
                    if (is_subclass_of($this->class, 'ZXC\Interfaces\Module', true)) {
                        if (method_exists($userClass, 'initialize')) {
                            $userClass->initialize();
                        }
                    }
                    $resultFromBeforeMethod = $this->callBefore($zxc, $userClass);
                    if (method_exists($userClass, $this->classMethod)) {
                        if ($this->hooksResultTransfer) {
                            $paramsForSecondRouteArguments['resultBefore'] = $resultFromBeforeMethod;
                            $resultFromMainMethod = call_user_func_array(
                                [$userClass, $this->classMethod],
                                [$zxc, $paramsForSecondRouteArguments]
                            );
                            $outputFunctionsResult = $this->callAfter($zxc, $resultFromMainMethod, $userClass);
                        } else {
                            $outputFunctionsResult = call_user_func_array(
                                [$userClass, $this->classMethod],
                                [$zxc, $paramsForSecondRouteArguments]
                            );
                            $this->callAfter($zxc, null, $userClass);
                        }
                    }
                }
            }
        } elseif (is_callable($this->callback)) {
            //TODO check double initialize when we are using before and after hooks from same class (we are colling __construct twice)
            $resultFromBeforeMethod = $this->callBefore($zxc);
            if ($this->hooksResultTransfer) {
                $paramsForSecondRouteArguments['resultBefore'] = $resultFromBeforeMethod;
                $resultFromMainMethod = call_user_func_array(
                    $this->callback, [$zxc, $paramsForSecondRouteArguments]
                );
                $outputFunctionsResult = $this->callAfter($zxc, $resultFromMainMethod);
            } else {
                $outputFunctionsResult = call_user_func_array(
                    $this->callback, [$zxc, $paramsForSecondRouteArguments]
                );
                $this->callAfter($zxc);
            }
        } else {
            throw new \InvalidArgumentException('Main function or method is not defined for the route');
        }
        return $outputFunctionsResult;
    }

    private function callBefore(ZXC $zxc, $mainClass = null)
    {
        $paramsForSecondRouteArguments['routeParams'] = $this->routeURIParams;
        $resultBefore = null;
        if ($this->before) {
            if (is_array($this->before)) {
                if (class_exists($this->before['class'])) {
                    if ($mainClass && get_class($mainClass) === $this->before['class']) {
                        $userClassBefore = $mainClass;
                    } else {
                        $userClassBefore = new $this->before['class'];
                    }
                    if ($this->hooksResultTransfer) {
                        $resultBefore = call_user_func_array(
                            [$userClassBefore, $this->before['method']],
                            [$zxc, $paramsForSecondRouteArguments]
                        );
                    } else {
                        call_user_func_array(
                            [$userClassBefore, $this->before['method']],
                            [$zxc, $paramsForSecondRouteArguments]
                        );
                    }
                }
            } else {
                if ($this->hooksResultTransfer) {
                    $resultBefore = call_user_func_array(
                        $this->before, [$zxc, $paramsForSecondRouteArguments]
                    );
                } else {
                    call_user_func_array(
                        $this->before, [$zxc, $paramsForSecondRouteArguments]
                    );
                }
            }
        }
        return $resultBefore;
    }

    private function callAfter(ZXC $zxc, $resultMainMethod = null, $mainClass = null)
    {
        $paramsForSecondRouteArguments['routeParams'] = $this->routeURIParams;
        $paramsForSecondRouteArguments['resultMain'] = $resultMainMethod;

        if ($this->after) {
            if (is_array($this->after)) {
                if (class_exists($this->after['class'])) {

                    if ($mainClass && get_class($mainClass) === $this->after['class']) {
                        $userClassBefore = $mainClass;
                    } else {
                        $userClassBefore = new $this->after['class'];
                    }
                    call_user_func_array(
                        [$userClassBefore, $this->after['method']],
                        [$zxc, $paramsForSecondRouteArguments]
                    );
                }
            } else {
                call_user_func_array(
                    $this->after, [$zxc, $paramsForSecondRouteArguments]
                );
            }
        }
        return true;
    }

    public function classUsesTrait($className, string $traitName)
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

    /**
     * @return mixed
     */
    public function getAfter()
    {
        return $this->after;
    }

    /**
     * @return mixed
     */
    public function getHooksResultTransfer()
    {
        return $this->hooksResultTransfer;
    }
}