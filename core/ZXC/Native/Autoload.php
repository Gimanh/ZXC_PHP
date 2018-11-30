<?php

namespace ZXC\Native;

require_once ZXC_ROOT . DIRECTORY_SEPARATOR . 'ZXC' . DIRECTORY_SEPARATOR . 'Functions' . DIRECTORY_SEPARATOR
    . 'index.php';
require_once ZXC_ROOT . DIRECTORY_SEPARATOR . 'ZXC' . DIRECTORY_SEPARATOR . 'Patterns' . DIRECTORY_SEPARATOR
    . 'Singleton.php';
require_once ZXC_ROOT . DIRECTORY_SEPARATOR . 'ZXC' . DIRECTORY_SEPARATOR
    . 'Native' . DIRECTORY_SEPARATOR . 'Helper.php';
require_once ZXC_ROOT . DIRECTORY_SEPARATOR . 'ZXC' . DIRECTORY_SEPARATOR
    . 'Interfaces' . DIRECTORY_SEPARATOR . 'ZXC.php';



use ZXC\Patterns\Singleton;

class Autoload
{
    use Singleton;
    private static $autoloadDirectories = ['' => true];

    /**
     * Initialize autoload directories
     * @param array $config ['dirPath'=>true]
     * @return bool
     */
    public function initialize(array $config = [])
    {
        if ($config) {
            $this->setAutoloadDirectories($config);
            return true;
        }
        return false;
    }

    /**
     * Returns all registered directories
     * @return array
     */
    public static function getAutoloadDirectories()
    {
        return self::$autoloadDirectories;
    }

    /**
     * @param array $dir
     * @return bool
     */
    public function setAutoloadDirectories(array $dir)
    {
        if (!Helper::isAssoc($dir)) {
            return false;
        }
        self::$autoloadDirectories = array_merge(
            self::$autoloadDirectories, $dir
        );
        return true;
    }

    /**
     * Disable directory for autoload
     * @param string $dir
     * @return bool
     */
    public function disableAutoloadDirectories($dir)
    {
        if (isset(self::$autoloadDirectories[$dir])) {
            self::$autoloadDirectories[$dir] = false;

            return true;
        }

        return false;
    }

    /**
     * Enable directory for autoload
     * @param string $dir
     * @return bool
     */
    public function enableAutoloadDirectories($dir)
    {
        if (isset(self::$autoloadDirectories[$dir])) {
            self::$autoloadDirectories[$dir] = true;

            return true;
        }

        return false;
    }

    /**
     * Remove directories from autoload
     * @param string $dir
     * @return bool
     */
    public function removeAutoloadDirectories($dir)
    {
        if (isset(self::$autoloadDirectories[$dir])) {
            unset(self::$autoloadDirectories[$dir]);

            return true;
        }

        return false;
    }

    /**
     * Require file
     * @param string $className
     */
    public static function autoload($className)
    {
        $file = false;
        $fileClass = str_replace('\\', DIRECTORY_SEPARATOR, $className);
        if (strpos($className, 'ZXC') === 0) {
            $file = ZXC_ROOT . DIRECTORY_SEPARATOR . $fileClass . '.php';
        } else {
            if (!empty(self::$autoloadDirectories)) {
                foreach (self::$autoloadDirectories as $dir => $val) {
                    if ($val) {
                        if ($dir) {
                            $file = ZXC_ROOT . DIRECTORY_SEPARATOR . $dir
                                . DIRECTORY_SEPARATOR . $fileClass . '.php';
                        } else {
                            $file = ZXC_ROOT . DIRECTORY_SEPARATOR . $fileClass . '.php';
                        }

                        $file = realpath($file);
                        if ($file && is_file($file)) {
                            break;
                        }
                    }
                }
            }
        }
        if ($file && file_exists($file)) {
            require_once $file;
        }
    }
}

spl_autoload_register('ZXC\Native\Autoload::autoload');