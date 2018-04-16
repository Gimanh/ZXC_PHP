<?php

namespace ZXC\Classes\SQL;

final class Query
{
    public static function create($type)
    {
        if ($type) {
            $type = strtolower($type);
        }
        if ($type === 'select') {
            return new Select();
        }
        if ($type === 'delete') {
            return new Delete();
        }

        throw new \InvalidArgumentException('Unknown query type ' . $type);
    }
}