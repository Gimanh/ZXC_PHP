<?php

namespace ZXC\Interfaces\Modules\SQL;

interface Structure
{
    public static function getStructureByName($structureName);

    public static function getStructureByTable($structureName);

    public static function registerStructure(array $structure);
}