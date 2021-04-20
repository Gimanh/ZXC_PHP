<?php


namespace ZXC;


use Exception;
use ZXC\Native\Config;
use ZXC\Native\Router;
use ZXC\Native\Modules;
use ZXC\Native\PSR\Response;
use ZXC\Native\PSR\ResponseFactory;
use ZXC\Native\PSR\ServerRequestFactory;
use ZXC\Interfaces\Psr\Http\Message\ResponseInterface;


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

    public function go(string $configPath): void
    {
        try {
            $this->prepareConfig($configPath);
            $routeHandlerResult = $this->router->go();
            self::sendResponse($routeHandlerResult);
        } catch (Exception $e) {
            self::sendResponse(
                (new Response())->withStatus(500)
            );
        }
    }

    public static function sendResponse(ResponseInterface $response)
    {
        if (!headers_sent()) {
            foreach ($response->getHeaders() as $name => $values) {
                $first = stripos($name, 'Set-Cookie') === 0 ? false : true;
                foreach ($values as $value) {
                    header(sprintf('%s: %s', trim($name), trim($value)), $first);
                    $first = false;
                }
            }
            header(sprintf('HTTP/%s %s %s',
                $response->getProtocolVersion(),
                $response->getStatusCode(),
                $response->getReasonPhrase()
            ), true, $response->getStatusCode());

            $body = $response->getBody();
            if ($body->isSeekable()) {
                $body->rewind();
            }
            while (!$body->eof()) {
                echo $body->read(4096);
                if (connection_status() != CONNECTION_NORMAL) {
                    break;
                }
            }
        }
    }
}