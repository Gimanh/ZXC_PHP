<?php

namespace ZXC\Native\HTTP;

use ZXC\Native\Helper;

class CSRF
{
    public static function get()
    {
        return bin2hex(openssl_random_pseudo_bytes(32));
    }

    public static function check($token, $tokenFromHeader)
    {
        return Helper::hashEquals($token, $tokenFromHeader);
    }
}