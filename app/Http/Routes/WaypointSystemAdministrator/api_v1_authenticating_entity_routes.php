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
                ]
            ),
        ],
    ],
    function ()
    {
        Route::get(
            '/admin/authenticatingEntities',
            [
                'as'   => 'api.v1.AuthenticatingEntityController.index',
                'uses' => 'AuthenticatingEntityController@index',
            ]
        );
        Route::post(
            '/admin/authenticatingEntities',
            [
                'as'   => 'api.v1.AuthenticatingEntityController.store',
                'uses' => 'AuthenticatingEntityController@store',
            ]
        );
        Route::delete(
            '/admin/authenticatingEntities/{authenticating_entity_id}',
            [
                'as'   => 'api.v1.AuthenticatingEntityController.destroy',
                'uses' => 'AuthenticatingEntityController@destroy',
            ]
        );
    }
);
