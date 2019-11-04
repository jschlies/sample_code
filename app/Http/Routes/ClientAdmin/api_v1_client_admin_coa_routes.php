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
    ],
    function ()
    {
        /**
         * nativeCoas routes
         */
        Route::get(
            '/clients/{client_id}/nativeCoas',
            [
                'as'   => 'api.v1.NativeCoaController.index',
                'uses' => 'NativeCoaController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/nativeCoas/{native_coa_id}',
            [
                'as'   => 'api.v1.NativeCoaController.show',
                'uses' => 'NativeCoaController@show',
            ]
        );
        Route::post(
            '/clients/{client_id}/nativeCoas',
            [
                'as'   => 'api.v1.NativeCoaController.store',
                'uses' => 'NativeCoaController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/nativeCoas/{native_coa_id}',
            [
                'as'   => 'api.v1.NativeCoaController.update',
                'uses' => 'NativeCoaController@update',
            ]
        );
        Route::delete(
            '/clients/{client_id}/nativeCoas/{native_coa_id}',
            [
                'as'   => 'api.v1.NativeCoaController.destroy',
                'uses' => 'NativeCoaController@destroy',
            ]
        );

        /**
         * nativeAccounts routes
         */
        Route::get(
            '/clients/{client_id}/nativeCoas/{native_coa_id}/nativeAccounts',
            [
                'as'   => 'api.v1.NativeAccountController.index',
                'uses' => 'NativeAccountController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/nativeCoas/{native_coa_id}/nativeAccounts/{native_account_id}',
            [
                'as'   => 'api.v1.NativeAccountController.show',
                'uses' => 'NativeAccountController@show',
            ]
        );
        Route::post(
            '/clients/{client_id}/nativeCoas/{native_coa_id}/nativeAccounts',
            [
                'as'   => 'api.v1.NativeAccountController.store',
                'uses' => 'NativeAccountController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/nativeCoas/{native_coa_id}/nativeAccounts/{native_account_id}',
            [
                'as'   => 'api.v1.NativeAccountController.update',
                'uses' => 'NativeAccountController@update',
            ]
        );
        Route::delete(
            '/clients/{client_id}/nativeCoas/{native_coa_id}/nativeAccounts/{native_account_id}',
            [
                'as'   => 'api.v1.NativeAccountController.destroy',
                'uses' => 'NativeAccountController@destroy',
            ]
        );

        /**
         * nativeAccountTypes routes
         */
        Route::get(
            '/clients/{client_id}/nativeAccountTypes',
            [
                'as'   => 'api.v1.NativeAccountTypeController.index',
                'uses' => 'NativeAccountTypeController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/nativeAccountTypeDetails',
            [
                'as'   => 'api.v1.NativeAccountTypeController.indexDetail',
                'uses' => 'NativeAccountTypeController@indexDetail',
            ]
        );
        Route::get(
            '/clients/{client_id}/nativeAccountTypes/{native_account_type_id}',
            [
                'as'   => 'api.v1.NativeAccountTypeController.show',
                'uses' => 'NativeAccountTypeController@show',
            ]
        );
        Route::get(
            '/clients/{client_id}/nativeAccountTypeDetails/{native_account_type_id}',
            [
                'as'   => 'api.v1.NativeAccountTypeController.showDetail',
                'uses' => 'NativeAccountTypeController@showDetail',
            ]
        );
        Route::post(
            '/clients/{client_id}/nativeAccountTypes',
            [
                'as'   => 'api.v1.NativeAccountTypeController.store',
                'uses' => 'NativeAccountTypeController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/nativeAccountTypes/{native_account_type_id}',
            [
                'as'   => 'api.v1.NativeAccountTypeController.update',
                'uses' => 'NativeAccountTypeController@update',
            ]
        );
        Route::delete(
            '/clients/{client_id}/nativeAccountTypes/{native_account_type_id}',
            [
                'as'   => 'api.v1.NativeAccountTypeController.destroy',
                'uses' => 'NativeAccountTypeController@destroy',
            ]
        );

        /**
         * nativeAccountTypeTrailers
         */
        Route::get(
            '/clients/{client_id}/nativeAccountTypes/{native_account_type_id}/nativeAccountTypeTrailers',
            [
                'as'   => 'api.v1.NativeAccountTypeTrailerController.index',
                'uses' => 'NativeAccountTypeTrailerController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/nativeAccountTypes/{native_account_type_id}/nativeAccountTypeTrailers/{native_account_type_trailer_id}',
            [
                'as'   => 'api.v1.NativeAccountTypeTrailerController.show',
                'uses' => 'NativeAccountTypeTrailerController@show',
            ]
        );
        Route::post(
            '/clients/{client_id}/nativeAccountTypes/{native_account_type_id}/nativeAccountTypeTrailers',
            [
                'as'   => 'api.v1.NativeAccountTypeTrailerController.store',
                'uses' => 'NativeAccountTypeTrailerController@store',
            ]
        );
        Route::put(
            '/clients/{client_id}/nativeAccountTypes/{native_account_type_id}/nativeAccountTypeTrailers/{native_account_type_trailer_id}',
            [
                'as'   => 'api.v1.NativeAccountTypeTrailerController.update',
                'uses' => 'NativeAccountTypeTrailerController@update',
            ]
        );
        Route::delete(
            '/clients/{client_id}/nativeAccountTypes/{native_account_type_id}/nativeAccountTypeTrailers/{native_account_type_trailer_id}',
            [
                'as'   => 'api.v1.NativeAccountTypeTrailerController.destroy',
                'uses' => 'NativeAccountTypeTrailerController@destroy',
            ]
        );

        /**
         * propertyNativeCoas
         */
        Route::get(
            '/clients/{client_id}/properties/{property_id}/propertyNativeCoas',
            [
                'as'   => 'api.v1.PropertyNativeCoaController.index',
                'uses' => 'PropertyNativeCoaController@index',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/propertyNativeCoas/{property_native_coa_id}',
            [
                'as'   => 'api.v1.PropertyNativeCoaController.show',
                'uses' => 'PropertyNativeCoaController@show',
            ]
        );
        Route::post(
            '/clients/{client_id}/properties/{property_id}/propertyNativeCoas',
            [
                'as'   => 'api.v1.PropertyNativeCoaController.store',
                'uses' => 'PropertyNativeCoaController@store',
            ]
        );
        Route::delete(
            '/clients/{client_id}/properties/{property_id}/propertyNativeCoas/{property_native_coa_id}',
            [
                'as'   => 'api.v1.PropertyNativeCoaController.destroy',
                'uses' => 'PropertyNativeCoaController@destroy',
            ]
        );

        Route::post(
            '/clients/{client_id}/advancedVarianceExplanationTypes',
            [
                'as'   => 'api.v1.AdvancedVarianceExplanationTypeController.store',
                'uses' => 'AdvancedVarianceExplanationTypeController@store',
            ]
        );
    }
);