<?php

namespace ZXC;

require_once 'Native/Autoload.php';

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

            $configAutoloadDir = Config::get('ZXC/Autoload');
            if ($configAutoloadDir) {
                $autoloadInstance = Autoload::getInstance();
                $autoloadInstance->initialize($configAutoloadDir);
            }

            $loggerConfig = Config::get('ZXC/Logger');
            if ($loggerConfig) {
                $this->logger = new Logger($loggerConfig);
            }
        }


//
//        $this->http = HTTP::getInstance();
//
//        $loggerConfig = Config::get('ZXC/Logger');
//        if ($loggerConfig) {
//            $this->logger = new Logger($loggerConfig);
//        }
//
//        $routerParams = Config::get('ZXC/Router');
//        if ($routerParams) {
//            $this->router = Router::getInstance();
//            $this->router->initialize($routerParams);
//        } else {
//            throw new \InvalidArgumentException();
//        }
    }

    public function go()
    {
        /**
         * @var $routeParams Route
         */
        $routeParams = $this->router->getRoutParamsFromURI(
            $this->request->getPath(), $this->request->getBaseRoute(), $this->request->getMethod()
        );
        if (!$routeParams) {
            $this->request->sendHeader(404);
            return false;
        }
        ob_start();
        //TODO add codes for Exception
        try {
            $routeHandler = $routeParams->executeRoute($this);
            $body = ob_get_clean();
        } catch (\InvalidArgumentException $e) {
            $errorId = uniqid();
            $this->writeLog($e->getMessage() . ' |---> ' . $errorId);
            ob_end_clean();
            $body = '';
            $routeHandler = ['status' => 500, 'error' => $errorId];
        } catch (\Exception $e) {
            $errorId = uniqid();
            $this->writeLog($e->getMessage() . ' |---> ' . $errorId);
            ob_end_clean();
            $body = '';
            $routeHandler = ['status' => 500, 'error' => $errorId];
        }

        echo json_encode(['status' => 200, 'body' => $body, 'handler' => $routeHandler]);
        return true;
    }

    public function writeLog($msg = '', $param = []): bool
    {
        if (!$this->logger) {
            return false;
        }
        if ($this->logger->getLevel() !== 'debug') {
            return false;
        }
        $this->logger->debug($msg, $param);
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
}