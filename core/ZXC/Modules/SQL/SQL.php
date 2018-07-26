<?php

namespace ZXC\Modules\SQL;

use ZXC\Native\DB;

interface SQL
{
    public static function getSQLString();

    public static function exec(DB $db);
}