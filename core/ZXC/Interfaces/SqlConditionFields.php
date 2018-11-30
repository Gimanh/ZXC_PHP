<?php

namespace ZXC\Interfaces;

interface SqlConditionFields
{
    public function __construct(array $conditionFields);

    public function getString(): string;
}