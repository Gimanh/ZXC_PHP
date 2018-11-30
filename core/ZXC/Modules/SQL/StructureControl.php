<?php

namespace ZXC\Modules\SQL;

use ZXC\Native\Config;
use ZXC\Interfaces\Module;

class StructureControl implements Module
{
    use \ZXC\Traits\Module;

//    private static $moduleName = 'StructureControl';
    private static $dir = null;
    private static $structures = [];

    /**
     * Try get structure by name if structure will not found
     * function will try load structure from autoload directory for structure
     * which defined in config file "ZXC/Structures/dir"
     * @param $structureName
     * @return array|null
     */
    public static function getStructureByName($structureName)
    {
        $structure = null;
        if (array_key_exists($structureName, self::$structures)) {
            $structure = self::$structures[$structureName];
        }

        if (!$structure) {
            if (!self::$dir) {
                return null;
            } else {
                $structFileName = 'Struct' . ucfirst($structureName) . '.php';
                $structFile = ZXC_ROOT . DIRECTORY_SEPARATOR . self::$dir . DIRECTORY_SEPARATOR . $structFileName;
                if (!file_exists($structFile)) {
                    return null;
                } else {
                    $loadedStructure = require_once $structFile;
                    if (!$loadedStructure) {
                        return null;
                    }
                    self::registerStructure($loadedStructure);
                    return self::$structures[$structureName];
                }
            }
        }
        return $structure;
    }

    /**
     * Register Structure
     * @param array $structure
     */
    public static function registerStructure(array $structure)
    {
        if (!isset($structure['name']) || !$structure['name']) {
            throw new \InvalidArgumentException('name field for structure is undefined');
        }
        self::$structures[$structure['name']] = $structure;
    }

    /**
     * Initialize class with config
     * @param array $config
     * @return bool
     */
    public function initialize(array $config = null)
    {
        if ($config['dir']) {
            self::$dir = $config['dir'];
        }
        $this->version = '0.0.1';
        return true;
    }

//    public function getModuleName()
//    {
//        return self::$moduleName;
//    }
//
//    public function setModuleName($name)
//    {
//        self::$moduleName = $name;
//    }
}