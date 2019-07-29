<?php

namespace ZXC\Classes;

use DateTime;
use Exception;
use ZXC\Native\Helper;
use InvalidArgumentException;

/**
 * Class Token
 * @package ZXC\Classes
 * @link https://tools.ietf.org/html/rfc7519#section-4.1
 * @link https://ru.wikipedia.org/wiki/JSON_Web_Token
 */
class Token
{
    protected static $reportMessages = [];

    public static $supported = [
        'HS256' => ['func' => 'hash_hmac', 'alg' => 'SHA256'],
        'HS384' => ['func' => 'hash_hmac', 'alg' => 'SHA384'],
        'HS512' => ['func' => 'hash_hmac', 'alg' => 'SHA512'],
        'RS256' => ['func' => 'openssl', 'alg' => 'SHA256'],
        'RS384' => ['func' => 'openssl', 'alg' => 'SHA384'],
        'RS512' => ['func' => 'openssl', 'alg' => 'SHA512'],
    ];

    /**
     * @param mixed $payload
     * @param string $secretKey
     * @param string $alg
     * @return string
     * @throws Exception
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
     * @throws Exception
     */
    public static function decode($jwt, $secretKey)
    {
        $timestamp = time();
        $jwtSections = explode('.', $jwt);
        if (count($jwtSections) !== 3) {
            return false;
        }
        $header = self::jsonDecode(self::base64UrlDecode($jwtSections[0]));
        $payload = self::jsonDecode(self::base64UrlDecode($jwtSections[1]));
        $sign = self::base64UrlDecode($jwtSections[2]);
        if ($header === null || $payload === null || $sign === null) {
            throw new InvalidArgumentException('Invalid JWT');
        }
        if (empty($header['alg']) || empty(self::$supported[$header['alg']])) {
            throw new InvalidArgumentException('Invalid alg or alg not supported');
        }
        $body = $jwtSections[0] . '.' . $jwtSections[1];
        $verify = self::verify($body, $sign, $secretKey, $header['alg'], $payload, $timestamp);
        if (!$verify) {
            return false;
        }
        return $payload;
    }

    /**
     * @param $msg
     * @param $secretKey
     * @param string $alg
     * @return string
     * @throws Exception
     */
    public static function sign($msg, $secretKey, $alg = 'HS256')
    {
        if (!isset(self::$supported[$alg])) {
            throw new InvalidArgumentException('Algorithm' . $alg . ' not supported');
        }

        $hashInfo = self::$supported[$alg];
        if ($hashInfo['func'] === 'hash_hmac') {
            return hash_hmac($hashInfo['alg'], $msg, $secretKey, true);
        }

        if ($hashInfo['func'] === 'openssl') {
            $signature = '';
            $success = openssl_sign($msg, $signature, $secretKey, $hashInfo['alg']);
            if (!$success) {
                return '';
            } else {
                return $signature;
            }
        }
        return '';
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
     * @param $secretOrPublicKey
     * @param $alg
     * @param $payload
     * @param $timestamp
     * @return bool
     * @throws Exception \InvalidArgumentException
     */
    public static function verify($jwtMsg, $sign, $secretOrPublicKey, $alg, $payload, $timestamp)
    {
        self::$reportMessages = [];
        $hashInfo = self::$supported[$alg];
        if ($hashInfo['func'] === 'openssl') {
            $success = openssl_verify($jwtMsg, $sign, $secretOrPublicKey, $hashInfo['alg']);
            if ($success === 1) {
                return true;
            } elseif ($success === 0) {
                self::addReportMessage('OpenSSL verify error');
                return false;
            } else {
                self::addReportMessage('OpenSSL error ' . openssl_error_string());
                return false;
            }
        } else {
            $computedSign = self::sign($jwtMsg, $secretOrPublicKey, $alg);
            $signCorrect = Helper::hashEquals($sign, $computedSign);
        }
        if (!$signCorrect) {
            self::addReportMessage('Failed hash_equals');
            return false;
        }

        if (isset($payload['nbf']) && $payload['nbf'] > $timestamp) {
            self::addReportMessage('Invalid JWT you can use token after ' . date(DateTime::ISO8601, $payload['nbf']));
            return false;
        }

        if (isset($payload['iat']) && $payload['iat'] > $timestamp) {
            self::addReportMessage('Invalid JWT "iat" issued at');
            return false;
        }

        if (isset($payload['exp']) && $timestamp >= $payload['exp']) {
            self::addReportMessage('Invalid JWT, token is expired');
            return false;
        }
        return true;
    }

    public static function addReportMessage($message)
    {
        self::$reportMessages[] = $message;
    }

    public static function getReportMessage()
    {
        return implode(' | ', self::$reportMessages);
    }

    public static function fetchPayload($jwt)
    {
        $jwtSections = explode('.', $jwt);
        if (count($jwtSections) !== 3) {
            return false;
        }
        $payload = self::jsonDecode(self::base64UrlDecode($jwtSections[1]));
        return $payload;
    }
}