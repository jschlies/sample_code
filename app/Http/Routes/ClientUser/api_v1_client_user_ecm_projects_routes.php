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
 * Here is we place the routes used by Role::WAYPOINT_ROOT_ROLE's and Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE's and Role::WAYPOINT_ASSOCIATE_ROLE's,
 * Role::CLIENT_ADMINISTRATIVE_USER_ROLE's and Role::CLIENT_GENERIC_USER_ROLL's. Note they are
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
            '/ecmProjects/available/ProjectCategories',
            [
                'as'   => 'api.v1.EcmProjectPublicController.getAvailableProjectCategories',
                'uses' => 'EcmProjectPublicController@getAvailableProjectCategories',
            ]
        );
        Route::get(
            '/ecmProjects/available/ProjectStatuses',
            [
                'as'   => 'api.v1.EcmProjectPublicController.getAvailableProjectStatuses',
                'uses' => 'EcmProjectPublicController@getAvailableProjectStatuses',
            ]
        );
        Route::get(
            '/ecmProjects/available/EnergyUnits',
            [
                'as'   => 'api.v1.EcmProjectPublicController.getAvailableEnergyUnits',
                'uses' => 'EcmProjectPublicController@getAvailableEnergyUnits',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/ecmProjects/{ecm_project_id}',
            [
                'as'   => 'api.v1.EcmProjectPublicController.show',
                'uses' => 'EcmProjectPublicController@show',
            ]
        );
        Route::post(
            '/clients/{client_id}/properties/{property_id}/ecmProjects',
            [
                'as'   => 'api.v1.EcmProjectPublicController.store',
                'uses' => 'EcmProjectPublicController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/properties/{property_id}/ecmProjects/{ecm_project_id}',
            [
                'as'   => 'api.v1.EcmProjectPublicController.update',
                'uses' => 'EcmProjectPublicController@update',
            ]
        );
        Route::delete(
            '/clients/{client_id}/properties/{property_id}/ecmProjects/{ecm_project_id}',
            [
                'as'   => 'api.v1.EcmProjectPublicController.destroy',
                'uses' => 'EcmProjectPublicController@destroy',
            ]
        );
        Route::get(
            '/clients/{client_id}/ecmProjects/{ecm_projects_id_arr?}',
            [
                'as'   => 'api.v1.EcmProjectPublicController.indexForClient',
                'uses' => 'EcmProjectPublicController@indexForClient',
            ]
        )->where(['ecm_projects_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);

        Route::get(
            '/clients/{client_id}/ecmProjects/download',
            [
                'as'   => 'api.v1.EcmProjectPublicController.indexForClient',
                'uses' => 'EcmProjectPublicController@indexForClient',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id_arr?}/ecmProjects',
            [
                'as'   => 'api.v1.EcmProjectPublicController.indexForProperty',
                'uses' => 'EcmProjectPublicController@indexForProperty',
            ]
        )->where(['property_id_arr' => '^\d+(,+\d+)*$']);
        Route::get(
            '/clients/{client_id}/properties/{property_id_arr?}/ecmProjects/download',
            [
                'as'   => 'api.v1.EcmProjectPublicController.indexForProperty',
                'uses' => 'EcmProjectPublicController@indexForProperty',
            ]
        )->where(['ecm_projects_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
        Route::get(
            '/clients/{client_id}/propertyGroups/{property_group_id_arr?}/ecmProjects',
            [
                'as'   => 'api.v1.EcmProjectPublicController.indexForPropertyGroup',
                'uses' => 'EcmProjectPublicController@indexForPropertyGroup',
            ]
        )->where(['ecm_projects_id_arr' => ApiController::REGEX_ARRAY_OF_INTEGERS]);
    }
);
