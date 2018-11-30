<?php

namespace ZXC\Classes\SQL;

final class Query
{
    public static function create($type)
    {
        if ($type) {
            $type = strtolower($type);
        }
        switch ($type) {
            case 'select':
                return new Select();
                break;
            case 'insert':
                return new Insert();
                break;
            case 'update':
                return new Update();
                break;
            case 'delete':
                return new Delete();
                break;
            default:
                throw new \InvalidArgumentException('Unknown query type ' . $type);
        }
    }
}