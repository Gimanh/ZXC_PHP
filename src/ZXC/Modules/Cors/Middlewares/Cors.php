<?php

namespace ZXC\Modules\Cors\Middlewares;

use ZXC\Native\Router;
use ZXC\Interfaces\Psr\Server\MiddlewareInterface;
use ZXC\Interfaces\Psr\Http\Message\ResponseInterface;
use ZXC\Interfaces\Psr\Server\RequestHandlerInterface;
use ZXC\Interfaces\Psr\Http\Message\ServerRequestInterface;

class Cors implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getMethod() === Router::METHOD_OPTIONS) {

        }
        $response = $handler->handle($request);
        $response->getBody()->write('World');
        return $response;
    }
}
