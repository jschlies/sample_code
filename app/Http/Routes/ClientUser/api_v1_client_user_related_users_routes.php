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
    ],
    function ()
    {
        /**
         * generic relatedUserTypes routes
         */
        Route::post(
            '/clients/{client_id}/relatedUserTypes',
            [
                'as'   => 'api.v1.RelatedUserTypePublicController.store',
                'uses' => 'RelatedUserTypePublicController@store',
            ]
        );
        Route::get(
            '/clients/{client_id}/relatedUserTypes',
            [
                'as'   => 'api.v1.RelatedUserTypePublicController.index',
                'uses' => 'RelatedUserTypePublicController@index',
            ]
        );
        Route::delete(
            '/clients/{client_id}/relatedUserTypes/{related_user_type_id}',
            [
                'as'   => 'api.v1.RelatedUserTypePublicController.destroy',
                'uses' => 'RelatedUserTypePublicController@destroy',
            ]
        );

        /**
         * boutique relatedUserTypes Property routes
         */
        Route::post(
            '/clients/{client_id}/relatedUserTypes/properties',
            [
                'as'   => 'api.v1.RelatedUserTypePublicController.storeProperty',
                'uses' => 'RelatedUserTypePublicController@storeProperty',
            ]
        );
        Route::get(
            '/clients/{client_id}/relatedUserTypes/properties',
            [
                'as'   => 'api.v1.RelatedUserTypePublicController.indexProperty',
                'uses' => 'RelatedUserTypePublicController@indexProperty',
            ]
        );
        /**
         * boutique relatedUserTypes Opportunity routes
         */
        Route::post(
            '/clients/{client_id}/relatedUserTypes/opportunities',
            [
                'as'   => 'api.v1.RelatedUserTypePublicController.storeOpportunity',
                'uses' => 'RelatedUserTypePublicController@storeOpportunity',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/relatedUserTypes',
            [
                'as'   => 'api.v1.PropertyDetailController.showRelatedUserTypes',
                'uses' => 'PropertyDetailController@showRelatedUserTypes',
            ]
        );

        Route::get(
            '/clients/{client_id}/opportunities/relatedUserTypes',
            [
                'as'   => 'api.v1.OpportunityController.showRelatedUserTypes',
                'uses' => 'OpportunityController@showRelatedUserTypes',
            ]
        );
        Route::get(
            '/clients/{client_id}/advancedVariences/relatedUserTypes',
            [
                'as'   => 'api.v1.AdvancedVariencesDetailController.showRelatedUserTypes',
                'uses' => 'PropertyDetailController@showRelatedUserTypes',
            ]
        );
        Route::get(
            '/clients/{client_id}/relatedUserTypes/opportunities',
            [
                'as'   => 'api.v1.OpportunityController.showRelatedUserTypes',
                'uses' => 'OpportunityController@showRelatedUserTypes',
            ]
        );

        /**
         * generic relatedUsers routes
         */
        Route::post(
            '/clients/{client_id}/relatedUsers',
            [
                'as'   => 'api.v1.RelatedUserPublicController.store',
                'uses' => 'RelatedUserPublicController@store',
            ]
        );
        Route::get(
            '/clients/{client_id}/relatedUsers',
            [
                'as'   => 'api.v1.RelatedUserPublicController.index',
                'uses' => 'RelatedUserPublicController@index',
            ]
        );
        Route::delete(
            '/clients/{client_id}/relatedUsers/{related_user_id}',
            [
                'as'   => 'api.v1.RelatedUserPublicController.destroy',
                'uses' => 'RelatedUserPublicController@destroy',
            ]
        );

        /**
         * boutique relatedUser routes
         */
        Route::get(
            '/clients/{client_id}/users/{user_id}/relatedUsers',
            [
                'as'   => 'api.v1.RelatedUserPublicController.indexRelatedUsersForUser',
                'uses' => 'RelatedUserPublicController@indexRelatedUsersForUser',
            ]
        );
        Route::get(
            '/clients/{client_id}/properties/{property_id}/relatedUsers',
            [
                'as'   => 'api.v1.RelatedUserPublicController.indexRelatedUsersForProperty',
                'uses' => 'RelatedUserPublicController@indexRelatedUsersForProperty',
            ]
        );
        Route::get(
            '/clients/{client_id}/users/{user_id}/relatedUsers/opportunities',
            [
                'as'   => 'api.v1.RelatedUserPublicController.indexOpportunities',
                'uses' => 'RelatedUserPublicController@indexOpportunities',
            ]
        );
        Route::get(
            '/clients/{client_id}/users/{user_id}/relatedUsers/advancedVariances',
            [
                'as'   => 'api.v1.RelatedUserPublicController.indexAdvancedVariances',
                'uses' => 'RelatedUserPublicController@indexAdvancedVariances',
            ]
        );
    }
);