<?php


namespace ZXC\Native;


use ZXC\Interfaces\Psr\Http\Message\UriInterface;
use ZXC\Native\PSR\UriFactory;

class FromGlobals
{
    public static function getUri(): UriInterface
    {
        $uri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
            . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return (new UriFactory)->createUri($uri);
    }

    public static function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function getServerParams(): array
    {
        return $_SERVER;
    }

    public static function getPost(): array
    {
        return $_POST;
    }

    public static function getGet(): array
    {
        return $_GET;
    }

    public static function getCookie(): array
    {
        return $_COOKIE;
    }

    public static function getFiles(): array
    {
        return $_FILES;
    }
}
