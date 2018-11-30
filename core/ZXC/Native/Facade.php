<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 27/11/2018
 * Time: 00:56
 */

namespace ZXC\Native;


class Facade
{
    protected static $instance = null;

    public static function __callStatic($method, $args)
    {

    }
}