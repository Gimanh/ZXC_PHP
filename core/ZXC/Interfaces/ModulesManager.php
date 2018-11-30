<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 12.11.2018
 * Time: 15:11
 */

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
     * @return Module|null
     */
    public static function getModule($moduleName);

    /**
     * Returns new instance of Module
     * @param string $moduleName 'StructureControl'
     * @param array|null $options
     * @return Module|null
     */
    public static function getNewModule($moduleName, array $options = null);
}