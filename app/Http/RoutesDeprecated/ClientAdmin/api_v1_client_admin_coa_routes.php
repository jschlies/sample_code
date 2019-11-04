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
 * Here is we place the routes used by Role::WAYPOINT_ROOT_ROLE's. Note they are
 * prefix'ed so these routes cannot be 'reused' (with another or no prefix) elsewhere. Note that in Lavarel,
 * a particular cannot be defined twice. Looks like the second declaration wins.
 *
 * No route::resource() anyplace other than here and app/Http/Routes/api_v1_admin_routes.php (ie Root routes)
 */

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
                ]
            ),
        ],
        'prefix'     => Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
    ],
    function ()
    {

        /**
         * nativeCoas routes
         */
        Route::get(
            'clients/{client_id}/nativeCoas',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeCoaDeprecatedController.index',
                'uses' => 'NativeCoaDeprecatedController@index',
            ]
        );
        Route::get(
            'clients/{client_id}/nativeCoas/{native_coa_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeCoaDeprecatedController.show',
                'uses' => 'NativeCoaDeprecatedController@show',
            ]
        );
        Route::post(
            'clients/{client_id}/nativeCoas',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeCoaDeprecatedController.store',
                'uses' => 'NativeCoaDeprecatedController@store',
            ]
        );
        Route::put(
            'clients/{client_id}/nativeCoas/{native_coa_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeCoaDeprecatedController.update',
                'uses' => 'NativeCoaDeprecatedController@update',
            ]
        );
        Route::delete(
            'clients/{client_id}/nativeCoas/{native_coa_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeCoaDeprecatedController.destroy',
                'uses' => 'NativeCoaDeprecatedController@destroy',
            ]
        );

        /**
         * nativeAccounts routes
         */
        Route::get(
            'clients/{client_id}/nativeCoas/{native_coa_id}/nativeAccounts',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountDeprecatedController.index',
                'uses' => 'NativeAccountDeprecatedController@index',
            ]
        );
        Route::get(
            'clients/{client_id}/nativeCoas/{native_coa_id}/nativeAccounts/{native_account_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountDeprecatedController.show',
                'uses' => 'NativeAccountDeprecatedController@show',
            ]
        );
        Route::post(
            'clients/{client_id}/nativeCoas/{native_coa_id}/nativeAccounts',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountDeprecatedController.store',
                'uses' => 'NativeAccountDeprecatedController@store',
            ]
        );
        Route::put(
            'clients/{client_id}/nativeCoas/{native_coa_id}/nativeAccounts/{native_account_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountDeprecatedController.update',
                'uses' => 'NativeAccountDeprecatedController@update',
            ]
        );
        Route::delete(
            'clients/{client_id}/nativeCoas/{native_coa_id}/nativeAccounts/{native_account_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountDeprecatedController.destroy',
                'uses' => 'NativeAccountDeprecatedController@destroy',
            ]
        );

        /**
         * nativeAccountTypes routes
         */
        Route::get(
            'clients/{client_id}/nativeAccountTypes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountTypeDeprecatedController.index',
                'uses' => 'NativeAccountTypeDeprecatedController@index',
            ]
        );
        Route::get(
            'clients/{client_id}/nativeAccountTypeDetails',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountTypeDeprecatedController.indexDetail',
                'uses' => 'NativeAccountTypeDeprecatedController@indexDetail',
            ]
        );
        Route::get(
            'clients/{client_id}/nativeAccountTypes/{native_account_type_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountTypeDeprecatedController.show',
                'uses' => 'NativeAccountTypeDeprecatedController@show',
            ]
        );
        Route::get(
            'clients/{client_id}/nativeAccountTypeDetails/{native_account_type_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountTypeDeprecatedController.showDetail',
                'uses' => 'NativeAccountTypeDeprecatedController@showDetail',
            ]
        );
        Route::post(
            'clients/{client_id}/nativeAccountTypes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountTypeDeprecatedController.store',
                'uses' => 'NativeAccountTypeDeprecatedController@store',
            ]
        );
        Route::put(
            'clients/{client_id}/nativeAccountTypes/{native_account_type_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountTypeDeprecatedController.update',
                'uses' => 'NativeAccountTypeDeprecatedController@update',
            ]
        );
        Route::delete(
            'clients/{client_id}/nativeAccountTypes/{native_account_type_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountTypeDeprecatedController.destroy',
                'uses' => 'NativeAccountTypeDeprecatedController@destroy',
            ]
        );

        /**
         * nativeAccountTypeTrailers
         */
        Route::get(
            'clients/{client_id}/nativeAccountTypes/{native_account_type_id}/nativeAccountTypeTrailers',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountTypeTrailerDeprecatedController.index',
                'uses' => 'NativeAccountTypeTrailerDeprecatedController@index',
            ]
        );
        Route::get(
            'clients/{client_id}/nativeAccountTypes/{native_account_type_id}/nativeAccountTypeTrailers/{native_account_type_trailer_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountTypeTrailerDeprecatedController.show',
                'uses' => 'NativeAccountTypeTrailerDeprecatedController@show',
            ]
        );
        Route::post(
            'clients/{client_id}/nativeAccountTypes/{native_account_type_id}/nativeAccountTypeTrailers',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountTypeTrailerDeprecatedController.store',
                'uses' => 'NativeAccountTypeTrailerDeprecatedController@store',
            ]
        );
        Route::put(
            'clients/{client_id}/nativeAccountTypes/{native_account_type_id}/nativeAccountTypeTrailers/{native_account_type_trailer_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountTypeTrailerDeprecatedController.update',
                'uses' => 'NativeAccountTypeTrailerDeprecatedController@update',
            ]
        );
        Route::delete(
            'clients/{client_id}/nativeAccountTypes/{native_account_type_id}/nativeAccountTypeTrailers/{native_account_type_trailer_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.NativeAccountTypeTrailerDeprecatedController.destroy',
                'uses' => 'NativeAccountTypeTrailerDeprecatedController@destroy',
            ]
        );

        /**
         * propertyNativeCoas
         */
        Route::get(
            'clients/{client_id}/properties/{property_id}/propertyNativeCoas',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.PropertyNativeCoaDeprecatedController.index',
                'uses' => 'PropertyNativeCoaDeprecatedController@index',
            ]
        );
        Route::get(
            'clients/{client_id}/properties/{property_id}/propertyNativeCoas/{property_native_coa_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.PropertyNativeCoaDeprecatedController.show',
                'uses' => 'PropertyNativeCoaDeprecatedController@show',
            ]
        );
        Route::post(
            'clients/{client_id}/properties/{property_id}/propertyNativeCoas',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.PropertyNativeCoaDeprecatedController.store',
                'uses' => 'PropertyNativeCoaDeprecatedController@store',
            ]
        );
        Route::delete(
            'clients/{client_id}/properties/{property_id}/propertyNativeCoas/{property_native_coa_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.PropertyNativeCoaDeprecatedController.destroy',
                'uses' => 'PropertyNativeCoaDeprecatedController@destroy',
            ]
        );

        Route::post(
            'clients/{client_id}/advancedVarianceExplanationTypes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '.AdvancedVarianceExplanationTypeDeprecatedController.store',
                'uses' => 'AdvancedVarianceExplanationTypeDeprecatedController@store',
            ]
        );
    }
);