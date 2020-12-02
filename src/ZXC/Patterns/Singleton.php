<?php


namespace ZXC\Patterns;

trait Singleton
{
    static private $instance = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }

    static public function instance()
    {
        return
            self::$instance === null
                ? self::$instance = new static()//new self()
                : self::$instance;
    }
}
