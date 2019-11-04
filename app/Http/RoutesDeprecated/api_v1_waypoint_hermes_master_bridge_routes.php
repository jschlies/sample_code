<?php

use App\Waypoint\Models\Role;

Route::group(
    [
        'prefix' => Role::WAYPOINT_ROOT_ROLE,
    ],
    function ()
    {
        /**
         * waypoint_master:CLIENT_DETAIL
         */
        Route::get(
            '/clients',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_client_detail',
                'uses' => 'WaypointMasterBridgeController@index_client_detail',
            ]
        );
        /**
         * waypoint_master:PROPERTY_DETAILS
         */
        Route::get(
            '/clients/{client_id_old}/properties',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_property_details',
                'uses' => 'WaypointMasterBridgeController@index_property_details',
            ]
        );
        /**
         * waypoint_master:PROPERTY_CODE_MAPPING
         */
        Route::get(
            '/clients/{client_id_old}/property_code_mapping',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_property_code_mapping',
                'uses' => 'WaypointMasterBridgeController@index_property_code_mapping',
            ]
        );
        /**
         * waypoint_master:BOMA_COA_CODES
         */
        Route::get(
            '/clients/boma_coa_codes',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_boma_coa_codes',
                'uses' => 'WaypointMasterBridgeController@index_boma_coa_codes',
            ]
        );
        /**
         * waypoint_master:WAYPOINT_BOMA_COA_MAPPING
         */
        Route::get(
            '/clients/{client_id_old}/waypoint_boma_coa_mapping',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_waypoint_boma_coa_mapping',
                'uses' => 'WaypointMasterBridgeController@index_waypoint_boma_coa_mapping',
            ]
        );
        /**
         * waypoint_master:CLIENT_SFTP_DETAILS
         */
        Route::get(
            '/clients/client_sftp_details',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_client_sftp_details',
                'uses' => 'WaypointMasterBridgeController@index_client_sftp_details',
            ]
        );
        /**
         * waypoint_master:WP_ASSET_TYPE
         */
        Route::get(
            '/clients/wp_asset_type',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_wp_asset_type',
                'uses' => 'WaypointMasterBridgeController@index_wp_asset_type',
            ]
        );
        /**
         * waypoint_staging:VERSION_METADATA
         */
        Route::get(
            '/clients/version_metadata',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_version_metadata',
                'uses' => 'WaypointMasterBridgeController@index_version_metadata',
            ]
        );
        /**
         * waypoint_staging:COLUMN_DATATYPES
         */
        Route::get(
            '/clients/column_datatypes',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_column_datatypes',
                'uses' => 'WaypointMasterBridgeController@index_column_datatypes',
            ]
        );

        /**
         * waypoint_staging:WAYPOINT_ACCOUNT_CODES
         */
        Route::get(
            '/clients/{client_id_old}/waypoint_account_codes',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_waypoint_account_codes',
                'uses' => 'WaypointMasterBridgeController@index_waypoint_account_codes',
            ]
        );

        /**
         * waypoint_staging:OCCUPANCY_LEASE_TYPE_D
         */
        Route::get(
            '/clients/occupancy_lease_type_d',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_occupancy_lease_type_d',
                'uses' => 'WaypointMasterBridgeController@index_occupancy_lease_type_d',
            ]
        );
        /**
         * waypoint_master:CLIENT_DETAIL
         */
        Route::get(
            '/clients/map',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_client_detail_map',
                'uses' => 'WaypointMasterBridgeController@index_client_detail_map',
            ]
        );
        /**
         * waypoint_master:CLIENT_reportTemplates
         */
        Route::get(
            '/clients/{client_id_old}/reportTemplates',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_report_templates',
                'uses' => 'WaypointMasterBridgeController@index_report_templates',
            ]
        );

        /**
         * waypoint_master:CLIENT_nativeCoas
         */
        Route::get(
            '/clients/{client_id_old}/reportTemplates/{report_template_id}/nativeCoas',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_report_template_coas',
                'uses' => 'WaypointMasterBridgeController@index_report_template_coas',
            ]
        );
        /**
         * waypoint_master:CLIENT_reportTemplates_DETAIL
         */
        Route::get(
            '/clients/{client_id_old}/reportTemplates/{report_template_id}/detail',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_report_template_coas_detail',
                'uses' => 'WaypointMasterBridgeController@index_report_template_coas_detail',
            ]
        );
        /**
         * waypoint_master:CLIENT_reportTemplates_DETAIL
         */
        Route::get(
            '/clients/{client_id_old}/reportTemplates/{report_template_id}/reportTemplateMappings',
            [
                'as'   => 'api.v1.' . Role::WAYPOINT_ROOT_ROLE . '.WaypointMasterBridgeController.index_report_template_cross_reference',
                'uses' => 'WaypointMasterBridgeController@index_report_template_cross_reference',
            ]
        );
    }
);