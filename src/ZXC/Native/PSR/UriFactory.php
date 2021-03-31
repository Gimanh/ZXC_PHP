<?php


namespace ZXC\Native\PSR;


use ZXC\Interfaces\Psr\Http\Message\UriFactoryInterface;
use ZXC\Interfaces\Psr\Http\Message\UriInterface;

class UriFactory implements UriFactoryInterface
{

    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
