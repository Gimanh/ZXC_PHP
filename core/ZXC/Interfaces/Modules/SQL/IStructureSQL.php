<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15.11.2018
 * Time: 16:47
 */

namespace ZXC\Interfaces\Modules\SQL;


use ZXC\Interfaces\Native\IStructure;
use ZXC\Modules\SQL\StructureBaseSQL;

interface IStructureSQL extends IStructure
{
    /**
     * String for DB
     * @return mixed
     */
    public function getString();

    /**
     * @param $structure
     * @param $joinOptions array [
     *       'mode' => 'LEFT',      //LEFT JOIN
     *       'as' => 'articles',    //LEFT JOIN table as articles
     *       'joinOperatorWhere' => 'AND',       //join operator
     *       'useNativeWhere' => true,          //if true where values will add to query
     *       'fieldsCorrelation' => [
     *           'user_id' => [                 //native field name for joined structure
     *               'condition' => '=',
     *               'tableAlias' => $structureUsers->getJoinAlias(),
     *               'field' => 'id'            //field name for another structure(parent,...,child)
     *           ]
     *       ]
     * ]
     * @return mixed
     */
    public function join(StructureBaseSQL $structure, $joinOptions);
}