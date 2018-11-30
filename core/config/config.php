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
            'Structures' => [
                'class' => '\ZXC\Modules\SQL\StructureControl',
                'options' => [
                    'dir' => '../Structures'
                ]
            ],
            'Auth' => [
                'class' => 'ZXC\Modules\Auth\Auth',
                'options' => [
                    'provider' => 'ZXC\Modules\Auth\Providers\Classic',
                    'options' => [
                        'strongPassword' => [
                            'value' => true,
                            'callback' => 'ZXC\Native\Helper:isValidStrongPassword'
                        ],
                        'structure' => 'authTest',
                        'confirmEmail' => true,
                        'uri' => [
                            'reminder' => 'http://localhost:3000/#/hs/reminder',
                            'confirm' => 'http://www.zxcphp.com/confirm/email',
                        ],
                        'reminder' => [
                            'frequency' => 10,
                            'by' => 'email'//email | custom
                        ],
                        'token' => [
                            'alg' => 'HS256',
                            'secret_key' => 'wSwmnvn*7&h3*90()@2',
                            'access' => [
                                'expire' => 10
                            ],
                            'refresh' => [
                                'expire' => 100
                            ]
                        ],
                        'logger' => [
                            'value' => true,
                            //inner | new
                            'instance' => 'new',
                            //options will be using if instance equal 'new'
                            'options' => [
                                'folder' => '../log',
                                'file' => '/zxc_auth_classic_test.log',
                                'root' => true
                            ]
                        ],
                        //do not need value field for required options
                        //mailer will be using if 'confirmEmail' => true,
                        'mailer' => [
                            //inner | new
                            'instance' => 'new',
                            'options' => [
                                'server' => 'smtp.mailtrap.io',
                                'port' => 465,
//                                'ssl' => true,
                                'user' => 'c696921f893656',
                                'password' => '1de0bbd8c472c4',
                                'from' => 'zxc_php',
                                'fromEmail' => 'zxcphptestf@mail.com'
                            ]
                        ],
                        //do not need value field for required options
                        'db' => [
                            //inner | new
                            'instance' => 'new',
                            'options' => [
                                'dbname' => 'zxc',
                                'dbtype' => 'pgsql',
                                'host' => 'localhost',
                                'port' => 5433,
                                'user' => 'lpingu',
                                'password' => '123456'
                            ]
                        ],
                    ]
                ]
            ],
            'RBAC' => [
                'class' => 'ZXC\Modules\RBAC\RBAC',
                'options' => [
                    'provider' => 'ZXC\Modules\RBAC\Role',
                    'options' => [
                        'role' => [
                            'structure' => [
                                'permissions' => 'permissionsTest',
                                'rolePerm' => 'rolePermTest',
                                'roles' => 'rolesTest',
                                'userRoles' => 'userRoleTest'
                            ],
                            'db' => [
                                'instance' => 'inner',
                            ],
                        ],
                    ],
                ]
            ],
            'Logger' => [
                'class' => 'ZXC\Modules\Logger\Logger',
                'options' => [
                    /**
                     * current app debug lvl
                     */
                    'applevel' => 'debug',
                    'folder' => '../log',
                    'file' => '/log_zxc_test.log',
                    /**
                     * root if set true will load from ZXC_ROOT.'../log/log.log'
                     */
                    'root' => true

                ]
            ],
            'DB' => [
                'class' => 'ZXC\Native\DB',
                'options' => [
                    'dbname' => 'zxc',
                    'dbtype' => 'pgsql',
                    'host' => 'localhost',
                    'port' => 5433,
                    'user' => 'lpingu',
                    'password' => '123456'
                ]
            ],
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
            'middleware' => [
                'CORS' => 'HS\CORS:handler',
            ],
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