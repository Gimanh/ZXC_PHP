<?php

namespace ZXC\Interfaces;


interface ModulesManager
{
    /**
     * Install modules
     * @param array|null $options modules
     * @return mixed
     */
    public static function installModules(array $options = null);

    /**
     * Uninstall modules
     * @param array|null $options modules ['StructureControl' => true, 'Auth'=>true]
     * @return mixed
     */
    public static function uninstallModules(array $options = null);

    /**
     * Returns Module
     * @param string $moduleName 'StructureControl'
     * @return IModule|null
     */
    public static function getModule($moduleName);

    /**
     * Returns new instance of Module
     * @param string $moduleName 'StructureControl'
     * @param array|null $options
     * @return IModule|null
     */
    public static function getNewModule($moduleName, array $options = null);

    /**
     * @param string $moduleName case insensitive name
     * @method hasModule
     * @return mixed
     */
    public static function hasModule($moduleName);

    public static function destroy();
}