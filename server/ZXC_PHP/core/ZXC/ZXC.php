<?php

namespace ZXC;

require_once 'Native/Autoload.php';

use Exception;
use ErrorException;
use Psr\Log\LogLevel;
use ZXC\Native\Config;
use ZXC\Native\Helper;
use ZXC\Native\PSR\Request;
use ZXC\Native\Router;
use ZXC\Native\Autoload;
use ReflectionException;
use ZXC\Patterns\Singleton;
use ZXC\Native\PSR\Response;
use InvalidArgumentException;
use ZXC\Modules\Logger\Logger;
use ZXC\Native\ModulesManager;
use ZXC\Native\HTTP\ZXCResponse;
use ZXC\Native\PSR\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ZXC implements Interfaces\ZXC
{
    use Singleton;


    private $version = '0.0.1-a';
    /**
     * @var ServerRequestInterface
     */
    private $request = null;
    /**
     * @var ResponseInterface
     */
    private $response = null;
    /**
     * @var Logger
     */
    private $logger = null;
    /**
     * @var Router
     */
    private $router = null;
    /**
     * Not found handler MUST return modified given ResponseInterface
     * @var null
     */
    private $notFoundHandler = null;
    /**
     * @var string|callable
     */
    private $responseCreator = null;

    private $logFileName = 'ZXC_SYS.log';
    /**
     * Value of header Accept-Language
     * @var string
     */
    public static $lang = 'en';

    private $loggerEnable = true;

    private static $ip;

    /**
     * @return mixed
     */
    public static function getIp()
    {
        return self::$ip;
    }

    /**
     * @param array $config
     * @throws ReflectionException
     */
    public function initialize(array $config = null)
    {
        $this->request = new ServerRequest($_SERVER, $_COOKIE, $_FILES);
        $this->response = new Response();

        static::$ip = Helper::getIp();
        if ($config) {
            Config::initialize($config);

            $this->checkConfig();

            Autoload::initialize(Config::get('ZXC/Autoload'));

            $modules = Config::get('ZXC/Modules');

            if ($modules) {
                ModulesManager::installModules(Config::get('ZXC/Modules'));
                $this->logger = ModulesManager::getModule('logger');
            }

            $this->router = Router::getInstance();
            $this->router->initialize(Config::get('ZXC/Router'));

            $this->detectLanguage();
        }
    }

    private function checkConfig()
    {
        if (!Config::get('ZXC/Router')) {
            throw new InvalidArgumentException('ZXC/Router is required');
        }
    }

    public function detectLanguage()
    {
        if ($this->request->hasHeader('Accept-Language')) {
            $value = $this->request->getHeaderLine('Accept-Language');
            self::$lang = substr($value, 0, 2);
        }
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
     * @param string $msg
     * @param array $param
     * @param string $fileName
     * @param string $logLvl
     * @return bool
     * @throws Exception
     * @method log
     */
    public static function log($msg = '', $param = [], $fileName = '', $logLvl = LogLevel::INFO)
    {
        $ZXC = ZXC::getInstance();
        if (!$ZXC->loggerEnable) {
            return false;
        }
        $logger = $ZXC->logger;
        if ($logger) {
            if ($fileName) {
                $logger = $logger->withLogFileName($fileName);
            }
            $logger->log($logLvl, $msg, $param);
        }
        return true;
    }

    /**
     * @param $lvl
     * @param $message
     * @param $file
     * @param $line
     * @return bool
     * @throws ErrorException
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
        throw new ErrorException($message, 0, $lvl, $file, $line);
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return ServerRequestInterface|Request
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
        try {
            $routeHandlerResult = $this->router->go();
            ZXCResponse::sendResponse($routeHandlerResult);
        } catch (InvalidArgumentException $e) {
            ZXCResponse::sendError($this, 500, $e->getMessage());
        } catch (Exception $e) {
            ZXCResponse::sendError($this, 500, $e->getMessage());
        }
    }


    /**
     * @return string
     */
    public function getLogFileName()
    {
        return $this->logFileName;
    }

    /**
     * @return ResponseInterface | Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param callable | string $callback - Class:method
     * @method setNotFoundHandler
     */
    public function setNotFoundHandler($callback)
    {
        $this->notFoundHandler = $callback;
    }

    /**
     * @return null
     */
    public function getNotFoundHandler()
    {
        return $this->notFoundHandler;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }
}