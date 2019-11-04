<?php

use App\Waypoint\Models\Role;

Route::group(
    [
        'prefix' => Role::WAYPOINT_ROOT_ROLE,
    ],
    function ()
    {
        Route::get(
            '/flushAllCache',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.ArtisanController.flushAllCache',
                'uses' => 'ArtisanController@flushAllCache',
            ]
        );

        Route::get(
            '/flushNonSessionCache',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.ArtisanController.flushAllNonSessionCache',
                'uses' => 'ArtisanController@flushAllNonSessionCache',
            ]
        );

        Route::get(
            '/clients/{client_id}/triggerGroupCalc',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.ArtisanController.triggerGroupCalc',
                'uses' => 'ArtisanController@triggerGroupCalc',
            ]
        );

        Route::get(
            '/clients/{client_id}/blockGroupCalc',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.ArtisanController.blockGroupCalc',
                'uses' => 'ArtisanController@blockGroupCalc',
            ]
        );

        Route::get(
            '/clients/{client_id}/setFilterDefaultValue/dropdown_name/{dropdown_name}/dropdown_value/{dropdown_value}',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.ArtisanController.setFilterDefaultValue',
                'uses' => 'ArtisanController@setFilterDefaultValue',
            ]
        );

        Route::get(
            '/clients/{client_id}/filter_alter/filter_name/{filter_name}/filter_options/{filter_options}',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.ArtisanController.filter_alter',
                'uses' => 'ArtisanController@filter_alter',
            ]
        );

        Route::get(
            '/clients/{client_id}/setClientConfigValue/config_name/{config_name}/config_value/{config_value}',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.ArtisanController.setClientConfigValue',
                'uses' => 'ArtisanController@setClientConfigValue',
            ]
        );

        Route::get(
            '/clients/{client_id}/refreshGeneratedListsAndGroups',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.ArtisanController.refreshGeneratedListsAndGroups',
                'uses' => 'ArtisanController@refreshGeneratedListsAndGroups',
            ]
        );
    }
);