<?php

namespace ZXC\Classes\SQL;

abstract class SQL
{
    /**
     * @var $fields  Fields
     */
    protected $fields;

    protected $from;

    protected $join;//TODO keep in array then in generate we must create string from array

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