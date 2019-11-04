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
    ],
    function ()
    {
        /** Native Chart of Accounts Property Report */
        Route::get(
            '/clients/{client_id}/NativeCoaReport/property/{property_id}',
            [
                'as'   => 'api.v1.NativeCoaReportLedgerController.getPropertyReport',
                'uses' => 'NativeCoaReportLedgerController@getPropertyReport',
            ]
        );

        /** Native Chart of Accounts Group Report */
        Route::get(
            '/clients/{client_id}/NativeCoaReport/group/{group_id}',
            [
                'as'   => 'api.v1.NativeCoaReportLedgerController.getPropertyGroupReport',
                'uses' => 'NativeCoaReportLedgerController@getPropertyGroupReport',
            ]
        );

        /** Peer Average Notes */
        Route::get(
            '/clients/{client_id}/PeerNotes/property/{property_id}/year/{year}/area/{area}',
            [
                'as'   => 'api.v1.PeerNotesController.index',
                'uses' => 'PeerNotesController@index',
            ]
        );

        /** Overview Spreadsheet */
        Route::get(
            '/clients/{client_id}/OperatingExpensesCombinedSpreadsheet/{ledgerDataType}/{ledger_data_type_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.SpreadsheetController.createCombinedOperatingExpensesSpreadsheet',
                'uses' => 'SpreadsheetController@createCombinedOperatingExpensesSpreadsheet',
            ]
        );

        /** Variance Combined Spreadsheet */
        Route::get(
            '/clients/{client_id}/VarianceCombinedSpreadsheet/{ledgerDataType}/{ledger_data_type_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.SpreadsheetController.createCombinedVarianceSpreadsheet',
                'uses' => 'SpreadsheetController@createCombinedVarianceSpreadsheet',
            ]
        );

        /** Peer Average Combined Spreadsheet */
        Route::get(
            '/clients/{client_id}/PeerAverageCombinedSpreadsheet/{ledgerDataType}/{ledger_data_type_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.SpreadsheetController.createCombinedPeerAverageSpreadsheet',
                'uses' => 'SpreadsheetController@createCombinedPeerAverageSpreadsheet',
            ]
        );

        /** Peer Average Combined Spreadsheet */
        Route::get(
            '/clients/{client_id}/YearOverYearCombinedSpreadsheet/{ledgerDataType}/{ledger_data_type_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.SpreadsheetController.createCombinedYearOverYearSpreadsheet',
                'uses' => 'SpreadsheetController@createCombinedYearOverYearSpreadsheet',
            ]
        );

        /** Overview Spreadsheet */
        Route::get(
            '/clients/{client_id}/CombinedOverviewSpreadsheet/{ledgerDataType}/{ledger_data_type_id}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.SpreadsheetController.createCombinedOverviewSpreadsheet',
                'uses' => 'SpreadsheetController@createCombinedOverviewSpreadsheet',
            ]
        );

        /** PeerAverageProperty */
        Route::get(
            '/clients/{client_id}/PeerAverageProperty/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.PeerAveragePropertyController.index',
                'uses' => 'PeerAveragePropertyController@index',
            ]
        );

        /** PeerAveragePropertyGroup */
        Route::get(
            '/clients/{client_id}/PeerAveragePropertyGroup/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.PeerAveragePropertyGroupController.index',
                'uses' => 'PeerAveragePropertyGroupController@index',
            ]
        );

        /** PeerAveragePropertyRanking */
        Route::get(
            '/clients/{client_id}/PeerAveragePropertyRanking/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.PeerAveragePropertyRankingController.index',
                'uses' => 'PeerAveragePropertyRankingController@index',
            ]
        );

        /** PeerAveragePropertyGroupRanking */
        Route::get(
            '/clients/{client_id}/PeerAveragePropertyGroupRanking/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.PeerAveragePropertyGroupRankingController.index',
                'uses' => 'PeerAveragePropertyGroupRankingController@index',
            ]
        );

        /** OccupancyProperty */
        Route::get(
            '/clients/{client_id}/OccupancyProperty/property/{property_id}/year/{year}',
            [
                'as'   => 'api.v1.OccupancyPropertyController.index',
                'uses' => 'OccupancyPropertyController@index',
            ]
        );

        /** OccupancyProperty */
        Route::get(
            '/clients/{client_id}/AsOfMonthOccupancy/property/{property_id}',
            [
                'as'   => 'api.v1.OccupancyPropertyController.getAsOfMonthOccupancyProperty',
                'uses' => 'OccupancyPropertyController@getAsOfMonthOccupancyProperty',
            ]
        );

        /** OccupancyProperty */
        Route::get(
            '/clients/{client_id}/AsOfMonthOccupancy/group/{property_group_id}',
            [
                'as'   => 'api.v1.OccupancyPropertyController.getAsOfMonthOccupancyPropertyGroup',
                'uses' => 'OccupancyPropertyController@getAsOfMonthOccupancyPropertyGroup',
            ]
        );

        /** OccupancyPropertyGroup */
        Route::get(
            '/clients/{client_id}/OccupancyPropertyGroup/group/{property_group_id}/year/{year}',
            [
                'as'   => 'api.v1.OccupancyPropertyGroupController.index',
                'uses' => 'OccupancyPropertyGroupController@index',
            ]
        );

        /** MontlyOccupancy - property */
        Route::get(
            'clients/{client_id}/MonthlyOccupancy/property/{property_id}/year/{year}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.MonthlyOccupancyController.getMonthlyOccupancyForProperty',
                'uses' => 'MonthlyOccupancyController@getMonthlyOccupancyForProperty',
            ]
        );

        /** MontlyOccupancy - property group */
        Route::get(
            'clients/{client_id}/MonthlyOccupancy/group/{property_group_id}/year/{year}',
            [
                'as'   => 'api.v1.' . Role::CLIENT_GENERIC_USER_ROLE . '.MonthlyOccupancyController.getMonthlyOccupancyForPropertyGroup',
                'uses' => 'MonthlyOccupancyController@getMonthlyOccupancyForPropertyGroup',
            ]
        );

        /** CompareProperty */
        Route::get(
            '/clients/{client_id}/CompareProperty/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.ComparePropertyController.index',
                'uses' => 'ComparePropertyController@index',
            ]
        );

        /** ComparePropertyGroup */
        Route::get(
            '/clients/{client_id}/ComparePropertyGroup/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.ComparePropertyGroupController.index',
                'uses' => 'ComparePropertyGroupController@index',
            ]
        );

        /** OperatingExpensesProperty */
        Route::get(
            '/clients/{client_id}/OperatingExpensesProperty/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.OperatingExpensesPropertyController.index',
                'uses' => 'OperatingExpensesPropertyController@index',
            ]
        );

        /** OperatingExpensesPropertyRanking */
        Route::get(
            '/clients/{client_id}/OperatingExpensesPropertyRanking/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.OperatingExpensesPropertyRankingController.index',
                'uses' => 'OperatingExpensesPropertyRankingController@index',
            ]
        );

        /** OperatingExpensesPropertyGroup */
        Route::get(
            '/clients/{client_id}/OperatingExpensesPropertyGroup/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.OperatingExpensesPropertyGroupController.index',
                'uses' => 'OperatingExpensesPropertyGroupController@index',
            ]
        );

        /** OperatingExpensesPropertyGroupRanking */
        Route::get(
            '/clients/{client_id}/OperatingExpensesPropertyGroupRanking/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.OperatingExpensesPropertyGroupRankingController.index',
                'uses' => 'OperatingExpensesPropertyGroupRankingController@index',
            ]
        );

        /** VarianceProperty */
        Route::get(
            '/clients/{client_id}/VarianceProperty/property/{property_id}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.VariancePropertyController.index',
                'uses' => 'VariancePropertyController@index',
            ]
        );

        /** VariancePropertyGroup */
        Route::get(
            '/clients/{client_id}/VariancePropertyGroup/group/{property_group_id}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.VariancePropertyGroupController.index',
                'uses' => 'VariancePropertyGroupController@index',
            ]
        );

        /** VariancePropertyRanking */
        Route::get(
            '/clients/{client_id}/VariancePropertyRanking/property/{property_id}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.VariancePropertyRankingController.index',
                'uses' => 'VariancePropertyRankingController@index',
            ]
        );

        /** VariancePropertyGroupRanking */
        Route::get(
            '/clients/{client_id}/VariancePropertyGroupRanking/group/{property_group_id}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.VariancePropertyGroupRankingController.index',
                'uses' => 'VariancePropertyGroupRankingController@index',
            ]
        );

        /** YearOverYearProperty */
        Route::get(
            '/clients/{client_id}/YearOverYearProperty/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.YearOverYearPropertyController.index',
                'uses' => 'YearOverYearPropertyController@index',
            ]
        );

        /** YearOverYearPropertyRanking */
        Route::get(
            '/clients/{client_id}/YearOverYearPropertyRanking/property/{property_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.YearOverYearPropertyRankingController.index',
                'uses' => 'YearOverYearPropertyRankingController@index',
            ]
        );

        /** YearOverYearPropertyGroup */
        Route::get(
            '/clients/{client_id}/YearOverYearPropertyGroup/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.YearOverYearPropertyGroupController.index',
                'uses' => 'YearOverYearPropertyGroupController@index',
            ]
        );

        /** YearOverYearPropertyGroupRanking */
        Route::get(
            '/clients/{client_id}/YearOverYearPropertyGroupRanking/group/{property_group_id}/report/{report}/year/{year}/period/{period}/area/{area}/report_template_account_group/{report_template_account_group_id}',
            [
                'as'   => 'api.v1.YearOverYearPropertyGroupRankingController.index',
                'uses' => 'YearOverYearPropertyGroupRankingController@index',
            ]
        );

        /** LedgerNativeAccounts */
        Route::get(
            'LedgerNativeAccounts/clients/{client_id}/properties/{property_id}/reportTemplates/{report_template_id}/month/{month}/year/{year}/quarterly/{quarterly}',
            [
                'as'   => 'api.v1.LedgerNativeAccountController.index',
                'uses' => 'LedgerNativeAccountController@index',
            ]
        );

        /** LedgerNativeAccounts */
        Route::get(
            '/clients/{client_id}/properties/{property_id}/reportTemplates/{report_template_id}/month/{month}/year/{year}/quarterly/{quarterly}/LedgerNativeAccounts',
            [
                'as'   => 'api.v1.LedgerNativeAccountController.index',
                'uses' => 'LedgerNativeAccountController@index',
            ]
        );
    }
);
