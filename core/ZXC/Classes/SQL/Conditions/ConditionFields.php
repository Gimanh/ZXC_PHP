<?php

namespace ZXC\Classes\SQL\Conditions;

use ZXC\Interfaces\SqlConditionFields;

abstract class ConditionFields implements SqlConditionFields
{
    protected $conditionFields = [];

    public function __construct(array $conditionFields = [])
    {
        $this->conditionFields = $conditionFields;
    }

    abstract public function getStringFromFields(): string;

    public function getString(): string
    {
        return $this->getStringFromFields() . ' ';
    }

    public function getConditionFields(): array
    {
        return $this->conditionFields;
    }
}