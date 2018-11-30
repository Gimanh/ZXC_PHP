<?php

namespace ZXC\Native;

use ZXC\Patterns\Singleton;

class Helper
{
    use Singleton;

    public static $alphabet = [
        'a',
        'A',
        'b',
        'B',
        'c',
        'C',
        'd',
        'D',
        'e',
        'E',
        'f',
        'F',
        'g',
        'G',
        'h',
        'H',
        'i',
        'I',
        'j',
        'J',
        'k',
        'K',
        'l',
        'L',
        'm',
        'M',
        'n',
        'N',
        'o',
        'O',
        'p',
        'P',
        'q',
        'Q',
        'r',
        'R',
        's',
        'S',
        't',
        'T',
        'u',
        'U',
        'v',
        'V',
        'w',
        'W',
        'z',
        'Z',
        'Y',
        'y',
        'x',
        'X',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '0'
    ];

    public static function isAssoc($arr)
    {
        if (array() === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public static function isWindows()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check login can isset only [A-Za-z][A-Za-z0-9] and length can not between 4-20
     * @param string $login
     * @return bool
     */
    public static function isValidLogin($login = '')
    {
        if (!$login) {
            return false;
        }
        return (boolean)preg_match('/^[A-Za-z][A-Za-z0-9]{3,20}$/', $login);
    }

    /**
     * @param $password
     * Must be a minimum of 8 characters
     * Must contain at least 1 number
     * Must contain at least one uppercase character
     * Must contain at least one lowercase character
     * @link https://stackoverflow.com/questions/8141125/regex-for-password-php
     * @return bool
     */
    public static function isValidStrongPassword($password)
    {
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number = preg_match('@[0-9]@', $password);

        if (!$uppercase || !$lowercase || !$number || strlen($password) < 8) {
            return false;
        }
        return true;
    }

    public static function isEmail($email = null, $mx = true)
    {
        if (!$email) {
            return false;
        }
        if (!$mx) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return true;
            }
        }
        return filter_var($email, FILTER_VALIDATE_EMAIL) && (getmxrr(substr($email, strrpos($email, '@') + 1),
                $hosts));

    }

    public static function getCleanEmail($email)
    {
        $result = strtolower(filter_var($email, FILTER_SANITIZE_EMAIL));
        return $result;
    }

    public static function getPasswordHash($password = null, $cost = 10)
    {
        if ($password === null) {
            throw new \InvalidArgumentException('Password is not defined');
        }
        $options = [
            'cost' => $cost,
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public static function passwordVerify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public static function isIP($ip)
    {
        return (boolean)filter_var($ip, FILTER_VALIDATE_IP);
    }

    public static function equal($val1, $val2)
    {
        return $val1 === $val2;
    }

    public static function createHash()
    {
        return md5(uniqid() . time() . rand(0, 150));
    }

    public static function getResponse($code = 500, array $data = [])
    {
        return ['status' => $code, 'data' => $data];
    }

    public static function generateRandomText($minLength, $maxLength, $registry = true, $ignoreSymbols = [])
    {
        $charsCount = count(self::$alphabet) - 1;
        $length = rand($minLength, $maxLength);
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $symbol = self::$alphabet[rand(0, $charsCount)];
            if (in_array($symbol, $ignoreSymbols, true)) {
                while (in_array($symbol, $ignoreSymbols, true)) {
                    $symbol = self::$alphabet[rand(0, $charsCount)];
                }
            }
            if ($registry) {
                $str .= $symbol;
            } else {
                $str .= strtolower($symbol);
            }
        }
        return $str;
    }

    public static function keysToLower(array $input)
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

    /**
     * If array has not key and key with "null" value is empty
     * @param array $var - where we search keys
     * @param array $keys - keys for search
     * @return bool return false if var has not key
     */
    public static function issetKeys(array $var, array $keys)
    {
        foreach ($keys as $k) {
            if (!array_key_exists($k, $var)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param array $var
     * @param array $keys
     * @return array|bool ['field'=>['value'=>'value']]
     */
    public static function getConvertedArrayForStructureByKeys(array $var, array $keys)
    {
        $result = [];
        foreach ($keys as $k) {
            if (!array_key_exists($k, $var)) {
                return false;
            } else {
                $result[$k] = ['value' => $var[$k]];
            }
        }
        return $result;
    }

    public static function createInstanceOfClass($className)
    {
        //TODO TEST
        if (!$className) {
            throw new \InvalidArgumentException('Class name is required');
        }
        if (self::classUsesTrait($className, 'ZXC\Patterns\Singleton')) {
            return call_user_func($className . '::getInstance');
        }
        //TODO ERROR HANDLE
        return new $className;
    }

    public static function classUsesTrait($className, $traitName)
    {
        // TODO TEST
        $traits = class_uses($className, true);
        if ($traits) {
            return in_array($traitName, $traits, true);
        }
        return false;
    }

    /**
     * @link https://stackoverflow.com/a/2040279
     * @return string
     */
    public static function uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * For PHP 5.4 hash_equals
     * @param $str1
     * @param $str2
     * @return bool
     */
    public static function hashEquals($str1, $str2)
    {
        if (!function_exists('hash_equals')) {
            if (strlen($str1) != strlen($str2)) {
                return false;
            } else {
                $res = $str1 ^ $str2;
                $ret = 0;
                for ($i = strlen($res) - 1; $i >= 0; $i--) {
                    $ret |= ord($res[$i]);
                }
                return !$ret;
            }
        } else {
            return hash_equals($str1, $str2);
        }
    }

    public static function fixDirectorySlashes($path)
    {
        $path = (DIRECTORY_SEPARATOR === '\\')
            ? str_replace('/', '\\', $path)
            : str_replace('\\', '/', $path);
        $path = preg_replace('#' . DIRECTORY_SEPARATOR . '+#', DIRECTORY_SEPARATOR, $path);
        return $path;
    }

    public static function parseCallbackString($classString)
    {
        if (!$classString) {
            throw new \InvalidArgumentException('Undefined $classString');
        }
        $classAndMethod = explode(':', $classString);
        if (!$classAndMethod || count($classAndMethod) !== 2) {
            return [
                'class' => null,
                'method' => null
            ];
        }
        return [
            'class' => $classAndMethod[0],
            'method' => $classAndMethod[1]
        ];
    }

    /**
     * @param string|callable $callback Class:methodName or function
     * @return mixed
     */
    public static function callCallback($callback)
    {
        $args = func_get_args();
        unset($args[0]);
        if (is_string($callback)) {
            $classMethod = self::parseCallbackString($callback);
            if (!$classMethod['class'] || !$classMethod['method']) {
                if (function_exists($callback)) {
                    return call_user_func_array($callback, $args);
                } else {
                    throw new \InvalidArgumentException('Argument ' . $callback . ' not found');
                }
            } else {
                $instance = Helper::createInstanceOfClass($classMethod['class']);
                return call_user_func_array([$instance, $classMethod['method']], $args);
            }
        } elseif (is_callable($callback)) {
            return call_user_func_array($callback, $args);
        } else {
            throw new \InvalidArgumentException('Argument $callback error see PHPDOC');
        }
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
}