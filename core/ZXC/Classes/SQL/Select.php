<?php

namespace ZXC\Classes\SQL;

class Select extends SQL
{
    private $sqlStartString = 'SELECT ';

    public function select(Fields $fields): Select
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param array $from ['fields'=>['table1', 'table2']]
     * @return Select
     */
    public function from(array $from): Select
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

    public function join(array $joins)
    {
        /*foreach ($joins as $join) {
            $this->join .= $join['type'] . ' JOIN ' . $join['table'] . ' ON ' . $join['on'];
        }*/
        $this->join = $joins;
        return $this;
    }

    /**
     * @param array $where ['field',]
     * @return Select
     */
    public function where(array $where): Select
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
        //TODO
        return true;
    }

    public function generateSql(): string
    {
        if (!$this->checkDataBeforeGenerateSqlString()) {
            throw new \InvalidArgumentException();
        }
        $fieldsString = $this->fields->getSqlFieldsString() . ' ';
        $fromString = $this->fromToString();
        $whereString = '';
        $joinString = '';

        return $this->sqlStartString . $this->fields . ' FROM ' . $this->from . ' WHERE ' . implode(' ', $this->where);
    }
}