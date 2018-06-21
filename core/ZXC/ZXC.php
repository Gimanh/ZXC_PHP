<?php

namespace ZXC;

require_once 'Native/Autoload.php';

use ZXC\Native\HTTP\Response;
use ZXC\Native\Route;
use ZXC\Patterns\Singleton;
use ZXC\Native\Autoload;
use ZXC\Native\HTTP\Request;
use ZXC\Classes\Config;
use ZXC\Native\Logger;
use ZXC\Native\Router;

class ZXC
{
    use Singleton;
    private $version = '0.0.1-a';
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Router
     */
    private $router;

    /**
     * @param array $config
     */
    function initialize(array $config = [])
    {
        if ($config) {
            $configInstance = Config::getInstance();
            $configInstance->initialize($config);

            $haveConfigForAutoloadDir = Config::get('ZXC/Autoload');
            if ($haveConfigForAutoloadDir) {
                $autoloadInstance = Autoload::getInstance();
                $autoloadInstance->initialize($haveConfigForAutoloadDir);
            }

            $haveLoggerConfig = Config::get('ZXC/Logger');
            if ($haveLoggerConfig) {
                $this->logger = new Logger($haveLoggerConfig);
            }
            $haveRouterConfig = Config::get('ZXC/Router');
            if ($haveRouterConfig) {
                $this->router = Router::getInstance();
                $this->router->initialize($haveRouterConfig);
            }

            $this->request = Request::getInstance();
            if ($this->haveServerParametersForWorking()) {
                $this->request->initialize($_SERVER);
            }
        }
    }

    public function haveServerParametersForWorking()
    {
        return isset($_SERVER['HTTP_HOST']) && isset($_SERVER['SERVER_NAME']) &&
            isset($_SERVER['SERVER_PORT']) && isset($_SERVER['REQUEST_METHOD']) &&
            isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SERVER_PROTOCOL']);
    }

    public function writeLog($msg = '', $param = []): bool
    {
        if (!$this->logger) {
            return false;
        }
        $this->logger->info($msg, $param);
        return true;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    public function go()
    {
        ob_start();
        $body = '';
        $routeHandler = '';
        try {
            /**
             * @var $routeParams Route
             */
            if (!$this->router) {
                throw new \Exception('Router config is not defined');
            }
            $routeParams = $this->router->getRouteWithParamsFromURI($this->request->getPath(),
                $this->request->getMethod());
            if (!$routeParams) {
                throw new \Exception('Can not get router params');
            }
            $routeHandler = $routeParams->executeRoute($this);
            Response::setResponseHttpCode(200);
            $body = ob_get_clean();
        } catch (\InvalidArgumentException $e) {
            Response::setResponseHttpCode(500);
            $this->writeLog($e->getMessage() . ' ' . uniqid());
            ob_end_clean();
        } catch (\Exception $e) {
            Response::setResponseHttpCode(500);
            $this->writeLog($e->getMessage() . ' ' . uniqid());
            ob_end_clean();
        }
        Response::addHeaders(['Content-Type' => ['application/json']]);
        echo Response::sendResponse($body, $routeHandler);
        return true;
    }
}