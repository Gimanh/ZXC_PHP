<?php

namespace ZXC\Classes;

use ZXC\Patterns\Singleton;

class Config
{
    use Singleton;

    private static $config = [];

    public static function initialize(array $config = [])
    {
        if (!$config) {
            throw new \InvalidArgumentException('Given config must be assoc array');
        }
        self::$config = $config;
    }

    public static function get($pathToConfigParams = '', $registry = true)
    {
        if (!$pathToConfigParams || !is_string($pathToConfigParams)) {
            throw new \InvalidArgumentException('Given path must be string type "path/to/params"');
        }

        $lastSlash = substr($pathToConfigParams, -1);
        if ($lastSlash === '/') {
            $pathToConfigParams = rtrim($pathToConfigParams, '/');
        }

        if (!$registry) {
            $pathToConfigParams = strtolower($pathToConfigParams);
        }

        $path = explode('/', $pathToConfigParams);
        if ($registry) {
            $configParameters = self::$config;
        } else {
            $configParameters = self::keysToLower(self::$config);
        }
        foreach ($path as $item) {
            if (array_key_exists($item, $configParameters)) {
                $configParameters = $configParameters[$item];
            } else {
                return false;
            }
        }
        return $configParameters;
    }

    public static function add(array $moreConfig = [])
    {
        if (!$moreConfig) {
            throw new \InvalidArgumentException('Given config must be assoc array');
        }
        self::$config = array_merge_recursive(self::$config, $moreConfig);
        return true;
    }

    private static function keysToLower(array $input)
    {
        $result = [];
        foreach ($input as $key => $value) {
            $key = strtolower($key);

            if (is_array($value)) {
                $value = self::keysToLower($value);
            }

            $result[$key] = $value;
        }
        return $result;
    }
}