<?php

class SelectTest
{
    public function testSimpleSelect()
    {
        $struct = [
            'name'    => 'structName',
            'tableName'=>'tableName',
            'content' => [
                'fields' => [
                    'fname'=>[],
                    'lname'=>[],
                    'email'=>[],
                    'password'=>[],
                    'login'=>[],
                ]
            ]
        ];

    }
}