<?php


namespace ZXC;

use Exception;
use ZXC\Native\Config;
use ZXC\Native\Modules;
use ZXC\Native\Router;
use ZXC\Patterns\Singleton;
use ZXC\Native\PSR\Response;
use InvalidArgumentException;
use ZXC\Native\HTTP\ZXCResponse;
use ZXC\Native\PSR\ServerRequest;
use ZXC\Interfaces\Psr\Http\Message\ServerRequestInterface;

class ZXC
{
    use Singleton;

    /**
     * @var ServerRequestInterface
     */
    private $request = null;

    /**
     * @var Response
     */
    private $response = null;

//    /**
//     * Not found handler MUST return modified given ResponseInterface
//     * @var null
//     */
//    private $notFoundHandler = null;

    private function prepareConfig(string $configPath)
    {
        $config = json_decode(file_get_contents($configPath), true);
        Config::init($config);

        $this->request = new ServerRequest($_SERVER, $_COOKIE, $_FILES);
        $this->response = new Response();

        $routerConfig = Config::get('router');
        Router::instance()->prepare($routerConfig);
        Modules::install(Config::get('modules'));
    }

    public function go(string $configPath)
    {
        try {
            self::prepareConfig($configPath);
            $routeHandlerResult = Router::instance()->go();
            ZXCResponse::sendResponse($routeHandlerResult);
        } catch (Exception $e) {
            ZXCResponse::sendError($this, 500, $e->getMessage());
        }
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public static function request(): ServerRequestInterface
    {
        return ZXC::instance()->getRequest();
    }

    public static function response(): Response
    {
        return ZXC::instance()->getResponse();
    }
}
