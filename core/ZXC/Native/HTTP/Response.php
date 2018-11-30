<?php

namespace ZXC\Native\HTTP;

use ZXC\Patterns\Singleton;

class Response
{
    use Singleton;

    private static $answerData = ['response' => ['body' => '', 'handler' => '', 'status' => 200]];
    private static $headers = [];
    private static $status = 200;

    public static function createResponse($responseData)
    {
        self::$answerData = $responseData;
    }

    public static function sendResponse($responseDataBody = false, $responseDataHandler = false): string
    {
        self::$answerData['response']['body'] = !$responseDataBody ? '' : $responseDataBody;
        self::$answerData['response']['handler'] = !$responseDataHandler ? '' : $responseDataHandler;
        self::$answerData['response']['status'] = self::$status;
        self::sendHeaders();
        return json_encode(self::$answerData);
    }

    public static function sendHeaders()
    {
        if (!headers_sent()) {
            foreach (self::$headers as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }

            return true;
        }

        return false;
    }

    public static function setResponseHttpCode($code = 200)
    {
        self::$status = $code;
        return http_response_code($code);
    }

    public static function addHeaders(array $headers = [])
    {
        if (!$headers) {
            return false;
        }
        foreach ($headers as $header => $value) {
            if (!array_key_exists($header, self::$headers)) {
                self::$headers[$header] = $value;
            } else {
                self::$headers[$header] = array_merge(self::$headers[$header], $value);
            }
        }
        return true;
    }

    public static function deleteAllHeaders()
    {
        self::$headers = [];
        return true;
    }

    public static function deleteHeader($headerName = null)
    {
        if (!$headerName) {
            return false;
        }
        if (array_key_exists($headerName, self::$headers)) {
            unset(self::$headers[$headerName]);
            return true;
        }

        return false;
    }

    public static function deleteHeaderValue($headerName = null, array $headerValue = [])
    {
        if (!$headerName || !$headerValue) {
            return false;
        }
        foreach ($headerValue as $value) {
            $key = array_search($value, self::$headers[$headerName]);
            if ($key !== false) {
                unset(self::$headers[$headerName][$key]);
            }
        }
        if (!self::$headers[$headerName]) {
            self::deleteHeader($headerName);
        }
        return true;
    }

    public static function existHeader($headerName = null)
    {
        if (!$headerName) {
            return false;
        }
        return isset(self::$headers[$headerName]);
    }

    public static function getHeaders()
    {
        return self::$headers;
    }
}