<?php
return [
    'ZXC' => [
        'Modules' => [
            'Session' => [
                'class' => '\ZXC\Native\HTTP\Session',
                'options' => [
                    'prefix' => 'zxc_',
                    'time' => 6200,
                    'path' => '/',
                    'domain' => 'zxc.com'
                ]
            ],
            'Logger' => [
                'class' => 'ZXC\Modules\Logger\Logger',
                'defer'=>true,
                'options' => [
                    /**
                     * current app debug lvl
                     */
                    'applevel' => 'debug',
                    'folder' => 'log',
                    'file' => '/log_zxc_test.log',
                    /**
                     * root if set true will load from ZXC_ROOT.'../log/log.log'
                     */
                    'root' => true

                ]
            ],
//            'DB' => [
//                'class' => 'ZXC\Native\DB',
//                'options' => [
//                    'dbname' => 'zxc',
//                    'dbtype' => 'pgsql',
//                    'host' => 'localhost',
//                    'port' => 5432,
//                    'user' => 'postgres',
//                    'password' => '123456'
//                ]
//            ],
            'Mailer' => [
                'class' => 'ZXC\Modules\Mailer\Mail',
                'options' => [
                    'server' => 'smtp.mailtrap.io',
                    'port' => 465,
                    'ssl' => true,
                    'user' => '7b12bd3165709b',
                    'password' => '1de0bbd8c472c4',
                    'from' => 'zxc_php',
                    'fromEmail' => 'zxcphptestf@mail.com'
                ]
            ]
        ],
        'Autoload' => [
            /**
             * root is ZXC_ROOT (index directories)
             */
            '../../' => true,
            '' => true
        ],
        'Router' => [
            //работает для всех запросов для роутов есть before after хуки
//            'middleware' => [
//                'CORS' => 'HS\CORS:handler',
//            ],
            'methods' => ['POST' => true, 'GET' => true, 'OPTIONS' => true],
            'notFound' => function () {
            },
            'routes' => [
                [
                    'route' => 'POST|/|FakeClassForTest:fakeMethod'
                ],
                [
                    'route' => 'GET|/',
                    'before' => function () {
                        return ' before';
                    },
                    'callback' => function ($zxc, $params) {
                        return $params['resultBefore'] . ' <= HI! => ';
                    },
                    'after' => function ($zxc, $params) {

                        return $params['resultMain'] . 'after';
                    },
                    'hooksResultTransfer' => true
                ],
                [
                    'route' => 'GET|/:user',
                    'before' => function () {
                        return 'You are the best ';
                    },
                    'callback' => function ($zxc, $params) {
                        return $params['resultBefore'] . 'user "' . $params['routeParams']['user'] . '"';
                    },
                    'after' => function ($zxc, $params) {
                        return $params['resultMain'] . ' after me %)';
                    },
                    'hooksResultTransfer' => true
                ]
            ],
        ]
    ]
];