<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 18/08/2018
 * Time: 17:05
 */

namespace ZXC\Modules\SQL;

class StructurePGSQL extends StructureBaseSQL
{
    /**
     * Fields correlation from one table to another
     * like this (WHERE.... AND articles.user_id = userAlias.id AND articles.ident = userAlias.status  )
     * @var array
     */
    private $joinWhereFields = [];

    private $joinUpdateFieldsCorrelation = [];

    public function select()
    {
        $sqlString = 'SELECT ';
        $sqlString .= $this->getFieldsString();
        $sqlString .= $this->getFromString();
        $sqlString .= $this->getJoinString();
        $sqlString .= $this->getWhereString();
        return $sqlString;
    }

    public function delete()
    {
        $sqlString = 'DELETE ';
        $sqlString .= $this->getFromString();
        if ($this->join) {
            $sqlString .= $this->buildJoinWithUsing();
        } else {
            $sqlString .= $this->getJoinString();
        }
        if ($this->join) {
            /**
             * Create string with placeholders
             */
            $sqlString .= $this->getWhereString();
            /**
             * Create string with correlation
             * like this (WHERE.... AND articles.user_id = userAlias.id AND articles.ident = userAlias.status  )
             */
            $sqlString .= implode(', ', $this->joinWhereFields);
        } else {
            $sqlString .= $this->getWhereString();
        }
        $this->joinWhereFields = [];
        return $sqlString;
    }

    private function buildJoinWithUsing()
    {
        $using = 'USING ';
        $usingList = [];
        foreach ($this->join as $item) {
            $result = $this->getUsingStringForJoins($item);
            if ($result['using']) {
                $usingList[] = $result['using'];
                $this->joinWhereFields[] = $result['whereFieldsCorrelation'];
            }

        }
        return $using . implode(', ', $usingList) . ' ';
    }

    public function getUsingStringForJoins($join)
    {
        $result = [
            'using' => '',
            'whereFieldsCorrelation' => ''
        ];
        /**
         * @var $structure StructurePGSQL
         */
        $structure = $join['structure'];
        $as = null;
        if ($join['options']['as']) {
            $as = $join['options']['as'];
        } else {
            $as = $structure->getJoinAlias();
        }
        $result['using'] .= $structure->getTable() . ' ' . $as;
        $result['whereFieldsCorrelation'] = $this->getFieldsCorrelationString($join['options'], $as);
        return $result;
    }

    private function getFieldsCorrelationString($options, $as)
    {
        $result = '';
        foreach ($options['fieldsCorrelation'] as $item => $value) {
            $result .= $options['joinOperatorWhere'] . ' ' . $as . '.' . $item . ' ' . $value['condition'] . ' ' . $value['tableAlias'] . '.' . $value['field'] . ' ';
        }
        return $result . ' ';
    }

    public function update()
    {
        $sqlString = 'UPDATE ';
        $sqlString .= $this->getTable() . ' AS ' . $this->getJoinAlias() . ' ';
        $sqlString .= 'SET ';
        $sqlString .= $this->updateConditionToString() . ' ';
        $sqlString .= $this->getUpdateJoinString() . ' ';
        $sqlString .= $this->getWhereString() . ' ';
        $sqlString .= implode(' ', $this->joinUpdateFieldsCorrelation) . ' ';
        return $sqlString;
    }

    public function updateConditionToString()
    {
        $setFields = [];
//        $alias = $this->getJoinAlias();
        if (!$this->update) {
            throw new \InvalidArgumentException('Fields for update is undefined please use setUpdate');
        }
        foreach ($this->update as $field => $value) {
            $setFields [] = /*$alias . '.' . */
                $field . ' = ?';
            $this->values[] = $value['value'];
        }
        $setFields = implode(', ', $setFields);
        return $setFields;
    }

    public function getUpdateJoinString()
    {
        $sqlJoinString = '';
        if ($this->join) {
            $sqlJoinString = 'FROM ';
            foreach ($this->join as $join) {
                /**
                 * @var $structure StructurePGSQL
                 */
                $structure = $join['structure'];
                $sqlJoinString .= $structure->getTable() . ' AS ' . $structure->getJoinAlias() . ' ';
                foreach ($join['options']['fieldsCorrelation'] as $key => $value) {
                    $this->joinUpdateFieldsCorrelation[] = $value['operator'] . ' ' . $structure->getJoinAlias() . '.' . $key . ' ' . $value['condition'] . ' ' . $this->getJoinAlias() . '.' . $value['field'] . ' ';
                }
            }
        }
        return $sqlJoinString;
    }

    public function insert()
    {
        $sqlString = 'INSERT INTO ';
        $sqlString .= $this->getTable() . ' ';
        $fields = $this->getInsertedFieldsAndSetValues();
        $sqlString .= '(' . implode(', ', $fields) . ') ';
        $sqlString .= 'VALUES ';
        if (is_array($this->values[0])) {
            if (count($fields) > 1) {
                $placeholdersCount = implode(', ', array_fill(0, count($fields), '?'));
                $sqlString .= implode(', ', array_fill(0, count($this->values[0]), '(' . $placeholdersCount . ') '));
            } else {
                $sqlString .= implode(', ', array_fill(0, count($this->values[0]), '(' . '?' . ') '));
            }
        } else {
            $sqlString .= '(' . implode(', ', array_fill(0, count($fields), '?')) . ') ';
        }
        return $sqlString;
    }

    public function getInsertedFieldsAndSetValues()
    {
        if (!$this->insert) {
            throw new \InvalidArgumentException('Fields for insert is undefined please use setInsert');
        }
        $fields = [];
        foreach ($this->insert as $field => $value) {
            $fields [] = $field;
            $this->values[] = $value['value'];
        }
        return $fields;
    }
}