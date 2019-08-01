<?php

namespace ZXC\Interfaces\Native;

interface IDB
{
    public function exec($query, array $params = []);

    /**
     * @method getDbType
     * @return string
     */
    public function getDbType();

    /**
     * @method getErrorMessage
     * @return string
     */
    public function getErrorMessage();

    public function lastInsertId($seq);
}