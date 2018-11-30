<?php
return [
    'ZXC' => [
        'Autoload' => [
            /**
             * root is ZXC_ROOT (index directories)
             */
            '../../' => true,
            '' => true
        ],
        'Logger' => [
            /**
             * current app debug lvl
             */
            'applevel' => 'debug',
            'settings' => [
                'filePath' => '../log/log.log',
                /**
                 * root if set true will load from ZXC_ROOT.'/../../log/log.log'
                 */
                'root' => true
            ]
        ],
        'Session' => [
            'prefix' => 'zxc_',
            'time' => 6200,
            'path' => '/',
            'domain' => 'zxc.com'
        ],
        'Router' => [
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
                    return $params['resultBefore'] . 'user "'. $params['routeParams']['user'].'"';
                },
                'after' => function ($zxc, $params) {
                    return $params['resultMain'] . ' after me %)';
                },
                'hooksResultTransfer' => true
            ]
        ]
    ]
];