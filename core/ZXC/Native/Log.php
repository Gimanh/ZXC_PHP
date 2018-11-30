<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15.11.2018
 * Time: 14:35
 */

namespace ZXC\Native;

/**
 * @method static void emergency($message, array $context = array());
 * @method static void alert($message, array $context = array());
 * @method static void critical($message, array $context = array());
 * @method static void error($message, array $context = array());
 * @method static void warning($message, array $context = array());
 * @method static void notice($message, array $context = array());
 * @method static void info($message, array $context = array());
 * @method static void debug($message, array $context = array());
 * @method static void log($level, $message, array $context = array());
 * @package ZXC\Native
 */
class Log
{
    public static function __callStatic($method, $args)
    {
        $logger = ModulesManager::getModule('Logger');
        if (!$logger) {
            throw new \InvalidArgumentException('Logger module is not defined');
        }
        call_user_func_array([$logger, $method], $args);
    }
}