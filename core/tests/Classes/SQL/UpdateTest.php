<?php

use \PHPUnit\Framework\TestCase;

class UpdateTest extends TestCase
{
    public function testSimpleUpdate()
    {
        $config = [
            'dbname' => 'hs',
            'dbtype' => 'pgsql',
            'host' => 'localhost',
            'port' => 5433,
            'user' => 'postgres',
            'password' => '123456',
        ];
        $db = new \ZXC\Native\DB();
        $db->initialize($config);

        $tableConfig = [
            'zxc.users' => [],
        ];
        $updatedFieldsConfig = [
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
        $whereFieldsConfig = [
            'id' => [
                'condition' => '=',
                'value' => '1',
                'operator' => 'AND',
            ],
            'active' => [
                'condition' => '=',
                'value' => 0,
            ]
        ];
        $table = new \ZXC\Classes\SQL\Conditions\Table($tableConfig);
        $where = new \ZXC\Classes\SQL\Conditions\Where($whereFieldsConfig);
        $updatedFields = new \ZXC\Classes\SQL\Conditions\UpdateFields($updatedFieldsConfig);
        $query = new \ZXC\Classes\SQL\Query();
        $update = $query::create('update');
        $updateString = $update->update($table)->fields($updatedFields)->generateSql();
        $this->assertSame($updateString, 'UPDATE zxc.users SET login = ?, email = ? ');
        $this->assertSame($update->getValues(), ['headhunter', 'test@handscream.com']);
    }

    public function testSimpleUpdateWithWhereConditions()
    {
        $config = [
            'dbname' => 'hs',
            'dbtype' => 'pgsql',
            'host' => 'localhost',
            'port' => 5433,
            'user' => 'postgres',
            'password' => '123456',
        ];
        $db = new \ZXC\Native\DB();
        $db->initialize($config);

        $tableConfig = [
            'zxc.users' => [],
        ];
        $updatedFieldsConfig = [
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
        $whereFieldsConfig = [
            'id' => [
                'condition' => '=',
                'value' => '1',
                'operator' => 'AND',
            ],
            'active' => [
                'condition' => '=',
                'value' => 0,
            ]
        ];
        $table = new \ZXC\Classes\SQL\Conditions\Table($tableConfig);
        $where = new \ZXC\Classes\SQL\Conditions\Where($whereFieldsConfig);
        $updatedFields = new \ZXC\Classes\SQL\Conditions\UpdateFields($updatedFieldsConfig);
        $query = new \ZXC\Classes\SQL\Query();
        $update = $query::create('update');
        $updateString = $update->update($table)->fields($updatedFields)->where($where)->generateSql();
        $this->assertSame($updateString, 'UPDATE zxc.users SET login = ?, email = ? WHERE id = ? AND active = ? ');
        $this->assertSame($update->getValues(), ['headhunter', 'test@handscream.com', '1', 0]);
    }
}