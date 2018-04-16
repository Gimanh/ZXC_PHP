<?php

use PHPUnit\Framework\TestCase;

$dir = __DIR__;
$config = [];
$file = $dir . '/../index.php';
if (file_exists($file)) {
    require_once $file;
}

class DeleteTest extends TestCase
{
    public function testSimpleDelete()
    {
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

        $from = new \ZXC\Classes\SQL\Conditions\From($from);
        $where = new \ZXC\Classes\SQL\Conditions\Where($where);

        $query = new \ZXC\Classes\SQL\Query();
        $s1 = $query::create('delete');
        $selectString = $s1->delete()->from($from)->where($where)->generateSql();
        $this->assertSame($selectString, 'DELETE FROM zxc.users WHERE login = ? AND email = ? ');
    }
}