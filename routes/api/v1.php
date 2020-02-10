<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [], function ($api) {
    $api->group([
        'namespace' => 'Api\User\V1\Controllers',
        'prefix' => '',
    ], function ($api) {
        $api->post('login', 'AuthorizationsController@login');
    });
});

