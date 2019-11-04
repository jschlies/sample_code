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
        'prefix'     => Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE,
    ],
    function ()
    {
        Route::get(
            '/authenticatingEntities',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE . '.AuthenticatingEntityController.index',
                'uses' => 'AuthenticatingEntityController@index',
            ]
        );
        Route::post(
            '/authenticatingEntities',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE . '.AuthenticatingEntityController.store',
                'uses' => 'AuthenticatingEntityController@store',
            ]
        );
        Route::delete(
            '/authenticatingEntities/{authenticating_entity_id}',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE . '.AdvancedVarianceDetailController.destroyReviewer',
                'uses' => 'AdvancedVarianceDetailController@destroyReviewer',
            ]
        );
    }
);
