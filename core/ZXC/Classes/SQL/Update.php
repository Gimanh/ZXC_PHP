<?php

namespace ZXC\Classes\SQL;

use ZXC\Classes\SQL\Conditions\UpdateFields;
use ZXC\Classes\SQL\Conditions\Where;
use ZXC\Interfaces\SqlConditionFields;

class Update
{
    private $sql = 'UPDATE ';
    /**
     * @var $table SqlConditionFields
     */
    private $table;
    /**
     * @var $fields UpdateFields
     */
    private $fields;
    /**
     * @var $where Where
     */
    private $where;

    public function update(SqlConditionFields $table): Update
    {
        $this->table = $table;
        return $this;
    }

    public function fields(SqlConditionFields $fields): Update
    {
        $this->fields = $fields;
        return $this;
    }

    public function where(Where $where): Update
    {
        $this->where = $where;
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
        $fieldsValues = $this->fields->getValues();
        if ($this->where) {
            $whereValues = $this->where->getValues();
            $fieldsValues = array_merge($fieldsValues, $whereValues);
        }
        return $fieldsValues;
    }
}