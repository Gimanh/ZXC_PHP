<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 18/10/2018
 * Time: 23:37
 */

namespace ZXC\Modules\SQL;


abstract class AbstractFactoryMethod
{
    /**
     * @param string|null $type
     * @param array|null $options
     * @return StructureBaseSQL
     */
    public static function create($type = null, array $options = null){
        switch ($type) {
            case 'pgsql':
                return new StructurePGSQL($options);
        }
        throw new \InvalidArgumentException('DB type does not found');
    }
}