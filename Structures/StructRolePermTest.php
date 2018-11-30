<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 24/11/2018
 * Time: 23:50
 */
return [
    'name' => 'rolePermTest',
    'table' => 'auth_test.role_perm',
    'fields' => [
        'role_id' => [
            'type' => 'int'
        ],
        'perm_id' => [
            'type' => 'string'
        ],
    ]
];