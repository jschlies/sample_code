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
                    Role::CLIENT_GENERIC_USER_ROLE,
                ]
            ),
        ],
        'prefix'     => Role::CLIENT_GENERIC_USER_ROLE,
    ],
    function ()
    {
        Route::post(
            'clients/{client_id}/relatedUserTypes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RelatedUserTypePublicDeprecatedController.store',
                'uses' => 'RelatedUserTypePublicDeprecatedController@store',
            ]
        );
        Route::get(
            'clients/{client_id}/relatedUserTypes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RelatedUserTypePublicDeprecatedController.index',
                'uses' => 'RelatedUserTypePublicDeprecatedController@index',
            ]
        );
        Route::delete(
            'clients/{client_id}/relatedUserTypes/{related_user_type_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RelatedUserTypePublicDeprecatedController.destroy',
                'uses' => 'RelatedUserTypePublicDeprecatedController@destroy',
            ]
        );

        /**
         * boutique relatedUserTypes Property routes
         */
        Route::post(
            'clients/{client_id}/relatedUserTypes/properties',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RelatedUserTypePublicDeprecatedController.storeProperty',
                'uses' => 'RelatedUserTypePublicDeprecatedController@storeProperty',
            ]
        );
        Route::get(
            'clients/{client_id}/relatedUserTypes/properties',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RelatedUserTypePublicDeprecatedController.indexProperty',
                'uses' => 'RelatedUserTypePublicDeprecatedController@indexProperty',
            ]
        );
        /**
         * boutique relatedUserTypes Opportunity routes
         */
        Route::post(
            'clients/{client_id}/relatedUserTypes/opportunities',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RelatedUserTypePublicDeprecatedController.storeOpportunity',
                'uses' => 'RelatedUserTypePublicDeprecatedController@storeOpportunity',
            ]
        );
        Route::get(
            'clients/{client_id}/properties/relatedUserTypes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PropertyDetailDeprecatedController.showRelatedUserTypes',
                'uses' => 'PropertyDetailDeprecatedController@showRelatedUserTypes',
            ]
        );

        Route::get(
            'clients/{client_id}/opportunities/relatedUserTypes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OpportunityDeprecatedController.showRelatedUserTypes',
                'uses' => 'OpportunityDeprecatedController@showRelatedUserTypes',
            ]
        );
        Route::get(
            'clients/{client_id}/advancedVariences/relatedUserTypes',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.AdvancedVariencesDetailDeprecatedController.showRelatedUserTypes',
                'uses' => 'PropertyDetailDeprecatedController@showRelatedUserTypes',
            ]
        );
        Route::get(
            'clients/{client_id}/relatedUserTypes/opportunities',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OpportunityDeprecatedController.showRelatedUserTypes',
                'uses' => 'OpportunityDeprecatedController@showRelatedUserTypes',
            ]
        );

        /**
         * generic relatedUsers routes
         */
        Route::post(
            'clients/{client_id}/relatedUsers',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RelatedUserPublicDeprecatedController.store',
                'uses' => 'RelatedUserPublicDeprecatedController@store',
            ]
        );
        Route::get(
            'clients/{client_id}/relatedUsers',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RelatedUserPublicDeprecatedController.index',
                'uses' => 'RelatedUserPublicDeprecatedController@index',
            ]
        );
        Route::delete(
            'clients/{client_id}/relatedUsers/{related_user_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RelatedUserPublicDeprecatedController.destroy',
                'uses' => 'RelatedUserPublicDeprecatedController@destroy',
            ]
        );

        /**
         * boutique relatedUser routes
         */
        Route::get(
            'clients/{client_id}/users/{user_id}/relatedUsers',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RelatedUserPublicDeprecatedController.indexRelatedUsersForUser',
                'uses' => 'RelatedUserPublicDeprecatedController@indexRelatedUsersForUser',
            ]
        );
        Route::get(
            'clients/{client_id}/properties/{property_id}/relatedUsers',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RelatedUserPublicDeprecatedController.indexRelatedUsersForProperty',
                'uses' => 'RelatedUserPublicDeprecatedController@indexRelatedUsersForProperty',
            ]
        );
        Route::get(
            'clients/{client_id}/users/{user_id}/relatedUsers/opportunities',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RelatedUserPublicDeprecatedController.indexOpportunities',
                'uses' => 'RelatedUserPublicDeprecatedController@indexOpportunities',
            ]
        );
        Route::get(
            'clients/{client_id}/users/{user_id}/relatedUsers/advancedVariances',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.RelatedUserPublicDeprecatedController.indexAdvancedVariances',
                'uses' => 'RelatedUserPublicDeprecatedController@indexAdvancedVariances',
            ]
        );
    }
);