<?php

use PHPUnit\Framework\TestCase;

class InsertTest extends TestCase
{
    public function testSimpleInsert()
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

        $table = [
            'zxc.users' => [],
        ];
        $insertedFields = [
            'login' => [
                'condition' => '=',
                'value' => 'headhunter',
                'operator' => 'AND',
            ],
            'email' => [
                'condition' => '=',
                'value' => 'test@handscream.com',
            ]
        ];

        $table = new \ZXC\Classes\SQL\Conditions\Table($table);
        $insertedFields = new \ZXC\Classes\SQL\Conditions\InsertFields($insertedFields);

        $query = new \ZXC\Classes\SQL\Query();
        $insert = $query::create('insert');
        $insertString = $insert->insert($table)->fields($insertedFields)->generateSql();
        $this->assertSame($insertString, 'INSERT INTO zxc.users (login, email) VALUES (?, ?) ');
        $this->assertSame($insert->getValues(), ['headhunter', 'test@handscream.com']);
    }
}