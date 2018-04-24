<?php

use PHPUnit\Framework\TestCase;

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
        $delete = $query::create('delete');
        $selectString = $delete->delete()->from($from)->where($where)->generateSql();
        $this->assertSame($selectString, 'DELETE FROM zxc.users WHERE login = ? AND email = ? ');
        $this->assertSame($delete->getValues(), ['headhunter', 'test@handscream.com']);
    }
}