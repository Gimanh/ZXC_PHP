<?php

namespace ZXC\Classes\SQL\Conditions;

class Fields extends ConditionFields
{
    public function __construct(array $fields)
    {
        parent::__construct($fields);
    }


    public function getStringFromFields(): string
    {
        $sqlFields = array_filter($this->conditionFields, function ($value, $key) {
            if (!array_key_exists('sql', $value) ||
                (array_key_exists('sql', $value) && $value['sql'] === true)) {
                return $key;
            }
            return false;
        }, ARRAY_FILTER_USE_BOTH);
        $sqlFields = implode(', ', array_keys($sqlFields));
        $sqlFields .= ' ';
        return $sqlFields;
    }
}