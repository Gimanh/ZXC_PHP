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
        $fields = new \ZXC\Classes\SQL\Conditions\Fields($fieldsConfig);
        $s1 = $query::create('select');


        $where = [
            'login' => [
                'condition' => '=',
                'value' => 'headhunter',
                'operator' => 'AND',
                'function' => '',
                'subQuery' => [
                    'query' => 'SELECT company_id FROM companytable'
                ],
                'subCondition' => ''
            ],
            'email' => [
                'condition' => '=',
                'value' => 'test@handscream.com',
                'operator' => 'OR',
            ],
            'email1' => [
                'condition' => '=',
                'value' => 'test@handscream.com',
                'operator' => 'AND',
            ]
        ];
        $from = [
            'zxc.users' => [
                'subQuery' => [
                    'query' => 'SELECT some FROM table',
                    'as' => 'qwe'
                ]
            ],
        ];
        $joins = [
            'session' => [
                'as' => 'qwertySession',
                'type' => '',
                'on' => 'ses.id = zxc.users.id'
            ],
            'books' => [
                'as' => 'userBooks',
                'type' => 'inner',
                'on' => 'session.id = userBooks.id'
            ]
        ];
        $from = new \ZXC\Classes\SQL\Conditions\From($from);
        $where = new \ZXC\Classes\SQL\Conditions\Where($where);
        $joins = new \ZXC\Classes\SQL\Conditions\Join($joins);

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