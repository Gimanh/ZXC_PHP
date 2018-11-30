<?php

use PHPUnit\Framework\TestCase;

class DBTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConnectionString()
    {
        $config = [
            'dbname' => 'hs1',
            'dbtype' => 'pgsql',
            'host' => 'localhost',
            'port' => 5433,
            'user' => 'postgres',
            'password' => '123456q23',
        ];
        $db = new \ZXC\Native\DB();
        $db->initialize($config);
        $this->assertSame($db->getDsn(), 'pgsql:dbname=hs;host=localhost;port=5433');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConfigException()
    {
        $config = [
            'dbtype' => 'pgsql',
            'host' => 'localhost',
            'port' => 5433,
            'user' => 'postgres',
            'password' => '123456',
        ];
        $db = new \ZXC\Native\DB();
        $db->initialize($config);
    }
}