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
        /**
         * ActualBudgetVarianceTotal: property / RTAG/
         */
        Route::get(
            'clients/{client_id}/properties/{property_id}/reportTemplateAccountGroups/{report_template_account_group_id}/actualBudgetVarianceTotal',
            [
                'as'   => 'api.v1.ActualBudgetVarianceTotalController.getReportTemplateAccountGroupActualBudgetVarianceTotalForProperty',
                'uses' => 'ActualBudgetVarianceTotalController@getReportTemplateAccountGroupActualBudgetVarianceTotalForProperty',
            ]
        );
        /**
         * ActualBudgetVarianceTotal: property / CF/
         */
        Route::get(
            'clients/{client_id}/properties/{property_id}/calculatedFields/{calculated_field_id}/actualBudgetVarianceTotal',
            [
                'as'   => 'api.v1.ActualBudgetVarianceTotalController.getCalculatedFieldActualBudgetVarianceTotalForProperty',
                'uses' => 'ActualBudgetVarianceTotalController@getCalculatedFieldActualBudgetVarianceTotalForProperty',
            ]
        );
        /**
         * ActualBudgetVarianceTotal: property_group / RTAG/
         */
        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/reportTemplateAccountGroups/{report_template_account_group_id}/actualBudgetVarianceTotal',
            [
                'as'   => 'api.v1.ActualBudgetVarianceTotalController.getReportTemplateAccountGroupActualBudgetVarianceTotalForPropertyGroup',
                'uses' => 'ActualBudgetVarianceTotalController@getReportTemplateAccountGroupActualBudgetVarianceTotalForPropertyGroup',
            ]
        );
        /**
         * ActualBudgetVarianceTotal: property_group / CF/
         */
        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/calculatedFields/{calculated_field_id}/actualBudgetVarianceTotal',
            [
                'as'   => 'api.v1.ActualBudgetVarianceTotalController.getCalculatedFieldActualBudgetVarianceTotalForPropertyGroup',
                'uses' => 'ActualBudgetVarianceTotalController@getCalculatedFieldActualBudgetVarianceTotalForPropertyGroup',
            ]
        );

        /*********************************************************/

        /**
         * ActualBudgetVarianceOverTime: property / RTAG/
         */
        Route::get(
            'clients/{client_id}/properties/{property_id}/reportTemplateAccountGroups/{report_template_account_group_id}/actualBudgetVarianceOverTime',
            [
                'as'   => 'api.v1.ActualBudgetVarianceOverTimeController.getReportTemplateAccountGroupsActualBudgetVarianceOverTimeForProperty',
                'uses' => 'ActualBudgetVarianceOverTimeController@getReportTemplateAccountGroupsActualBudgetVarianceOverTimeForProperty',
            ]
        );
        /**
         * ActualBudgetVarianceOverTime: property / CF/
         */
        Route::get(
            'clients/{client_id}/properties/{property_id}/calculatedFields/{calculated_field_id}/actualBudgetVarianceOverTime',
            [
                'as'   => 'api.v1.ActualBudgetVarianceOverTimeController.getCalculatedFieldActualBudgetVarianceOverTimeForProperty',
                'uses' => 'ActualBudgetVarianceOverTimeController@getCalculatedFieldActualBudgetVarianceOverTimeForProperty',
            ]
        );
        /**
         * ActualBudgetVarianceOverTime: property_group / RTAG/
         */

        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/reportTemplateAccountGroups/{report_template_account_group_id}/actualBudgetVarianceOverTime',
            [
                'as'   => 'api.v1.ActualBudgetVarianceOverTimeController.getReportTemplateAccountGroupsActualBudgetVarianceOverTimeForPropertyGroup',
                'uses' => 'ActualBudgetVarianceOverTimeController@getReportTemplateAccountGroupsActualBudgetVarianceOverTimeForPropertyGroup',
            ]
        );
        /**
         * ActualBudgetVarianceOverTime: property_group / CF/
         */
        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/calculatedFields/{calculated_field_id}/actualBudgetVarianceOverTime',
            [
                'as'   => 'api.v1.ActualBudgetVarianceOverTimeController.getCalculatedFieldActualBudgetVarianceOverTimeForPropertyGroup',
                'uses' => 'ActualBudgetVarianceOverTimeController@getCalculatedFieldActualBudgetVarianceOverTimeForPropertyGroup',
            ]
        );

        /*********************************************************/

        /**
         * ActualBudgetVarianceBreakdown: property / RTAG/
         */
        Route::get(
            'clients/{client_id}/properties/{property_id}/reportTemplateAccountGroups/{report_template_account_group_id}/actualBudgetVarianceBreakdown',
            [
                'as'   => 'api.v1.ActualBudgetVarianceBreakdownController.getReportTemplateAccountGroupsActualBudgetVarianceBreakdownForProperty',
                'uses' => 'ActualBudgetVarianceBreakdownController@getReportTemplateAccountGroupsActualBudgetVarianceBreakdownForProperty',
            ]
        );
        /**
         * ActualBudgetVarianceBreakdown: property / CF/
         */
        //Route::get(
        //    'clients/{client_id}/properties/{property_id}/calculatedFields/{calculated_field_id}/actualBudgetVarianceBreakdown',
        //    [
        //        'as'   => 'api.v1.ActualBudgetVarianceBreakdownController.getCalculatedFieldActualBudgetVarianceBreakdownForProperty',
        //        'uses' => 'ActualBudgetVarianceBreakdownController@getCalculatedFieldActualBudgetVarianceBreakdownForProperty',
        //    ]
        //);
        /**
         * ActualBudgetVarianceBreakdown: property_group / RTAG/
         */

        Route::get(
            'clients/{client_id}/propertyGroups/{property_group_id}/reportTemplateAccountGroups/{report_template_account_group_id}/actualBudgetVarianceBreakdown',
            [
                'as'   => 'api.v1.ActualBudgetVarianceBreakdownController.getReportTemplateAccountGroupsActualBudgetVarianceBreakdownForPropertyGroup',
                'uses' => 'ActualBudgetVarianceBreakdownController@getReportTemplateAccountGroupsActualBudgetVarianceBreakdownForPropertyGroup',
            ]
        );

        Route::get(
            'clients/{client_id}/reportTemplates/{report_template_id}/summaryAccounts',
            [
                'as'   => 'api.v1.reportTemplates.summaries',
                'uses' => 'ReportTemplateController@getSummaryAccounts',
            ]
        );
        /**
         * ActualBudgetVarianceBreakdown: property_group / CF/
         */
        //Route::get(
        //    'clients/{client_id}/propertyGroups/{property_group_id}/calculatedFields/{calculated_field_id}/actualBudgetVarianceBreakdown',
        //    [
        //        'as'   => 'api.v1.NativeChartController.getCalculatedFieldActualBudgetVarianceBreakdownForPropertyGroup',
        //        'uses' => 'NativeChartController@getCalculatedFieldActualBudgetVarianceBreakdownForPropertyGroup',
        //    ]
        //);
    }
);
