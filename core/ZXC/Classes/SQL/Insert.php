<?php

namespace ZXC\Classes\SQL;

use ZXC\Classes\SQL\Conditions\Where;
use ZXC\Interfaces\SqlConditionFields;

class Insert
{
    private $sql = 'INSERT INTO ';
    /**
     * @var $table SqlConditionFields
     */
    private $table;
    /**
     * @var $fields Where
     */
    private $fields;

    public function insert(SqlConditionFields $table): Insert
    {
        $this->table = $table;
        return $this;
    }

    public function fields(SqlConditionFields $fields): Insert
    {
        $this->fields = $fields;
        return $this;
    }

    public function checkDataBeforeGenerateSqlString(): bool
    {
        if (!$this->fields) {
            return false;
        }
        return true;
    }

    public function generateSql(): string
    {
        if (!$this->checkDataBeforeGenerateSqlString()) {
            throw new \InvalidArgumentException();
        }
        $this->sql .= $this->table->getString();
        $this->sql .= $this->fields->getString();
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
        return $this->fields->getValues();
    }
}