<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [], function ($api) {
    $api->group([
        'namespace' => 'Api\User\V1\Controllers',
        'prefix' => 'users',
    ], function ($api) {
        $api->post('login', 'AuthorizationsController@login');
    });
    $api->group([
        'namespace' => 'Api\Auth\V1\Controllers',
        'prefix' => 'authorizations',
    ], function ($api) {
        $api->post('register', 'RegisterController@register');
        $api->post('login', 'AuthorizationsController@login');
    });
});
