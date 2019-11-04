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
                    Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
                    Role::CLIENT_GENERIC_USER_ROLE,
                ]
            ),
        ],
    ],
    function ()
    {
        Route::get(
            '/clients/{client_id}/properties/{property_id}/advancedVariance/{advanced_variance_id}/report',
            [
                'as'   => 'api.v1.AdvancedVarianceReportController.index',
                'uses' => 'AdvancedVarianceReportController@index',
            ]
        );
    }
);