<?php

namespace ZXC\Classes\SQL\Conditions;

use ZXC\Interfaces\SqlConditionFields;
use ZXC\Native\Helper;

class Table extends ConditionFields
{
    public function getStringFromFields(): string
    {
        if (Helper::isAssoc($this->conditionFields)) {
            $fields = [];
            foreach ($this->conditionFields as $field => $value) {
                if (isset($value['subQuery']) && !empty($value['subQuery'])) {
                    $fields[] = $this->getSubQuery($value['subQuery']);
                } else {
                    $fields[] = $field;
                }
            }
            $string = implode(',', $fields);
        } else {
            $string = implode(',', $this->conditionFields);
        }
        $string = ' ' . $string . ' ';
        return $string;
    }

//    public function getSubQuery($sqlConditionFields): string
//    {
        //TODO
//        $string = '';
//        if ($sqlConditionFields['query'] instanceof SqlConditionFields) {
//            $string = ' ( ' . $sqlConditionFields['query']->getString() . ' ) ';
//        } else {
//            if (is_string($sqlConditionFields['query'])) {
//                if (!empty($sqlConditionFields['query'])) {
//                    if (strpos($sqlConditionFields['query'], '(') === false) {
//                        $string = ' ( ' . $sqlConditionFields['query'] . ' ) ';
//                    } else {
//                        $string = $sqlConditionFields['query'];
//                    }
//                }
//            }
//        }
//        if (array_key_exists('as', $sqlConditionFields) && $sqlConditionFields['as']) {
//            $string .= ' AS ' . $sqlConditionFields['as'] . ' ';
//        }
//        return $string;
//    }
}