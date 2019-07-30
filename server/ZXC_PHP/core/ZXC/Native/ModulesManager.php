<?php

namespace ZXC\Native;

use ZXC\ZXC;
use Exception;
use ReflectionException;
use ZXC\Interfaces\IModule;
use InvalidArgumentException;

class ModulesManager implements \ZXC\Interfaces\ModulesManager
{
    private static $modulesOptions = [];
    private static $modulesInstances = [];
    private static $modulesName = [];

    /**
     * Install modules for using
     * @param array|null $options = [
     *      ...
     *      'Session' => [
     *          'class' => 'ZXC\Classes\Session',
     *          'options' => [
     *              'prefix' => 'zxc_',
     *              'time' => 6200,
     *              'path' => '/',
     *              'domain' => 'zxc.com'
     *          ]
     *      ],
     *      ...
     * ]
     * @return bool|mixed
     * @throws ReflectionException
     * @throws Exception
     */
    public static function installModules(array $options = null)
    {
        if (!$options) {
            return false;
        }
        self::$modulesOptions = $options;
        foreach (self::$modulesOptions as $pluginName => $pluginValue) {
            if (!isset($pluginValue['class']) || !isset($pluginValue['options'])) {
                ZXC::log('Invalid module parameters', ['ModuleName' => $pluginName, 'params' => $pluginValue],Helper::getLogFileName(get_called_class()) );
                throw new InvalidArgumentException('Invalid module parameters for module ' . $pluginName);
            }
            self::$modulesName[strtolower($pluginName)] = $pluginName;
        }
        foreach (self::$modulesOptions as $pluginName => $pluginValue) {
            $defer = isset($pluginValue['defer']) ? $pluginValue['defer'] : null;
            if (!$defer) {
                self::$modulesInstances[$pluginName] = self::createModuleInstance($pluginValue);
            }
        }
        return true;
    }

    /**
     * Uninstall modules
     * @param array|null $options = [ 'Session'=>true ]
     * @return bool|mixed
     */
    public static function uninstallModules(array $options = null)
    {
        if (!$options) {
            return false;
        }
        foreach ($options as $key => $value) {
            if ($value) {
                unset(self::$modulesInstances[$key]);
                unset(self::$modulesOptions[$key]);
                unset(self::$modulesName[$key]);
            }
        }
        return true;
    }

    /**
     * Returns instance of registered Module
     * @param string $moduleName -  'StructureControl'
     * @return IModule|null
     * @throws ReflectionException
     * @throws Exception
     */
    public static function getModule($moduleName)
    {
        if (!self::hasModule($moduleName)) {
            ZXC::log('Can not find module ' . $moduleName, ['name' => $moduleName]);
            return null;
        }
        $originName = self::$modulesName[strtolower($moduleName)];
        if (!isset(self::$modulesInstances[$originName])) {
            self::$modulesInstances[$originName] = self::createModuleInstance(self::$modulesOptions[$originName]);
            return self::$modulesInstances[$originName];
        }
        return self::$modulesInstances[$originName];
    }

    /**
     * Returns new instance of registered Module with given parameters $options
     * @param $moduleName - registered module name exp 'Logger'
     * @param array|null $options
     * @return mixed|null
     */
    public static function getNewModule($moduleName, array $options = null)
    {
        if (!self::hasModule($moduleName)) {
            return null;
        }
        $new = call_user_func_array([self::$modulesInstances[self::$modulesName[strtolower($moduleName)]], 'create'], [$options]);
        return $new;
    }

    public static function hasModule($moduleName)
    {
        return isset(self::$modulesName[strtolower($moduleName)]);
    }

    public static function destroy()
    {
        self::$modulesOptions = [];
        self::$modulesInstances = [];
        self::$modulesName = [];
    }

    /**
     * @param array $options
     * @method createModuleInstance
     * @return mixed|null
     * @throws ReflectionException
     */
    public static function createModuleInstance(array $options)
    {
        if ($options['class']) {
            $instance = Helper::createInstanceOfClass($options['class']);
            if (!$instance) {
                throw new InvalidArgumentException('Module ' . $options['class'] . ' can not create instance');
            }
            if (!$instance instanceof IModule) {
                throw new InvalidArgumentException('Module ' . $options['class'] . ' must implement \'ZXC\Interfaces\Module\'');
            }
            $instance->initialize($options['options']);
            return $instance;
        }
        return null;
    }

    /**
     * @return array
     */
    public static function getModulesOptions()
    {
        return self::$modulesOptions;
    }

    /**
     * @return array
     */
    public static function getModulesInstances()
    {
        return self::$modulesInstances;
    }

    /**
     * @param $className - full class name with namespace
     * @method getModuleByClassName
     * @return mixed
     * @throws ReflectionException
     */
    public static function getModuleByClassName($className)
    {
        foreach (self::$modulesInstances as $instance) {
            $instanceClassName = get_class($instance);
            if ($className === $instanceClassName) {
                return $instance;
            }
        }

        foreach (self::$modulesOptions as $name => $option) {
            if ($option['class'] === $className) {
                return self::getModule($name);
            }
        }

        return false;
    }
}