<?php

namespace ZXC\Classes\SQL\Conditions;


class InsertFields extends ConditionFields
{
    protected $values = [];

    public function getStringFromFields(): string
    {
        $fields = [];
        foreach ($this->conditionFields as $field => $value) {
            if (!array_key_exists('value', $value)) {
                throw new \InvalidArgumentException('Value field must be defined in fields "where"');
            }
            $fields[] = $field;
            $this->pushValue($value['value']);
        }

        $placeholders = implode(", ", array_fill(0, count($fields), '?'));
        $fields = implode(', ', $fields);
        $string = '(' . $fields . ') VALUES (' . $placeholders . ')';
        return $string;
    }

    public function pushValue($value)
    {
        $this->values[] = $value;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}