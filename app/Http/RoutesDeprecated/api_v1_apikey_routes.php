<?php

use App\Waypoint\Models\Role;

Route::group(
    [
        'prefix'    => Role::WAYPOINT_ROOT_ROLE,
        'namespace' => 'Api',
    ],
    function ()
    {
        /**
         * AdvancedVariance
         */
        Route::post(
            'clients/{client_id}/properties/{property_id}/advancedVariances/',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.AdvancedVarianceDetailApiKeyController.store',
                'uses' => 'AdvancedVarianceDetailApiKeyController@store',
            ]
        );
    }
);

Route::group(
    [
        'prefix'    => Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
        'namespace' => 'Api',
    ],
    function ()
    {
        /**
         * AdvancedVariance
         */
        Route::post(
            'clients/{client_id}/properties/{property_id}/advancedVariances/',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AdvancedVarianceDetailApiKeyController.store',
                'uses' => 'AdvancedVarianceDetailApiKeyController@store',
            ]
        );
    }
);