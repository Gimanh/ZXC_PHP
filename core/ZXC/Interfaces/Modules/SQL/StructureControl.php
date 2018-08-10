<?php

namespace ZXC\Interfaces\Modules\SQL;

interface StructureControl
{
    public static function getStructureByName($structureName);

    public static function registerStructure(array $structure);
}