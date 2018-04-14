<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 27/03/2018
 * Time: 23:24
 */

namespace ZXC\Classes\SQL\Conditions;


use ZXC\Interfaces\SqlConditionFields;

class Where extends ConditionFields
{
    protected $values = [];

    public function getStringFromFields(): string
    {
        $i = 0;
        $string = ' ';
        $length = count($this->conditionFields);
        foreach ($this->conditionFields as $field => $value) {
            if (!array_key_exists('value', $value)) {
                throw new \InvalidArgumentException('Value field must be defined in fields "where"');
            }
            $i++;
            $string .= $field . ' ';
            if (isset($value['subQuery']) && !empty($value['subQuery'])) {
                $string .= $this->getCondition($value);
                $string .= $this->getSubQuery($value['subQuery']);
                if ($i !== $length) {
                    $string .= $this->getOperator($value);
                }
            } else {
                $string .= $this->getCondition($value) . ' ? ';
                if ($i !== $length) {
                    $string .= $this->getOperator($value);
                }
            }
            $this->pushValue($value['value']);
        }
        $string = ' WHERE ' . $string;
        return $string;
    }

    public function getSubQuery($sqlConditionFields): string
    {
        $string = '';
        if ($sqlConditionFields['query'] instanceof SqlConditionFields) {
            $string = ' ( ' . $sqlConditionFields['query']->getString() . ' ) ';
        } else {
            if (is_string($sqlConditionFields['query'])) {
                if (!empty($sqlConditionFields['query'])) {
                    if (strpos($sqlConditionFields['query'], '(') === false) {
                        $string = ' ( ' . $sqlConditionFields['query'] . ' ) ';
                    } else {
                        $string = $sqlConditionFields['query'];
                    }
                }
            }
        }
        return $string;
    }

    public function getOperator($value): string
    {
        if (isset($value['operator'])) {
            return $value['operator'] . ' ';
        } else {
            return ' AND ';
        }
    }

    public function getCondition($value): string
    {
        if (isset($value['condition'])) {
            return $value['condition'] . ' ';
        } else {
            return ' = ';
        }
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function pushValue($value)
    {
        $this->values[] = $value;
    }

    public function resetValues()
    {
        $this->values = [];
    }
}