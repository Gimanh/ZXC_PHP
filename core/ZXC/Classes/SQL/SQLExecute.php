<?php

namespace ZXC\Classes\SQL;

abstract class SQLExecute extends SQL
{
    public function exec(DB $db, ResultStructure $resultStructure): ResultStructure
    {
        $result = $db->exec($this->generateSql(), $this->getValues());
        $resultStructure->fillFields($result);
        return $resultStructure;
    }
}