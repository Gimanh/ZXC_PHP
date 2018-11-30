<?php

namespace ZXC;

require_once 'Native/Autoload.php';

use ZXC\Modules\Logger\Logger;
use ZXC\Native\HTTP\Response;
use ZXC\Native\I18N;
use ZXC\Modules\Mailer\Mail;
use ZXC\Native\ModulesManager;
use ZXC\Native\Route;
use ZXC\Patterns\Singleton;
use ZXC\Native\Autoload;
use ZXC\Native\HTTP\Request;
use ZXC\Native\Config;
use ZXC\Native\Router;

class ZXC implements Interfaces\ZXC
{
    use Singleton;


    private $version = '0.0.1-a';
    /**
     * @var Request
     */
    private $request = null;
    /**
     * @var Logger
     */
    private $logger = null;
    /**
     * @var Router
     */
    private $router = null;
    /**
     * Not found handler
     * @var null
     */
    private $notFound = null;
    /**
     * @var string|callable
     */
    private $responseCreator = null;

    private $logFileName = 'ZXC_SYS.log';

    /**
     * @param array $config
     */
    public function initialize(array $config = null)
    {
        if ($config) {
            Config::initialize($config);

            $haveRouterConfig = Config::get('ZXC/Router');
            if (!$haveRouterConfig) {
                throw new \InvalidArgumentException('ZXC/Router is not defined');
            }

            $this->router = Router::getInstance();
            $this->router->initialize($haveRouterConfig);

            $modules = Config::get('ZXC/Modules');
            ModulesManager::installModules($modules);

            $this->logger = ModulesManager::getNewModule('Logger');
            if ($this->logger) {
                $this->logger->setLogsFolder(Config::get('ZXC/Modules/Logger/options/folder'));
                $this->logger->setLogFileName($this->logFileName);
            }

            $haveConfigForAutoloadDir = Config::get('ZXC/Autoload');
            if ($haveConfigForAutoloadDir) {
                $autoloadInstance = Autoload::getInstance();
                $autoloadInstance->initialize($haveConfigForAutoloadDir);
            }

            $this->request = Request::getInstance();
            if ($this->haveServerParametersForWorking()) {
                $this->request->initialize($_SERVER);
            }

            $this->detectLanguage();
        }
    }

    public function detectLanguage()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            $acceptLang = Config::get('ZXC/Lang');
            if (!$acceptLang) {
                $acceptLang = ['en'];
            }
            $lang = in_array($lang, $acceptLang) ? $lang : null;
            $localeFile = ZXC_ROOT . DIRECTORY_SEPARATOR . 'locales' . DIRECTORY_SEPARATOR . $lang . '.php';
            if (file_exists($localeFile)) {
                $locale = require_once $localeFile;
                I18N::initialize($locale);
            }
        }
    }

    public function haveServerParametersForWorking()
    {
        return isset($_SERVER['HTTP_HOST']) && isset($_SERVER['SERVER_NAME']) &&
            isset($_SERVER['SERVER_PORT']) && isset($_SERVER['REQUEST_METHOD']) &&
            isset($_SERVER['REQUEST_URI']) && isset($_SERVER['SERVER_PROTOCOL']);
    }

    public function writeLog($msg = '', $param = [])
    {
        if (!$this->logger) {
            return false;
        }
        $this->logger->info($msg, $param);
        return true;
    }

    /**
     * @param $lvl
     * @param $message
     * @param $file
     * @param $line
     * @return bool
     * @throws \ErrorException
     */
    public function errorHandler($lvl, $message, $file, $line)
    {
        if ($this->logger) {
            $this->logger->error($message, [
                'lvl' => $lvl,
                'file' => $file,
                'line' => $line
            ]);
        }
        if (!(error_reporting() & $lvl)) {
            // Этот код ошибки не включен в error_reporting,
            // так что пусть обрабатываются стандартным обработчиком ошибок PHP
            return false;
        }
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }
        throw new \ErrorException($message, 0, $lvl, $file, $line);
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    public function go()
    {
        set_error_handler([$this, 'errorHandler']);

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
            $this->router->callMiddleware();
            $method = $this->request->getMethod();
            $routeParams = $this->router->getRouteWithParamsFromURI($this->request->getPath(), $method);
            if (!$routeParams) {
                throw new \Exception('Can not get router params');
            }
            $routeHandler = $routeParams->executeRoute($this);
//            Response::setResponseHttpCode(200);
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
        if ($this->responseCreator) {
            //TODO
        } else {
            Response::addHeaders(['Content-Type' => ['application/json']]);
            echo Response::sendResponse($body, $routeHandler);
        }
        return true;
    }

    /**
     * @return string
     */
    public function getLogFileName()
    {
        return $this->logFileName;
    }
}