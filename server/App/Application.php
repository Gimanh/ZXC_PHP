<?php


namespace App;


use ZXC\Native\PSR\Response;
use ZXC\Native\PSR\ServerRequest;

class Application
{
    public function hello(ServerRequest $request, Response $response)
    {
        $response->write('Hello');
        return $response;
    }
}