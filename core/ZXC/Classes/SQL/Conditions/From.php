<?php

namespace ZXC\Classes\SQL\Conditions;

use ZXC\Interfaces\SqlConditionFields;
use ZXC\Native\Helper;

class From extends ConditionFields
{
    public function getStringFromFields(): string
    {
        if (Helper::isAssoc($this->conditionFields)) {
            $fields = [];
            foreach ($this->conditionFields as $field => $value) {
                if (isset($value['subQuery']) && !empty($value['subQuery'])) {
                    if ($value['subQuery'] instanceof SqlConditionFields) {
                        $fields[] = ' ( ' . $value['subQuery']->getString() . ' ) ';
                        break;
                    } else {
                        $fields[] = ' ( ' . $value['subQuery'] . ' ) ';
                        break;
                    }
                } else {
                    $fields[] = $field;
                }
            }
            $string = implode(',', $fields);
        } else {
            $string = implode(',', $this->conditionFields);
        }
        return $string;
    }
}