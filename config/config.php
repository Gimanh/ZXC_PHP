<?php
return [
    'ZXC' => [

        'Router' => [

            'methods' => ['POST' => true, 'GET' => true, 'OPTIONS' => true],

            'routes' => [
                [
                    'route' => 'GET|/|App\Application:hello'
                ]
            ]

        ],

        'Autoload' => [
            '../../../server' => true
        ]
    ]
];