<?php

Route::get(
    '/clients/{client_id}/apikey/login',
    [
        'as'   => 'api.v1.Auth0Controller.login_via_apiKey',
        'uses' => 'Auth0Controller@login_via_apiKey',
    ]
);