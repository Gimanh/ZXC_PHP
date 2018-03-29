<?php

namespace ZXC\Classes\SQL;

abstract class SQLExecute extends SQL
{
    public function exec(DB $db, ResultStructure $resultStructure): ResultStructure
    {
        $result = $db->exec($this->generateSql());
        $resultStructure->fillFields($result);
        return $resultStructure;
    }
}