<?php

use App\Waypoint\Models\Role;

Route::group(
    [
        'middleware' => [
            'role:' . implode(
                '|',
                [
                    Role::WAYPOINT_ROOT_ROLE,
                    Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE,
                    Role::WAYPOINT_ASSOCIATE_ROLE,
                ]
            ),
        ],
    ],
    function ()
    {
        Route::put(
            '/clients/{client_id}/updateClientSettings',
            [
                'as'   => 'api.v1.ClientDetailController.updateClientConfig',
                'uses' => 'ClientDetailController@updateClientConfig',
            ]
        );
    }
);
