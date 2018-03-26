<?php

namespace ZXC\Classes\SQL;

class Fields implements \ZXC\Interfaces\Fields
{
    private $table = [];
    private $fields = [];

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function getSqlFieldsString(): string
    {
        $sqlFields = array_filter($this->fields, function ($value, $key) {
            if (!array_key_exists('sql', $value) ||
                (array_key_exists('sql', $value) && $value['sql'] === true)) {
                return $key;
            }
            return false;
        }, ARRAY_FILTER_USE_BOTH);
        $sqlFields = implode(', ', array_keys($sqlFields));
        return $sqlFields;
    }

    public function getFieldsString(): string
    {
        foreach ($this->fields as $field) {
            $stop = false;
        }
        // TODO: Implement getFieldsString() method.
    }

    public function getFieldValue(string $fieldName)
    {
        // TODO: Implement getFieldValue() method.
    }

    public function setFieldValue(string $fieldName, $value): bool
    {
        // TODO: Implement setFieldValue() method.
    }

    public function setFieldsValue(array $values): bool
    {
        // TODO: Implement setFieldsValue() method.
    }

    public function blockFieldForSql(string $fieldName): bool
    {
        // TODO: Implement blockFieldForSql() method.
    }

    public function setTable(array $value): bool
    {
        // TODO: Implement setTable() method.
    }

    public function setFieldsValuesFromSqlResult()
    {
        //TODO
    }
}