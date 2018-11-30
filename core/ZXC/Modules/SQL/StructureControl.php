<?php

namespace ZXC\Modules\SQL;

class StructureControl implements \ZXC\Interfaces\Modules\SQL\StructureControl
{
    private static $structures = [];

    public static function getStructureByName($structureName)
    {
        return self::$structures[$structureName];
    }

    public static function registerStructure(array $structure)
    {
        if (!isset($structure['name']) || !$structure['name']) {
            throw new \InvalidArgumentException('name field for structure is undefined');
        }
        self::$structures[$structure['name']] = $structure;
    }
}