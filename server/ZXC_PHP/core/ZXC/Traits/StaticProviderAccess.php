<?php

namespace ZXC\Traits;

use ZXC\Interfaces\ZXC;
use ZXC\Native\Helper;

trait StaticProviderAccess
{
    /**
     * @var ZXC
     */
    protected static $instance = null;

    protected static $config = null;

    public static function __callStatic($method, $args)
    {
        $instance = static::getProvider();
        if (!$instance) {
            throw new \InvalidArgumentException('');
        }
        return call_user_func_array([$instance, $method], $args);
    }

    /**
     * @return string|object
     */
    protected static function getProviderClass()
    {
        throw new \RuntimeException('Does not implement getProviderClassName method');
    }

    /**
     * @param $class string|object
     * @return void
     */
    protected static function getConfigForProvider($class)
    {
        throw new \RuntimeException('Does not implement getConfigForProvider method for class ' . $class);
    }

    protected static function getProvider()
    {
        if (!static::$instance) {
            $newClass = static::getProviderClass();
            if (is_object($newClass)) {
                return $newClass;
            }
            static::$instance = Helper::createInstanceOfClass($newClass);
            static::$instance->initialize(static::getConfigForProvider($newClass));
            if (!static::$instance) {
                throw new \InvalidArgumentException('ZXC/Modules/Auth does not set');
            }
        }
        return static::$instance;
    }
}