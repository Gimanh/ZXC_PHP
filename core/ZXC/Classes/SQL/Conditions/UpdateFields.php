<?php

namespace ZXC\Classes\SQL\Conditions;

class UpdateFields extends ConditionFields
{
    protected $values = [];

    public function getStringFromFields(): string
    {
        $string = '';
        $fields = [];
        foreach ($this->conditionFields as $field => $value) {
            if (!array_key_exists('value', $value)) {
                throw new \InvalidArgumentException('Value field must be defined for updated fields');
            }
            $fields[] = $field . ' = ?';
            $this->pushValue($value['value']);
        }
        $string .= ' SET ';
        $string .= implode(', ', $fields);
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