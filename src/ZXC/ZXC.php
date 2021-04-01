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
        $this->router = new Router(
            new ServerRequestFactory(),
            new ResponseFactory(),
            Config::get('router')
        );
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
