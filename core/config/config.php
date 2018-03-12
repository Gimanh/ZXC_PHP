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
                'filePath' => '../../log/log.log',
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
        ]
    ]
];