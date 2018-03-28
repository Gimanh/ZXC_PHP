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

                if (isset($value['condition'])) {
                    $string .= $value['condition'] . ' ';
                } else {
                    $string .= ' = ';
                }
                if ($value['subQuery'] instanceof SqlConditionFields) {
                    $string .= ' ( ' . $value['subQuery']->getString() . ' ) ';
                } else {
                    $string .= ' ' . $value['subQuery'] . ' ';
                }

                if ($i !== $length) {
                    if (isset($value['operator'])) {
                        $string .= $value['operator'] . ' ';
                    } else {
                        $string .= ' AND ';
                    }
                }

            } else {

                if (isset($value['condition'])) {
                    $string .= $value['condition'] . ' ? ';
                } else {
                    $string .= ' = ? ';
                }
                if ($i !== $length) {
                    if (isset($value['operator'])) {
                        $string .= $value['operator'] . ' ';
                    } else {
                        $string .= ' AND ';
                    }
                }
            }
            $this->pushValue($value['value']);
        }
        $string = ' WHERE ' . $string;
        return $string;
    }

    public function getValues()
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