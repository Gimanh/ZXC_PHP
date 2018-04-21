<?php

use PHPUnit\Framework\TestCase;

$dir = __DIR__;
$config = [];
$file = $dir . '/../index.php';
if (file_exists($file)) {
    require_once $file;
}

class InsertTest extends TestCase
{
    public function testSimpleSelect()
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
        $where = [
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
        $where = new \ZXC\Classes\SQL\Conditions\InsertFields($where);

        $query = new \ZXC\Classes\SQL\Query();
        $insert = $query::create('insert');
        $insertString = $insert->insert($table)->fields($where)->generateSql();
        $this->assertSame($insertString, 'INSERT INTO zxc.users (login, email) VALUES (?, ?) ');
        $this->assertSame($insert->getValues(), ['headhunter', 'test@handscream.com']);
    }
}