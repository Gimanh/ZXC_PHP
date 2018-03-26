<?php

use PHPUnit\Framework\TestCase;

$dir = __DIR__;
$config = [];
$file = $dir . '/../index.php';
if (file_exists($file)) {
    require_once $file;
}

class StructureTest extends TestCase
{
    public function testFields()
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
                'ui' => [
                    'show' => true,
                    'component' => [
                        'params' => [],
                        'childrenComponents' => []
                    ]
                ]
            ],
            'password' => [
                'value' => '',
                'sql' => false
            ],
            'password1' => [
                'value' => '',
                'sql' => true
            ]
        ];
        $query = new \ZXC\Classes\SQL\Query();
        $fields = new \ZXC\Classes\SQL\Fields($fieldsConfig);
        $s1 = $query::create('select');


        $where = [
            'login' => [
                'condition' => '=',
                'value' => 'headhunter',
                'operator' => 'AND',
                'function' => '',
                'subQuery' => '',
                'subCondition' => ''
            ],
            'email' => [
                'condition' => '=',
                'value' => 'test@handscream.com',
            ]
        ];

        $from = [
            'table' => ['zxc.users'],
            'subQuery' => '',
        ];
        $joins = [
            [
                'type' => 'left',
                'table' => 'session AS ses',
                'on' => 'ses.id = zxc.users.id'

            ]
        ];
        $stop = $s1->select($fields)->from($from)->where($where)->join($joins)->generateSql();
        $stop = false;


        $fields = [
            'email' => [
                'type' => 'integer',
                'ui' => [
                    'showField' => true,
                    'component' => [
                        'name' => 'v-tab',
                        'params' => [
                            'value' => ''
                        ]
                    ]
                ]
            ]
        ];
    }
}