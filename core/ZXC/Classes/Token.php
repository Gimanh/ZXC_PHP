<?php

namespace ZXC\Classes;

use ZXC\Native\Helper;

class Token
{
    public static $supported = [
        'HS256' => ['hash_hmac', 'SHA256'],
        'HS384' => ['hash_hmac', 'SHA384'],
        'HS512' => ['hash_hmac', 'SHA512']
    ];

    /**
     * @param array $payload
     * @param string $secretKey
     * @param string $alg
     * @return string
     * @throws \Exception
     */
    public static function encode($payload, $secretKey, $alg = 'HS256')
    {
        $jwt = [];
        $header = [
            'alg' => $alg,
            'typ' => 'JWT'
        ];
        $jwt[] = self::base64UrlEncode(self::jsonEncode($header));
        $jwt[] = self::base64UrlEncode(self::jsonEncode($payload));
        $signMessage = implode('.', $jwt);
        $sign = self::sign($signMessage, $secretKey, $alg);
        $sign = self::base64UrlEncode($sign);
        $jwt[] = $sign;
        $jwt = implode('.', $jwt);
        return $jwt;
    }

    /**
     * @param $jwt
     * @param $secretKey
     * @return mixed
     * @throws \Exception
     */
    public static function decode($jwt, $secretKey)
    {
        $timestamp = time();
        $jwtSections = explode('.', $jwt);
        $header = self::jsonDecode(self::base64UrlDecode($jwtSections[0]));
        $payload = self::jsonDecode(self::base64UrlDecode($jwtSections[1]));
        $sign = self::base64UrlDecode($jwtSections[2]);
        if ($header === null || $payload === null || $sign === null) {
            throw new \InvalidArgumentException('Invalid JWT');
        }
        if (empty($header['alg']) || empty(self::$supported[$header['alg']])) {
            throw new \InvalidArgumentException('Invalid alg or alg not supported');
        }
        $body = $jwtSections[0] . '.' . $jwtSections[1];
        $verify = self::verify($body, $sign, $secretKey, $header['alg'], $payload, $timestamp);
        if (!$verify['status']) {
            throw new \InvalidArgumentException($verify['error']);
        }
        return $payload;
    }

    /**
     * @param $msg
     * @param $secretKey
     * @param string $alg
     * @return string
     * @throws \Exception
     */
    public static function sign($msg, $secretKey, $alg = 'HS256')
    {
        if (!self::$supported[$alg]) {
            throw new \InvalidArgumentException('Algorithm' . $alg . ' not supported');
        }
        $alg = self::$supported[$alg][1];
        return hash_hmac($alg, $msg, $secretKey, true);
    }

    public static function base64UrlEncode($string)
    {
        $base64 = base64_encode($string);
        $base64 = str_replace(['+', '/', '='], ['-', '_', ''], $base64);
        return $base64;
    }

    public static function base64UrlDecode($string)
    {
        $string = str_replace(['-', '_'], ['+', '/'], $string);
        $string = base64_decode($string);
        return $string;
    }

    public static function jsonEncode($value)
    {
        return json_encode($value);
    }

    public static function jsonDecode($value)
    {
        return json_decode($value, true);
    }

    /**
     * @param $jwtMsg
     * @param $sign
     * @param $secretKey
     * @param $alg
     * @param $payload
     * @param $timestamp
     * @return array ['status' => $signCorrect, 'error' => $errorInfo]
     * @throws \Exception \InvalidArgumentException
     */
    public static function verify($jwtMsg, $sign, $secretKey, $alg, $payload, $timestamp)
    {
        $errorInfo = '';
        $computedSign = self::sign($jwtMsg, $secretKey, $alg);
        $signCorrect = Helper::hashEquals($sign, $computedSign);
        if (!$signCorrect) {
            return ['status' => $signCorrect, 'error' => 'Failed hash_equals'];
        }
        //Identifies the time on which the JWT will start to be accepted for processing.
        if (isset($payload['nbf']) && $payload['nbf'] > $timestamp) {
            $errorInfo = 'Invalid JWT nbf';
        }
        //Identifies the time at which the JWT was issued.
        if (isset($payload['iat']) && $payload['iat'] > $timestamp) {
            $errorInfo = 'Invalid JWT iat';
        }
        //Identifies the expiration time on or after which the JWT must not be accepted for processing. The value should be in NumericDate[10][11] format.
        if (isset($payload['exp']) && $timestamp >= $payload['exp']) {
            $errorInfo = 'Invalid JWT iat';
        }
        return ['status' => $signCorrect, 'error' => $errorInfo];
    }
}