<?php

namespace ZXC\Classes\SQL;

use ZXC\Interfaces\SqlConditionFields;

class Select extends SQL
{
    private $sql = 'SELECT ';

    public function select(SqlConditionFields $fields): Select
    {
        $this->fields = $fields;
        return $this;
    }

    public function from(SqlConditionFields $from): Select
    {
        /*if (isset($from['table'])) {
            $this->from = implode(', ', $from['table']) . ' ';
        } else {
            if (isset($from['subQuery'])) {
                if (is_string($from['subQuery'])) {
                    $this->from = $from['subQuery'];
                } else {
                    if ($from['subQuery'] instanceof SQL) {
                        $this->from = $from['subQuery']->generateSql() . ' ';
                    }
                }
            }
        }*/

        $this->from = $from;
        return $this;
    }

    public function join(SqlConditionFields $joins)
    {
        /*foreach ($joins as $join) {
            $this->join .= $join['type'] . ' JOIN ' . $join['table'] . ' ON ' . $join['on'];
        }*/
        $this->join = $joins;
        return $this;
    }

    public function where(SqlConditionFields $where): Select
    {
        /*array_filter($where, function ($value, $key) {
            if (array_key_exists('value', $value)) {
                $this->addValue($value['value']);
                $whereString = $key . ' ' . $value['condition'] . ' ? ';
                if (isset($value['operator'])) {
                    $whereString .= $value['operator'] . ' ';
                }
                $this->where [] = $whereString;
            }
            return $key;
        }, ARRAY_FILTER_USE_BOTH);*/
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

    public function generateSql(): string
    {
        if (!$this->checkDataBeforeGenerateSqlString()) {
            throw new \InvalidArgumentException();
        }
        $this->sql .= $this->fields->getString();
        $this->sql .= $this->from->getString();

//        $fieldsString = $this->fields->getString() . ' ';
//
//        $fromString = $this->fromToString();
//        $whereString = '';
//        $joinString = '';
//
//        return $this->sqlStartString . $this->fields . ' FROM ' . $this->from . ' WHERE ' . implode(' ', $this->where);
    }
}