<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Controllers\Api\Ledger\LedgerController;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Ledger\Ledger;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Models\NativeAccountTypeSummary;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\User;
use const PHP_EOL;

/**
 * Class AlterClientConfigCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class AlterNativeAccountTypeTabOrderingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:config:client:native_account_type_tabs_order  
                        {--client_id= : client_id} 
                        {--location= : choose either ANALYTICS or ADVANCED_VARIANCE} 
                        {--native_account_type_names= : comma separated list of account type names}
                        {--use_top_of_expenses_tree= : boolean (optional) and for ANALYTICS only}
                        {--overwrite_users_defaults= : boolean (optional) and for ANALYTICS only, ignored for ADVANCED_VARIANCE} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = null;

    protected $allowed_location_values = null;

    /**
     * AlterClientConfigCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj in parent::__construct. Messes up code generator
         */
    }

    /**
     * Execute the console command.
     *
     * @todo push this logic into a repository
     */
    public function handle()
    {
        parent::handle();

        $this->allowed_location_values = [
            Ledger::ANALYTICS_CONFIG_KEY,
            AdvancedVariance::ADVANCED_VARIANCE_CONFIG_KEY,
        ];
        $this->description             = 'Update ' . NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY . ' in the client object' . PHP_EOL;

        if ( ! $client_id = $this->option('client_id'))
        {
            $this->error('no client_id detected - this is a required parameter');
            return 1;
        }
        if ( ! $location = $this->option('location'))
        {
            $this->error('no location detected - this is a required parameter');
            return 1;
        }

        $isAnalytics        = stri_equal($location, Ledger::ANALYTICS_CONFIG_KEY);
        $isAdvancedVariance = stri_equal($location, AdvancedVariance::ADVANCED_VARIANCE_CONFIG_KEY);

        if ( ! $native_account_type_names = $this->option('native_account_type_names'))
        {
            $this->error('no native account type names detected - this is a required parameter');
            return 1;
        }

        $overwrite_users_defaults = $this->option('overwrite_users_defaults') ?? null;
        $use_top_of_expenses_tree = $this->option('use_top_of_expenses_tree') === 'true';

        if (
            $overwrite_users_defaults
            &&
            ! (
                $overwrite_users_defaults === 'true'
                ||
                $overwrite_users_defaults === 'false'
            )
        )
        {
            $this->error('overwrite_users_defaults requires a boolean value, ' . $overwrite_users_defaults . ' given');
            return 1;
        }

        /** @var Client $ClientObj */
        if ( ! $ClientObj = $this->ClientRepositoryObj->find($client_id))
        {
            $this->error('no client found with the client_id given - please check the client_id parameter you provided and try again.');
            return 1;
        }
        if ( ! in_arrayi($location, $this->allowed_location_values))
        {
            $this->error('The location given is not in the allowed list, please choose either ANALYTICS or ADVANCED_VARIANCE');
            return 1;
        }

        $NativeAccountTypeSummaryArr = [];

        /** @var ReportTemplate $ReportTemplateObj */
        if ( ! $ReportTemplateObj = ReportTemplate::where(
            [
                'client_id'                                   => $client_id,
                'is_default_analytics_report_template'        => $isAnalytics,
                'is_default_advance_variance_report_template' => $isAdvancedVariance,
            ]
        )->first())
        {
            throw new GeneralException('Could not find default report template', 403);
        }

        foreach (explode(',', $native_account_type_names) as $native_account_type_name)
        {
            /**
             * @todo (Alex) [283y98dsf] - remove this exception if/when we start using the top of the expenses account tree
             */
            if (
                $isAnalytics
                &&
                stri_equal($native_account_type_name, LedgerController::NATIVE_ACCOUNT_TYPE_EXPENSES_TEXT)
                &&
                ! $use_top_of_expenses_tree
            )
            {
                $ReportTemplateAccountGroupObj = $ReportTemplateObj->reportTemplateAccountGroups
                    ->where('report_template_account_group_code', '=', LedgerController::OPERATING_EXPENSES_DEFAULT_CODE)
                    ->unique('native_account_type_id')
                    ->first();
            }
            else
            {
                $ReportTemplateAccountGroupObj = $ReportTemplateObj->reportTemplateAccountGroups
                    ->where('parent_report_template_account_group_id', '=', null)
                    ->filter(
                        function ($item) use ($native_account_type_name)
                        {
                            return stri_contains($item->report_template_account_group_name, $native_account_type_name);
                        }
                    )
                    ->unique('native_account_type_id')
                    ->first();
            }

            /** @var NativeAccountTypeSummary $NativeAccountTypeSummary */
            if (
                ! $ReportTemplateAccountGroupObj
                ||
                ! $NativeAccountTypeSummary = $ClientObj->getNativeAccountTypeSummaryIncludingRTAG($ReportTemplateAccountGroupObj)
            )
            {
                $this->warn('the report template account group could not be found for the native account type "' . $native_account_type_name . '" so this is being ignored. If this native account type is required please attach it to this report template: ' . $ReportTemplateObj->report_template_name . ' with report template id of ' . $ReportTemplateObj->id);
                continue;
            };

            if ( ! is_null($NativeAccountTypeSummary->report_template_account_group_id))
            {
                $NativeAccountTypeSummaryArr[] = $NativeAccountTypeSummary->toArrayWithAdditionalAttributes();
            }
        }

        if (
            empty($NativeAccountTypeSummaryArr)
            &&
            ! $this->confirm("The list of native account types is empty which may mean you'll not show any tabs for $location, would you like continue?")
        )
        {
            $this->warn('You have chosen not to update the native account types list for ' . $location);
            return 0;
        }

        $ClientConfigArr = json_decode($ClientObj->config_json, true);

        $ClientConfigArr
        [NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY]
        [$location]
            = $NativeAccountTypeSummaryArr;

        // only advanced variance default are set on the client config obj
        if ($isAdvancedVariance)
        {
            if ( ! empty($NativeAccountTypeSummaryArr))
            {
                $ClientConfigArr
                [Client::WAYPOINT_LEDGER_DROPDOWNS]
                [Client::DEFAULTS_CONFIG_KEY]
                ['activeAccountTab']
                    = array_first($ClientConfigArr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][$location])
                ['native_account_type_name'];

                $ClientConfigArr
                [Client::WAYPOINT_LEDGER_DROPDOWNS]
                [Client::DEFAULTS_CONFIG_KEY]
                ['accountTypeFilters']
                    = [];

                // reset the tabs
                $ClientConfigArr[AdvancedVariance::ADVANCED_VARIANCE_TABS_CONFIG_KEY] = [];

                foreach ($NativeAccountTypeSummaryArr as $native_account_type_arr)
                {
                    $ClientConfigArr
                    [Client::WAYPOINT_LEDGER_DROPDOWNS]
                    [Client::DEFAULTS_CONFIG_KEY]
                    ['accountTypeFilters']
                    [$native_account_type_arr['native_account_type_name']]
                        = $native_account_type_arr['report_template_account_group_id'];

                    $ClientConfigArr[AdvancedVariance::ADVANCED_VARIANCE_TABS_CONFIG_KEY][] = $native_account_type_arr['native_account_type_name'];
                }
            }

            $this->comment('This command has been performed although you may not see the changes you just made to the Advanced Variance tabs until you log out and log back into that client.');
        }

        $ClientObj->config_json = json_encode($ClientConfigArr);
        $ClientObj->save();

        // Adjust user config objects for all the users of that client to reflect the change for analytics only
        if (
            $overwrite_users_defaults
            &&
            $isAnalytics
        )
        {

            /** @var User $UserObj */
            foreach ($ClientObj->users as $UserObj)
            {
                $NativeAccountTypeSummaryArr = [];
                $user_config_arr             = $UserObj->getConfigJSON(true);

                /** @var ReportTemplate $ReportTemplateObj */
                if ( ! $ReportTemplateObj = ReportTemplate::find($user_config_arr[User::DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG]))
                {
                    throw new GeneralException('Could not find default report template', 404);
                }

                foreach (explode(',', $native_account_type_names) as $native_account_type_name)
                {

                    if (
                        stri_equal($native_account_type_name, LedgerController::NATIVE_ACCOUNT_TYPE_EXPENSES_TEXT)
                        &&
                        ! $use_top_of_expenses_tree
                    )
                    {
                        $ReportTemplateAccountGroups =
                            $this->ReportTemplateAccountGroupRepositoryObj
                                ->findWhere(
                                    [
                                        'report_template_account_group_code' => LedgerController::OPERATING_EXPENSES_DEFAULT_CODE,
                                        'report_template_id'                 => $ReportTemplateObj->id,
                                    ]
                                );
                    }
                    else
                    {
                        $ReportTemplateAccountGroups =
                            $this->ReportTemplateAccountGroupRepositoryObj
                                ->findWhere(
                                    [
                                        'parent_report_template_account_group_id' => null,
                                        'report_template_id'                      => $ReportTemplateObj->id,
                                    ]
                                );
                    }

                    $ReportTemplateAccountGroupObj = $ReportTemplateAccountGroups->filter(function ($item) use ($native_account_type_name)
                    {
                        return stri_contains($item->report_template_account_group_name, $native_account_type_name);
                    }
                    )
                                                                                 ->unique('native_account_type_id')
                                                                                 ->first();

                    /** @var NativeAccountTypeSummary $NativeAccountTypeSummary */
                    if (
                        ! $ReportTemplateAccountGroupObj
                        ||
                        ! $NativeAccountTypeSummary = $UserObj->getNativeAccounTypeSummaryIncludingRTAG($ReportTemplateAccountGroupObj)
                    )
                    {
                        $this->warn('the report template account group could not be found for the native account type "' . $native_account_type_name . '" so this is being ignored. If this native account type is required please attach it to this report template: ' . $ReportTemplateObj->report_template_name . ', for this user: ' . $UserObj->getDisplayName());
                        continue;
                    };

                    if ( ! is_null($NativeAccountTypeSummary->report_template_account_group_id))
                    {
                        $NativeAccountTypeSummaryArr[] = $NativeAccountTypeSummary->toArrayWithAdditionalAttributes();
                    }
                }

                if (
                    empty($NativeAccountTypeSummaryArr)
                    &&
                    ! $this->confirm("The list of native account types is empty which may mean you'll not show any tabs for $location, would you like continue?")
                )
                {
                    $this->warn('You have chosen not to update the native account types list for ' . $location);
                    return 0;
                }

                $user_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][Ledger::ANALYTICS_CONFIG_KEY]
                    = $NativeAccountTypeSummaryArr;

                $user_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['activeAccountTab']
                    = array_first($NativeAccountTypeSummaryArr)['native_account_type_name'];

                $user_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['accountTypeFilters']
                    = [];

                foreach ($NativeAccountTypeSummaryArr as $native_account_type_arr)
                {
                    $user_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]['accountTypeFilters'][$native_account_type_arr['native_account_type_name']]
                        = $native_account_type_arr['report_template_account_group_id'];
                }

                $UserObj->config_json = json_encode($user_config_arr);
                $UserObj->save();
            }
        }

        return true;
    }
}
