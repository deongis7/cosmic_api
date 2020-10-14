<?php
return array(

    'default' => 'pgsql',

    'connections' => array(

        # Our primary database connection
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', 5432),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => env('DB_PREFIX', ''),
            'schema' => env('DB_SCHEMA', 'public'),
            'sslmode' => env('DB_SSL_MODE', 'prefer'),
        ],

        # Our secondary database connection
        'pgsql2' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST2', '127.0.0.1'),
            'port' => env('DB_PORT2', 5432),
            'database' => env('DB_DATABASE2', 'forge'),
            'username' => env('DB_USERNAME2', 'forge'),
            'password' => env('DB_PASSWORD2', ''),
            'charset' => env('DB_CHARSET2', 'utf8'),
            'prefix' => env('DB_PREFIX2', ''),
            'schema' => env('DB_SCHEMA2', 'public'),
            'sslmode' => env('DB_SSL_MODE2', 'prefer'),
        ],

        # Our third database connection
        'pgsql3' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST3', '127.0.0.1'),
            'port' => env('DB_PORT3', 5432),
            'database' => env('DB_DATABASE3', 'forge'),
            'username' => env('DB_USERNAME3', 'forge'),
            'password' => env('DB_PASSWORD3', ''),
            'charset' => env('DB_CHARSET3', 'utf8'),
            'prefix' => env('DB_PREFIX3', ''),
            'schema' => env('DB_SCHEMA3', 'public'),
            'sslmode' => env('DB_SSL_MODE3', 'prefer'),
        ],




    ),


    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'cluster' => env('REDIS_CLUSTER', false),

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DB', 0),
        ],

        'cache' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('REDIS_CACHE_DB', 1),
        ],

    ],

);
