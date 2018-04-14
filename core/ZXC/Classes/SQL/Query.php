<?php

namespace ZXC\Classes\SQL;

final class Query
{
    public static function create($type)
    {
        if ($type === 'select') {
            return new Select();
        }

        throw new \InvalidArgumentException('Unknown query type ' . $type);
    }
}