<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.11.2018
 * Time: 11:23
 */

namespace ZXC\Native\HTTP;

use ZXC\Native\Helper;

class CSRF
{
    public static $supported = [
        'HS256' => 'SHA256',
        'HS384' => 'SHA384',
        'HS512' => 'SHA512'
    ];

    public static function get($msg, $secretKey, $alg = 'HS256')
    {
        if (!self::$supported[$alg]) {
            throw new \InvalidArgumentException('Algorithm' . $alg . ' not supported');
        }
        $alg = self::$supported[$alg];
        return hash_hmac($alg, $msg, $secretKey, true);
    }

    public static function check($csrf, $msg, $secretKey, $alg = 'HS256')
    {
        $tokenComputed = static::get($msg, $secretKey, $alg);
        return Helper::hashEquals($csrf, $tokenComputed);
    }
}