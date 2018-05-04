<?php

use PHPUnit\Framework\TestCase;

class DBTest extends TestCase
{
    public function testF()
    {
        $config = [
            'dbname' => 'hs',
            'dbtype' => 'pgsql',
            'host' => 'localhost',
            'port' => 5433,
            'user' => 'postgres',
            'password' => '123456',
        ];
        $db = new \ZXC\Classes\SQL\DB();
        $db->initialize($config);
        $this->assertSame($db->getDsn(), 'pgsql:dbname=hs;host=localhost;port=5433');
    }
}