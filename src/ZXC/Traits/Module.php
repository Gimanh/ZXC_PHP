<?php


namespace ZXC\Traits;

use ZXC\Interfaces\IModule;

trait Module
{
    public static function create(array $options = [])
    {
        $newClass = get_class();
        /**
         * @var $instance IModule
         */
        $instance = new $newClass;
        $instance->init($options);
        return $instance;
    }
}
