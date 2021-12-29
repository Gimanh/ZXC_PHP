<?php

namespace ZXC\Traits;

trait Module
{
    public static function create(array $options = [])
    {
        $instance = new static();
        $instance->init($options);
        return $instance;
    }
}
