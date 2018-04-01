<?php

namespace ZXC\Classes\SQL;

use ZXC\Interfaces\SqlConditionFields;

class Select extends SQLExecute
{
    private $sql = 'SELECT ';
    /**
     * @var $fields SqlConditionFields
     */
    private $fields;
    /**
     * @var $from SqlConditionFields
     */
    private $from;
    /**
     * @var $join SqlConditionFields
     */
    private $join;
    /**
     * @var $where SqlConditionFields
     */
    private $where;
    /**
     * @var array
     */
    private $valuesForSelect = [];

    public function select(SqlConditionFields $fields): Select
    {
        $this->fields = $fields;
        return $this;
    }

    public function from(SqlConditionFields $from): Select
    {
        $this->from = $from;
        return $this;
    }

    public function join(SqlConditionFields $joins)
    {
        $this->join = $joins;
        return $this;
    }

    public function where(SqlConditionFields $where): Select
    {
        $this->where = $where;
        return $this;
    }

    public function checkDataBeforeGenerateSqlString(): bool
    {
        if (!$this->fields || !$this->from) {
            return false;
        }
        return true;
    }

    public function pushValue($value)
    {
        $this->valuesForSelect[] = $value;
        return $this;
    }

    public function generateSql(): string
    {
        if (!$this->checkDataBeforeGenerateSqlString()) {
            throw new \InvalidArgumentException();
        }
        $this->sql .= $this->fields->getString();
        $this->sql .= $this->from->getString();
        if ($this->where) {
            $this->sql .= $this->where->getString();

        }
        return $this->sql;
    }

    /**
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }
}