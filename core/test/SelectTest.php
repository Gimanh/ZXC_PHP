<?php

use PHPUnit\Framework\TestCase;

$dir = __DIR__;
$config = [];
$file = $dir . '/../index.php';
if (file_exists($file)) {
    require_once $file;
}

class SelectTest extends TestCase
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

        $fieldsConfig = [
            'login' => [
                'value' => '',
                'sql' => true
            ],
            'password' => [
                'value' => '',
                'sql' => true
            ]
        ];
        $from = [
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

        $fields = new \ZXC\Classes\SQL\Conditions\Fields($fieldsConfig);
        $from = new \ZXC\Classes\SQL\Conditions\From($from);
        $where = new \ZXC\Classes\SQL\Conditions\Where($where);

        $query = new \ZXC\Classes\SQL\Query();
        $s1 = $query::create('select');
        $selectString = $s1->select($fields)->from($from)->where($where)->generateSql();
        $this->assertSame($selectString, 'SELECT login, password FROM zxc.users WHERE login = ? AND email = ? ');
    }
}