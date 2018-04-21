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
        if ($type === 'insert') {
            return new Insert();
        }
        if ($type === 'update') {
            return new Update();
        }

        throw new \InvalidArgumentException('Unknown query type ' . $type);
    }
}