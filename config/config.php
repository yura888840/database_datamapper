<?php

/**
 * Array with Config data
 */
return [
    'db' => [
        'connections' => [
            'onlineconvert' => [
                'host'      => '192.168.33.99',
                'port'      => 3306,
                'db'        => 'converter',
                'username'  => 'root',
                'password'  => 'root',
                'charset'   => 'utf8'
            ],
            'usermanagement' => [
                'host'      => '192.168.33.99',
                'port'      => 3306,
                'db'        => 'user_management',
                'username'  => 'root',
                'password'  => 'root',
                'charset'   => 'utf8'
            ]
        ]
    ],
    'logger' => [
        'name' => 'chunk.log'
    ]
];
