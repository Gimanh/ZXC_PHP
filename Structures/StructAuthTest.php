<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 17/11/2018
 * Time: 00:54
 */
return [
    'name' => 'authTest',
    'table' => 'auth_test.users',
    'fields' => [
        'id' => [
            'type' => 'string'
        ],
        'login' => [
            'type' => 'string'
        ],
        'email' => [
            'type' => 'string'
        ],
        'password' => [
            'type' => 'string'
        ],
        'active_status' => [
            'type' => 'int'
        ],
        'block_status' => [
            'type' => 'int'
        ],
        'email_activation_code' => [
            'type' => 'string'
        ],
        'email_activation' => [
            'type' => 'int'
        ],
        'remind_password_code' => [
            'type' => 'string'
        ],
        'agreement' => [
            'type' => 'int'
        ],
        'last_remind_time' => [
            'type' => 'string'
        ]
    ]
];