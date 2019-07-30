<?php

namespace ZXC\Native\HTTP;

use ZXC\Native\Helper;
use ZXC\Traits\Module;
use ZXC\Interfaces\IModule;
use InvalidArgumentException;

class ZCookie implements IModule
{
    use Module;
    private static $config = [];
    private static $name = false;
    private static $value = "";
    private static $time = 0;
    private static $domain = '';
    private static $path = '/';
    private static $secure = false;
    private static $httpOnly = true;

    public function initialize(array $config = null)
    {
        if (!Helper::issetKeys($config, ['domain'])) {
            throw new InvalidArgumentException('Config for Cookie must isset required fields "name" and "domain"');
        }
        self::$config = $config;
    }

    public static function set($name = null, $value = null, $timeInMinutes = null)
    {
        return @setcookie(
            $name ? $name : self::$name,
            $value ? $value : self::$value,
            $timeInMinutes ? (time() + ($timeInMinutes * 60)) : self::$time,
            self::$path,
            self::$domain,
            self::$secure,
            self::$httpOnly
        );
    }

    public static function get($name = null)
    {
        $fieldName = $name ? $name : self::$name;
        return isset($_COOKIE[$fieldName]) ? $_COOKIE[$fieldName] : null;
    }

    public static function delete($name = null)
    {
        $fieldName = $name ? $name : self::$name;
        return setcookie($fieldName, '', time() - 3600, self::$path, self::$domain, self::$secure, self::$httpOnly);
    }

    public static function setDomain($domain)
    {
        self::$domain = $domain;
    }

    public static function getDomain()
    {
        return self::$domain;
    }

    public static function setName($id)
    {
        self::$name = $id;
    }

    public static function getName()
    {
        return self::$name;
    }

    public static function setPath($path)
    {
        self::$path = $path;
    }

    public static function getPath()
    {
        return self::$path;
    }

    public static function setSecure($secure)
    {
        self::$secure = $secure;
    }

    public static function getSecure()
    {
        return self::$secure;
    }

    public static function setTime($timeMinutes)
    {
        self::$time = time() + ($timeMinutes * 60);
    }

    public static function getTime()
    {
        return self::$time;
    }

    public static function setValue($value)
    {
        self::$value = $value;
    }

    public static function getValue()
    {
        return self::$value;
    }
}
