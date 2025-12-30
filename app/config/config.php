<?php

return new \Phalcon\Config\Config([
    'database' => [
        'adapter'     => 'Mysql',
        'host'        => 'localhost',
        'username'    => 'root',
        'password'    => '',
        'dbname'      => 'paulita_db',
        'charset'     => 'utf8',
    ],
    'application' => [
        'controllersDir' => APP_PATH . '/controllers/',
        'modelsDir'      => APP_PATH . '/models/',
        'viewsDir'       => APP_PATH . '/views/',
        'pluginsDir'     => APP_PATH . '/plugins/',
        'baseUri'        => '/inventario/',
    ]
]);