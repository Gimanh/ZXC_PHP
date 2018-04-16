<?php

namespace ZXC\Classes\SQL;

use ZXC\Classes\SQL\Conditions\Where;
use ZXC\Interfaces\SqlConditionFields;

class Delete extends SQLExecute
{
    private $sql = 'DELETE ';
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
     * @var $where Where
     */
    private $where;

    public function delete(): Delete
    {
        return $this;
    }

    public function from(SqlConditionFields $from): Delete
    {
        $this->from = $from;
        return $this;
    }

    public function join(SqlConditionFields $joins)
    {
        $this->join = $joins;
        return $this;
    }

    public function where(SqlConditionFields $where): Delete
    {
        $this->where = $where;
        return $this;
    }

    public function checkDataBeforeGenerateSqlString(): bool
    {
        if (!$this->from) {
            return false;
        }
        return true;
    }

    public function generateSql(): string
    {
        if (!$this->checkDataBeforeGenerateSqlString()) {
            throw new \InvalidArgumentException();
        }
        $this->sql .= $this->from->getString();
        if ($this->where) {
            $this->sql .= $this->where->getString();

        }
        $this->sql = preg_replace('!\s+!', ' ', $this->sql);
        return $this->sql;
    }

    /**
     * @return string
     */
    public function getSql(): string
    {
        return $this->sql;
    }

    public function getValues(): array
    {
        return $this->where->getValues();
    }
}