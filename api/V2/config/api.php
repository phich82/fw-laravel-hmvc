<?php

return [
    'prefix' => 'api',
    'version' => 'v2',
    'middleware' => 'api',
    'namespace' => 'Api\V2\Http\Controllers',
    'test' => [
        'demo' => 1000
    ],
    'http_config' => [
        'base_uri' => 'https://jsonplaceholder.typicode.com/',
        'headers' => [

        ],
        'common' => [
            'query' => [

            ],
            'params' => [

            ],
            'prefix' => '',
            'version' => '',
        ],
    ],
];
