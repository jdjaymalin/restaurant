<?php

return [
    'prodDatabase' => [
        'driver' => 'pgsql',
        'host' => env('DB_Database_Host', 'postgres'),
        'database' => env('DB_Database', 'app'),
        'username' => env('DB_Username', 'root'),
        'password' => env('DB_Password', 'password'),
        'port'=> '5432',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => '',
        'strict' => false,
    ]
];
