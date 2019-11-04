<?php

/**
 * Here is we place the routes used by Role::WAYPOINT_ROOT_ROLE's and Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE's and Role::WAYPOINT_ASSOCIATE_ROLE's,
 * Role::CLIENT_ADMINISTRATIVE_USER_ROLE's and Role::CLIENT_GENERIC_USER_ROLL's. Note they are
 * prefix'ed so these routes cannot be 'reused' (with another or no prefix) elsewhere. Note that in Lavarel,
 * a particular cannot be defined twice. Looks like the second declaration wins.
 *
 * No route::resource() anyplace other than here
 */

use App\Waypoint\Models\Role;

Route::group(
    [
        'middleware' => [
            'role:' . implode(
                '|', [
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
        /** Native Chart of Accounts Property Report */
        Route::get(
            'NativeCoaReport/property/{property_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.NativeCoaReportLedgerDeprecatedController.getPropertyReport',
                'uses' => 'NativeCoaReportLedgerDeprecatedController@getPropertyReport',
            ]
        );

        /** Native Chart of Accounts Group Report */
        Route::get(
            'NativeCoaReport/group/{group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.NativeCoaReportLedgerDeprecatedController.getGroupReport',
                'uses' => 'NativeCoaReportLedgerDeprecatedController@getPropertyGroupReport',
            ]
        );

        /** Peer Average Notes */
        Route::get(
            'PeerNotes/property/{property_id}/year/{year}/area/{area}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PeerNotesController.index',
                'uses' => 'PeerNotesDeprecatedController@index',
            ]
        );

        /** Overview Spreadsheet */
        Route::get(
            'OperatingExpensesCombinedSpreadsheet/{ledgerDataType}/{id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.Spreadsheet.createCombinedOperatingExpensesSpreadsheet',
                'uses' => 'SpreadsheetDeprecatedController@createCombinedOperatingExpensesSpreadsheet',
            ]
        );

        /** Variance Combined Spreadsheet */
        Route::get(
            'VarianceCombinedSpreadsheet/{ledgerDataType}/{id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.Spreadsheet.createCombinedVarianceSpreadsheet',
                'uses' => 'SpreadsheetDeprecatedController@createCombinedVarianceSpreadsheet',
            ]
        );

        /** Peer Average Combined Spreadsheet */
        Route::get(
            'PeerAverageCombinedSpreadsheet/{ledgerDataType}/{id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.Spreadsheet.createCombinedPeerAverageSpreadsheet',
                'uses' => 'SpreadsheetDeprecatedController@createCombinedPeerAverageSpreadsheet',
            ]
        );

        /** Peer Average Combined Spreadsheet */
        Route::get(
            'YearOverYearCombinedSpreadsheet/{ledgerDataType}/{id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.Spreadsheet.createCombinedYearOverYearSpreadsheet',
                'uses' => 'SpreadsheetDeprecatedController@createCombinedYearOverYearSpreadsheet',
            ]
        );

        /** Overview Spreadsheet */
        Route::get(
            'CombinedOverviewSpreadsheet/{ledgerDataType}/{id}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.Spreadsheet.createCombinedOverviewSpreadsheet',
                'uses' => 'SpreadsheetDeprecatedController@createCombinedOverviewSpreadsheet',
            ]
        );

        /** PeerAverageProperty */
        Route::get(
            'PeerAverageProperty/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PeerAveragePropertyController.index',
                'uses' => 'PeerAveragePropertyDeprecatedController@index',
            ]
        );

        /** PeerAveragePropertyGroup */
        Route::get(
            'PeerAveragePropertyGroup/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PeerAveragePropertyGroupController.index',
                'uses' => 'PeerAveragePropertyGroupDeprecatedController@index',
            ]
        );

        /** PeerAveragePropertyRanking */
        Route::get(
            'PeerAveragePropertyRanking/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PeerAveragePropertyRankingController.index',
                'uses' => 'PeerAveragePropertyRankingDeprecatedController@index',
            ]
        );

        /** PeerAveragePropertyGroupRanking */
        Route::get(
            'PeerAveragePropertyGroupRanking/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.PeerAveragePropertyGroupRankingController.index',
                'uses' => 'PeerAveragePropertyGroupRankingDeprecatedController@index',
            ]
        );

        /** OccupancyProperty */
        Route::get(
            'OccupancyProperty/property/{property_id}/year/{year}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OccupancyPropertyController.index',
                'uses' => 'OccupancyPropertyDeprecatedController@index',
            ]
        );

        /** OccupancyProperty */
        Route::get(
            'AsOfMonthOccupancy/property/{property_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OccupancyPropertyController.getAsOfMonthOccupancyProperty',
                'uses' => 'OccupancyPropertyDeprecatedController@getAsOfMonthOccupancyProperty',
            ]
        );

        /** OccupancyProperty */
        Route::get(
            'AsOfMonthOccupancy/group/{property_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OccupancyPropertyController.getAsOfMonthOccupancyPropertyGroup',
                'uses' => 'OccupancyPropertyDeprecatedController@getAsOfMonthOccupancyPropertyGroup',
            ]
        );

        /** OccupancyPropertyGroup */
        Route::get(
            'OccupancyPropertyGroup/group/{property_group_id}/year/{year}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OccupancyPropertyGroupController.index',
                'uses' => 'OccupancyPropertyGroupDeprecatedController@index',
            ]
        );

        /**
         * MonthlyOccupancy - property
         * Commented out per Jim
         */
        // Route::get(˚
        //     'clients/{client_id}/MonthlyOccupancy/property/{property_id}/year/{year}',
        //     [
        //         'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.MonthlyOccupancyDeprecatedController.getMonthlyOccupancyForProperty',
        //         'uses' => 'MonthlyOccupancyDeprecatedController@getMonthlyOccupancyForProperty',
        //     ]
        // );

        /**
         * MonthlyOccupancy - property group
         * Commented out per Jim
         */
        // Route::get(
        //     'clients/{client_id}/MonthlyOccupancy/group/{property_group_id}/year/{year}',
        //     [
        //         'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.MonthlyOccupancyDeprecatedController.getMonthlyOccupancyForPropertyGroup',
        //         'uses' => 'MonthlyOccupancyDeprecatedController@getMonthlyOccupancyForPropertyGroup',
        //     ]
        // );

        /** CompareProperty */
        Route::get(
            'CompareProperty/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ComparePropertyController.index',
                'uses' => 'ComparePropertyDeprecatedController@index',
            ]
        );

        /** ComparePropertyGroup */
        Route::get(
            'ComparePropertyGroup/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.ComparePropertyGroupController.index',
                'uses' => 'ComparePropertyGroupDeprecatedController@index',
            ]
        );

        /** OperatingExpensesProperty */
        Route::get(
            'OperatingExpensesProperty/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OperatingExpensesPropertyController.index',
                'uses' => 'OperatingExpensesPropertyDeprecatedController@index',
            ]
        );

        /** OperatingExpensesPropertyRanking */
        Route::get(
            'OperatingExpensesPropertyRanking/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OperatingExpensesPropertyRankingController.index',
                'uses' => 'OperatingExpensesPropertyRankingDeprecatedController@index',
            ]
        );

        /** OperatingExpensesPropertyGroup */
        Route::get(
            'OperatingExpensesPropertyGroup/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OperatingExpensesPropertyGroupController.index',
                'uses' => 'OperatingExpensesPropertyGroupDeprecatedController@index',
            ]
        );

        /** OperatingExpensesPropertyGroupRanking */
        Route::get(
            'OperatingExpensesPropertyGroupRanking/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.OperatingExpensesPropertyGroupRankingController.index',
                'uses' => 'OperatingExpensesPropertyGroupRankingDeprecatedController@index',
            ]
        );

        /** VarianceProperty */
        Route::get(
            'VarianceProperty/property/{property_id}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.VariancePropertyController.index',
                'uses' => 'VariancePropertyDeprecatedController@index',
            ]
        );

        /** VariancePropertyGroup */
        Route::get(
            'VariancePropertyGroup/group/{property_group_id}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.VariancePropertyGroupController.index',
                'uses' => 'VariancePropertyGroupDeprecatedController@index',
            ]
        );

        /** VariancePropertyRanking */
        Route::get(
            'VariancePropertyRanking/property/{property_id}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.VariancePropertyRankingController.index',
                'uses' => 'VariancePropertyRankingDeprecatedController@index',
            ]
        );

        /** VariancePropertyGroupRanking */
        Route::get(
            'VariancePropertyGroupRanking/group/{property_group_id}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.VariancePropertyGroupRankingController.index',
                'uses' => 'VariancePropertyGroupRankingDeprecatedController@index',
            ]
        );

        /** YearOverYearProperty */
        Route::get(
            'YearOverYearProperty/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.YearOverYearPropertyController.index',
                'uses' => 'YearOverYearPropertyDeprecatedController@index',
            ]
        );

        /** YearOverYearPropertyRanking */
        Route::get(
            'YearOverYearPropertyRanking/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.YearOverYearPropertyRankingController.index',
                'uses' => 'YearOverYearPropertyRankingDeprecatedController@index',
            ]
        );

        /** YearOverYearPropertyGroup */
        Route::get(
            'YearOverYearPropertyGroup/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.YearOverYearPropertyGroupController.index',
                'uses' => 'YearOverYearPropertyGroupDeprecatedController@index',
            ]
        );

        /** YearOverYearPropertyGroupRanking */
        Route::get(
            'YearOverYearPropertyGroupRanking/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.YearOverYearPropertyGroupRankingController.index',
                'uses' => 'YearOverYearPropertyGroupRankingDeprecatedController@index',
            ]
        );

        /** LedgerNativeAccounts */
        Route::get(
            'LedgerNativeAccounts/clients/{client_id}/properties/{property_id}/reportTemplates/{report_template_id}/month/{month}/year/{year}/quarterly/{quarterly}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.LedgerNativeAccountController.index',
                'uses' => 'LedgerNativeAccountDeprecatedController@index',
            ]
        );
    }
);
