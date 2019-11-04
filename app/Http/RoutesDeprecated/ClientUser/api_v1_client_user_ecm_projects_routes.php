<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where all API routes are defined.
|
*/

/**
 * Here is we place the routes used by Role::WAYPOINT_ROOT_ROLE's and Role::CLIENT_ADMINISTRATIVE_USER_ROLE's. Note they are
 * prefix'ed so these routes cannot be 'reused' (with another or no prefix) elsewhere. Note that in Lavarel,
 * a particular cannot be defined twice. Looks like the second declaration wins.
 *
 * No route::resource() anyplace other than here
 */

use App\Waypoint\Http\ApiController;
use App\Waypoint\Models\Role;

Route::group(
    [
        'middleware' => [
            'role:' . implode(
                '|',
                [
                    Role::WAYPOINT_ROOT_ROLE,
                    Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
                    Role::CLIENT_GENERIC_USER_ROLE,
                ]
            ),
        ],
        'prefix'     => Role::CLIENT_GENERIC_USER_ROLE,
    ],
    function ()
    {
        ##########################################
        # ecmProjects
        ##########################################
        Route::get(
            'ecmProjects/available/ProjectCategories',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.EcmProjectPublicDeprecatedController.getAvailableProjectCategories',
                'uses' => 'EcmProjectPublicDeprecatedController@getAvailableProjectCategories',
            ]
        );
        Route::get(
            'ecmProjects/available/ProjectStatuses',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.EcmProjectPublicDeprecatedController.getAvailableProjectStatuses',
                'uses' => 'EcmProjectPublicDeprecatedController@getAvailableProjectStatuses',
            ]
        );
        Route::get(
            'ecmProjects/available/EnergyUnits',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.EcmProjectPublicDeprecatedController.getAvailableEnergyUnits',
                'uses' => 'EcmProjectPublicDeprecatedController@getAvailableEnergyUnits',
            ]
        );
        Route::get(
            'ecmProjects/{id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.EcmProjectPublicDeprecatedController.show',
                'uses' => 'EcmProjectPublicDeprecatedController@show',
            ]
        );
        Route::post(
            'ecmProjects',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.EcmProjectPublicDeprecatedController.store',
                'uses' => 'EcmProjectPublicDeprecatedController@store',
            ]
        );
        Route::put(
            'ecmProjects/{ecm_project_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.EcmProjectPublicDeprecatedController.update',
                'uses' => 'EcmProjectPublicDeprecatedController@update',
            ]
        );
        Route::delete(
            'ecmProjects/{ecm_project_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.EcmProjectPublicDeprecatedController.destroy',
                'uses' => 'EcmProjectPublicDeprecatedController@destroy',
            ]
        );
        Route::get(
            'clients/{client_id}/ecmProjects/{ecm_projects_id_arr?}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.EcmProjectPublicDeprecatedController.indexForClient',
                'uses' => 'EcmProjectPublicDeprecatedController@indexForClient',
            ]
        )->where(['ecm_projects_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::get(
            'clients/{client_id}/ecmProjects/download',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.EcmProjectPublicDeprecatedController.indexForClient',
                'uses' => 'EcmProjectPublicDeprecatedController@indexForClient',
            ]
        );
        Route::get(
            'clients/{client_id}/properties/{property_id_arr?}/ecmProjects',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.EcmProjectPublicDeprecatedController.indexForProperty',
                'uses' => 'EcmProjectPublicDeprecatedController@indexForProperty',
            ]
        )->where(['property_id_arr' => '^\d+(,+\d+)*$']);
        Route::get(
            'clients/{client_id}/properties/{property_id_arr?}/ecmProjects/download',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.EcmProjectPublicDeprecatedController.indexForProperty',
                'uses' => 'EcmProjectPublicDeprecatedController@indexForProperty',
            ]
        )->where(['ecm_projects_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id_arr?}/ecmProjects',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.EcmProjectPublicDeprecatedController.indexForPropertyGroup',
                'uses' => 'EcmProjectPublicDeprecatedController@indexForPropertyGroup',
            ]
        )->where(['ecm_projects_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
    }
);
