<?php

namespace ZXC\Classes\SQL;

abstract class SQL
{
    abstract public function checkDataBeforeGenerateSqlString(): bool;

    abstract public function generateSql(): string;
}