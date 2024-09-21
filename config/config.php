<?php

use Phalcon\Config\Config;

return new Config([
    'database' => [
        'host'        => 'localhost',
        'username'    => 'root',
        'password'    => '',
        'dbname'      => 'utip',
        'charset'     => 'utf8',
    ],
    'application' => [
        'controllersDir' => APP_PATH . '/controllers/',
        'migrationsDir'  => APP_PATH . '/migrations/',
        'modelsDir'      => APP_PATH . '/models/',
    ]
]);
