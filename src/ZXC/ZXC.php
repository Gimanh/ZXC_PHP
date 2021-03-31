<?php


namespace ZXC;


use Exception;
use ZXC\Native\Config;
use ZXC\Native\Router;
use ZXC\Native\Modules;
use ZXC\Native\HTTP\ZXCResponse;
use ZXC\Native\PSR\ResponseFactory;
use ZXC\Native\PSR\ServerRequestFactory;


class ZXC
{
    /**
     * @var Router
     */
    private $router = null;

    public function __construct(string $configPath)
    {
        $this->go($configPath);
    }

    private function prepareConfig(string $configPath)
    {
        $config = json_decode(file_get_contents($configPath), true);
        Config::init($config);
        //TODO move Factory to config file
        $serverRequestFactory = new ServerRequestFactory();
        $responseFactory = new ResponseFactory();
        $routerConfig = Config::get('router');
        $this->router = new Router($serverRequestFactory, $responseFactory, $routerConfig);
        Modules::install(Config::get('modules'));
    }

    public function go(string $configPath)
    {
        try {
            $this->prepareConfig($configPath);
            $routeHandlerResult = $this->router->go();
            ZXCResponse::sendResponse($routeHandlerResult);
        } catch (Exception $e) {
            ZXCResponse::sendError($this, 500, $e->getMessage());
        }
    }
}
