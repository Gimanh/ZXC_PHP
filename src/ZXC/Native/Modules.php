<?php

namespace ZXC\Native;

use RuntimeException;
use ZXC\Interfaces\IModule;

class Modules
{
    /**
     * @var ModuleParams[]
     */
    private static $modulesOptions = [];

    private static $modulesInstances = [];

    private static $modulesName = [];

    public static function install(array $options = [])
    {
        foreach ($options as $name => $option) {
            self::$modulesName[strtolower($name)] = $name;
            self::$modulesOptions[$name] = new ModuleParams(
                $option['class'] ?? '',
                $option['options'] ?? [],
                $option ['defer'] ?? true
            );
        }
        foreach (self::$modulesOptions as $moduleName => $modulesOption) {
            if (!$modulesOption->isDefer()) {
                self::$modulesInstances[$moduleName] = self::createInstance($modulesOption);
            }
        }
    }

    public static function get(string $moduleName)
    {
        if (!self::has($moduleName)) {
            return null;
        }
        $name = self::$modulesName[strtolower($moduleName)];
        if (!isset(self::$modulesInstances[$name])) {
            self::$modulesInstances[$name] = self::createInstance(self::$modulesOptions[$name]);
            return self::$modulesInstances[$name];
        }
        return self::$modulesInstances[$name];
    }

    /**
     * Returns new instance of registered Module with given parameters $options
     * @param $moduleName - registered module name exp 'Logger'
     * @param array|null $options
     * @return mixed|null
     */
    public static function getNew(string $moduleName, array $options = [])
    {
        if (!self::has($moduleName)) {
            return null;
        }
        return call_user_func_array([
            self::$modulesInstances[self::$modulesName[strtolower($moduleName)]], 'create'
        ], [$options]);
    }

    public static function has($moduleName)
    {
        return isset(self::$modulesName[strtolower($moduleName)]);
    }

    public static function createInstance(ModuleParams $options)
    {
        $instance = Helper::createInstanceOfClass($options->getClass());
        if (!$instance instanceof IModule) {
            throw new RuntimeException('Module ' . $options['class'] . ' must implement \'ZXC\Interfaces\Module\'');
        }
        $instance->init($options->getOptions());
        return $instance;
    }

    /**
     * @param $className - full class name with namespace
     * @method getModuleByClassName
     * @return mixed
     */
    public static function getByClassName($className)
    {
        foreach (self::$modulesInstances as $instance) {
            $instanceClassName = get_class($instance);
            if ($className === $instanceClassName) {
                return $instance;
            }
        }

        foreach (self::$modulesOptions as $name => $option) {
            if ($option->getClass() === $className) {
                return self::get($name);
            }
        }

        return false;
    }

    public static function uninstall(array $options = []): bool
    {
        $wasDeleted = false;
        foreach ($options as $key => $value) {
            if ($value) {
                $name = self::$modulesName[strtolower($key)];
                $wasDeleted = true;
                unset(self::$modulesInstances[$name]);
                unset(self::$modulesOptions[$name]);
                unset(self::$modulesName[strtolower($key)]);
            }
        }
        return $wasDeleted;
    }
}
