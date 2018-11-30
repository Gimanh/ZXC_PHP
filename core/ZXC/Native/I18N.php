<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 22/10/2018
 * Time: 22:38
 */

namespace ZXC\Native;


class I18N
{
    public static $phrases = [];

    public static function t($key)
    {
        if (isset(self::$phrases[$key])) {
            return self::$phrases[$key];
        }
        return $key;
    }

    public static function initialize(array $configPhrases = null)
    {
        if (!$configPhrases) {
            return false;
        }
        self::$phrases = $configPhrases;
        return true;
    }
}