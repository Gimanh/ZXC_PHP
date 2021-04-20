<?php


namespace ZXC\Native;


use ReflectionClass;
use DirectoryIterator;
use ReflectionException;
use InvalidArgumentException;


class Helper
{

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
     * @return bool
     * @link https://stackoverflow.com/questions/8141125/regex-for-password-php
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
            throw new InvalidArgumentException('Password is not defined');
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

//    public static function keysToLower(array $input)
//    {
//        $result = [];
//        foreach ($input as $key => $value) {
//            $key = strtolower($key);
//
//            if (is_array($value)) {
//                $value = self::keysToLower($value);
//            }
//
//            $result[$key] = $value;
//        }
//        return $result;
//    }

//    /**
//     * If array has not key and key with "null" value is empty
//     * @param array $var - where we search keys
//     * @param array $keys - keys for search
//     * @return bool return false if var has not key
//     */
//    public static function issetKeys(array $var, array $keys)
//    {
//        foreach ($keys as $k) {
//            if (!array_key_exists($k, $var)) {
//                return false;
//            }
//        }
//        return true;
//    }

    public static function createInstanceOfClass(string $className)
    {
        $args = func_get_args();
        if (count($args) > 1) {
            try {
                $r = new ReflectionClass($className);
                unset($args[0]);
                return $r->newInstanceArgs($args);
            } catch (ReflectionException $e) {
                return null;
            }
        } else {
            return new $className;
        }
    }

//    public static function classUsesTrait(string $className, string $traitName)
//    {
//        $traits = class_uses($className, true);
//        if ($traits) {
//            return in_array($traitName, $traits, true);
//        }
//        return false;
//    }

    /**
     * @link https://stackoverflow.com/a/2040279
     * @return string
     */
    public static function uuid(): string
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
            throw new InvalidArgumentException('Undefined $classString');
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
     * @throws ReflectionException
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
                    throw new InvalidArgumentException('Argument ' . $callback . ' not found');
                }
            } else {
                $instance = Helper::createInstanceOfClass($classMethod['class']);
                return call_user_func_array([$instance, $classMethod['method']], $args);
            }
        } elseif (is_callable($callback)) {
            return call_user_func_array($callback, $args);
        } else {
            throw new InvalidArgumentException('Argument $callback error see PHPDOC');
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

//    /**
//     * Returns true if writable flag found in $mode
//     * @param string $mode - stream_get_meta_data($stream)['mode']
//     * @return bool
//     */
//    public static function isWritable($mode)
//    {
//        $writable = ['w', 'w+', 'rw', 'r+', 'x+',
//            'c+', 'wb', 'w+b', 'r+b',
//            'x+b', 'c+b', 'w+t', 'r+t',
//            'x+t', 'c+t', 'a', 'a+'
//        ];
//        return in_array($mode, $writable);
//    }

//    /**
//     * Returns true if readable flag found in $mode
//     * @param string $mode - stream_get_meta_data($stream)['mode']
//     * @return bool
//     */
//    public static function isReadable($mode)
//    {
//        $readable = [
//            'r', 'w+', 'r+', 'x+', 'c+',
//            'rb', 'w+b', 'r+b', 'x+b',
//            'c+b', 'rt', 'w+t', 'r+t',
//            'x+t', 'c+t', 'a+'];
//        return in_array($mode, $readable);
//    }

//    public static function getPsrServerHeaders()
//    {
//        $psrHeaders = [];
//        $allHeaders = getallheaders();
//        foreach ($allHeaders as $name => $value) {
//            if (!is_array($value)) {
//                $value = [$value];
//            }
//            $psrHeaders[$name] = $value;
//        }
//        return $psrHeaders;
//    }

    /**
     * @method getIp
     * @link https://stackoverflow.com/a/13646735
     * @return mixed
     */
    public static function getIp()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }


    /**
     * @param $argv
     * @method getArgs
     * @return array
     */
    public static function getArgs($argv)
    {
        $my_args = array();
        for ($i = 1; $i < count($argv); $i++) {
            if (preg_match('/^--([^=]+)=(.*)/', $argv[$i], $match)) {
                $my_args[$match[1]] = $match[2];
            }
        }
        return $my_args;
    }

    public static function fetchFilesTreeWithExtension(DirectoryIterator $dir, $ext)
    {
        $data = [];
        if ($ext) {
            foreach ($dir as $node) {
                $nodeName = $node->getFilename();
                if ($node->isDir() && !$node->isDot()) {
                    $data[$nodeName] = self::fetchFilesTreeWithExtension(new DirectoryIterator($node->getPathname()), $ext);
                } else if ($node->isFile() && $node->getExtension() === $ext) {
                    $data[] = $node->getFilename();
                }
            }
        }
        return $data;
    }

    public static function minifyPHPCode($filePath)
    {
        return php_strip_whitespace($filePath);
    }


    public static function getFilesList($pattern = 'README.md', $flags = 0, $ignoreList = [])
    {
        $files = glob($pattern, $flags);
        $dirName = dirname($pattern);

        $ignoreDir = function ($currentDir) use ($ignoreList) {
            foreach ($ignoreList as $item) {
                if (strpos($currentDir, $item)) {
                    return true;
                }
            }
            return false;
        };

        foreach (glob($dirName . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            if (!$ignoreDir($dir)) {
                $files = array_merge($files, self::getFilesList($dir . '/' . basename($pattern), $flags, $ignoreList));
            }
        }
        return $files;
    }

    public static function rRmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object))
                        self::rRmdir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            rmdir($dir);
        }
    }

    public static function rCopy($src, $dest)
    {
        // If source is not a directory stop processing
        if (!is_dir($src)) return false;
        // If the destination directory does not exist create it
        if (!is_dir($dest)) {
            if (!mkdir($dest)) {
                // If the destination directory could not be created stop processing
                return false;
            }
        }
        // Open the source directory to read in files
        $i = new DirectoryIterator($src);
        foreach ($i as $f) {
            if ($f->isFile()) {
                copy($f->getRealPath(), "$dest/" . $f->getFilename());
            } else if (!$f->isDot() && $f->isDir()) {
                self::rCopy($f->getRealPath(), "$dest/$f");
            }
        }
        return true;
    }
}
