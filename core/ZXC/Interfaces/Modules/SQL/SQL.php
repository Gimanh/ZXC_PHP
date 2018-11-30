<?php

namespace ZXC\Interfaces\Modules\SQL;

use ZXC\Native\DB;

interface SQL
{
    public static function getSQLString();

    public static function join($mode);

    public static function exec(DB $db);
}