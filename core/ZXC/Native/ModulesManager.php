<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 12.11.2018
 * Time: 15:40
 */

namespace ZXC\Native;


use ZXC\Interfaces\Module;

class ModulesManager implements \ZXC\Interfaces\ModulesManager
{
    private static $modules = [];
    private static $modulesInstances = [];

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
     */
    public static function installModules(array $options = null)
    {
        if (!$options) {
            return false;
        }
        self::$modules = $options;
        foreach (self::$modules as $pluginName => $pluginValue) {
            if($pluginValue['class']){
                $instance = Helper::createInstanceOfClass($pluginValue['class']);
                if (!$instance) {
                    throw new \InvalidArgumentException('Module ' . $pluginName . ' can not create instance');
                }
                if (!$instance instanceof Module) {
                    throw new \InvalidArgumentException('Module ' . $pluginName . ' must implement \'ZXC\Interfaces\Module\'');
                }
                $instance->initialize($pluginValue['options']);
                if (!isset(self::$modulesInstances[$pluginName])) {
                    self::$modulesInstances[$pluginName] = $instance;
                }
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
                unset(self::$modules[$key]);
            }
        }
        return true;
    }

    /**
     * Returns instance of registered Module
     * @param string $moduleName -  'StructureControl'
     * @return Module|null
     */
    public static function getModule($moduleName)
    {
        if (isset(self::$modulesInstances[$moduleName])) {
            return self::$modulesInstances[$moduleName];
        }
        return null;
    }

    /**
     * Returns new instance of registered Module with given parameters $options
     * @param $moduleName - registered module name exp 'Logger'
     * @param array|null $options
     * @return mixed|null
     */
    public static function getNewModule($moduleName, array $options = null)
    {
        if (isset(self::$modulesInstances[$moduleName])) {
            $new = call_user_func_array([self::$modulesInstances[$moduleName], 'create'], [$options]);
            return $new;
        }
        return null;
    }
}