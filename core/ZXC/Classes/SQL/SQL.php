<?php

namespace ZXC\Classes\SQL;

use ZXC\Interfaces\SqlConditionFields;

abstract class SQL
{
    /**
     * @var $fields  SqlConditionFields
     */
    protected $fields;

    /**
     * @var SqlConditionFields
     */
    protected $from;

    /**
     * @var SqlConditionFields
     */
    protected $join;

    /**
     * @var SqlConditionFields
     */
    protected $where = [];

    protected $values = [];

    protected $limit = '';

    abstract public function generateSql(): string;

    abstract public function checkDataBeforeGenerateSqlString(): bool;

    public function addValue($value)
    {
        $this->values[] = $value;
    }

    public function fromToString(): string
    {
        $stop = false;
    }

    public function joinToString(): string
    {

    }

    public function whereToString(): string
    {

    }

    public function exec(DB $db)
    {
        return $db->exec($this->generateSql());
    }
}