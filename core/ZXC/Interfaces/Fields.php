<?php

namespace ZXC\Interfaces;

interface Fields
{
    public function getFieldsString(): string;

    public function getSqlFieldsString(): string;

    public function getFieldValue(string $fieldName);

    public function setFieldValue(string $fieldName, $value): bool;

    public function setFieldsValue(array $values): bool;

    public function blockFieldForSql(string $fieldName): bool;

    public function setTable(array $value): bool;

    public function setFieldsValuesFromSqlResult();
}