<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Models\NativeCoa;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\ReportTemplateMapping;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;

/**
 * Class ReportTemplateRepository
 * @package App\Waypoint\Repositories
 */
class ReportTemplateRepository extends ReportTemplateRepositoryBase
{
    /** @var ReportTemplateAccountGroupRepository */
    public $ReportTemplateAccountGroupRepositoryObj;

    /** @var ReportTemplateMappingRepository */
    public $ReportTemplateMappingRepositoryObj;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->ReportTemplateAccountGroupRepositoryObj = App::make(ReportTemplateAccountGroupRepository::class);
        $this->ReportTemplateMappingRepositoryObj      = App::make(ReportTemplateMappingRepository::class);
    }

    /**
     * @return string
     */
    public function model()
    {
        return ReportTemplate::class;
    }

    /**
     * @param array $attributes
     * @return ReportTemplate
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        if ( ! isset($attributes['client_id']) || ! $attributes['client_id'])
        {
            throw new GeneralException('no client id');
        }
        if (isset($attributes['is_default_advance_variance_report_template']) && $attributes['is_default_advance_variance_report_template'])
        {
            foreach ($this->findWhere(['client_id' => $attributes['client_id']]) as $ReportTemplateObj)
            {
                $this->update(
                    [
                        'is_default_advance_variance_report_template' => false,
                    ],
                    $ReportTemplateObj->id
                );
            }
        }
        else
        {
            $attributes['is_default_advance_variance_report_template'] = false;
        }
        if (isset($attributes['is_default_analytics_report_template']) && $attributes['is_default_analytics_report_template'])
        {
            foreach ($this->findWhere(['client_id' => $attributes['client_id']]) as $ReportTemplateObj)
            {
                $this->update(
                    [
                        'is_default_analytics_report_template' => false,
                    ],
                    $ReportTemplateObj->id
                );
            }
        }
        else
        {
            $attributes['is_default_analytics_report_template'] = false;
        }
        return parent::create($attributes);
    }

    /**
     * @param integer $client_id
     * @return ReportTemplate
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function generateAccountTypeBasedReportTemplate($client_id)
    {
        if ( ! $ClientObj = App::make(ClientRepository::class)->with('properties.nativeCoas.nativeAccounts.nativeAccountType')->find($client_id))
        {
            throw new GeneralException('no such client', 404);
        }

        /** @var ReportTemplate $ReportTemplateObj */
        $ReportTemplateObj                       = $this->create(
            [
                'report_template_name'                        => 'Account Type Based Report Template for ' . $ClientObj->name . ' ' . Carbon::now()->format('Y-m-d H:i:s'),
                'report_template_description'                 => 'Account Type Based Report Template for ' . $ClientObj->name . ' ' . Carbon::now()->format('Y-m-d H:i:s'),
                'client_id'                                   => $ClientObj->id,
                'is_boma_report_template'                     => false,
                'is_default_advance_variance_report_template' => true,
                'is_default_analytics_report_template'        => false,
            ]
        );
        $report_template_account_group_name_hash = [];

        foreach ($ClientObj->nativeAccountTypes as $NativeAccountTypeObj)
        {
            $de_dup = null;
            if ($this->ReportTemplateAccountGroupRepositoryObj->findWhere(
                [
                    'report_template_account_group_name' => $NativeAccountTypeObj->native_account_type_name,
                    'report_template_id'                 => $ReportTemplateObj->id,
                ]
            )->first())
            {
                $de_dup = mt_rand();
            }
            $report_template_account_group_name_hash[$NativeAccountTypeObj->native_account_type_name] =
                $this->ReportTemplateAccountGroupRepositoryObj->create(
                    [
                        'report_template_id'                      => $ReportTemplateObj->id,
                        'parent_report_template_account_group_id' => null,
                        'is_category'                             => true,
                        'is_waypoint_specific'                    => false,
                        'report_template_account_group_name'      => $NativeAccountTypeObj->native_account_type_name . $de_dup,
                        'display_name'                            => $NativeAccountTypeObj->native_account_type_name,
                        'native_account_type_id'                  => $NativeAccountTypeObj->id,
                        'report_template_account_group_code'      => uniqid(),
                    ]
                );
        }
        return $ReportTemplateObj;
    }

    /**
     * @param integer $client_id
     * @throws GeneralException
     */
    public function refreshAccountTypeBasedReportTemplate($client_id)
    {
        /** @var Client $ClientObj */
        if ( ! $ClientObj =
            App::make(ClientRepository::class)
               ->with('nativeCoas.nativeAccounts')->find($client_id))
        {
            throw new GeneralException('no such client', 404);
        }

        /** @var ReportTemplate $ReportTemplateObj */
        if ( ! $ReportTemplateObj = $ClientObj->reportTemplates->where('is_default_advance_variance_report_template', 1)->first())
        {
            throw new GeneralException('no such $ReportTemplateObj', 404);
        }

        /** @var NativeCoa $NativeCoaObj */
        foreach ($ClientObj->nativeCoas as $NativeCoaObj)
        {
            $report_template_native_account_id_arr = $ReportTemplateObj->getAllNativeAccounts()->pluck('id')->toArray();
            foreach ($NativeCoaObj->nativeAccounts as $NativeAccountObj)
            {
                if (in_array($NativeAccountObj->id, $report_template_native_account_id_arr))
                {
                    continue;
                }

                $this->ReportTemplateMappingRepositoryObj->create(
                    [
                        'native_account_id'                => $NativeAccountObj->id,
                        'report_template_account_group_id' =>
                            $ReportTemplateObj->reportTemplateAccountGroups
                                ->filter(
                                    function ($ReportTemplateAccountGroupObj)
                                    {
                                        return $ReportTemplateAccountGroupObj->parent_report_template_account_group_id == null;
                                    }
                                )
                                ->where(
                                    'native_account_type_id',
                                    $NativeAccountObj->native_account_type_id
                                )
                                ->first()->id,
                    ]
                );
            }
        }
    }

    /**
     * @param integer $client_id
     * @return ReportTemplate
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function generateBomaBasedReportTemplate($client_id)
    {
        if ( ! $ClientObj = App::make(ClientRepository::class)->find($client_id))
        {
            throw new GeneralException('no such client', 404);
        }

        ReportTemplate::setSuspendValidation(true);
        ReportTemplateAccountGroup::setSuspendValidation(true);
        ReportTemplateMapping::setSuspendValidation(true);

        $is_default_analytics_report_template = true;
        if ($ClientObj->reportTemplates->where('is_default_analytics_report_template', true)->first())
        {
            $is_default_analytics_report_template = false;
        }

        $is_boma_report_template = true;
        if ($ClientObj->reportTemplates->where('is_boma_report_template', true)->first())
        {
            $is_boma_report_template = false;
        }

        /** @var ReportTemplate $ReportTemplateObj */
        $BomaBasedReportTemplateObj                = $this->create(
            [
                'report_template_name'                 => 'BOMA Based Report Template for ' . $ClientObj->name . ' ' . Carbon::now()->format('Y-m-d H:i:s'),
                'report_template_description'          => 'BOMA Based Report Template for ' . $ClientObj->name . ' ' . Carbon::now()->format('Y-m-d H:i:s'),
                'client_id'                            => $ClientObj->id,
                'is_boma_report_template'              => $is_boma_report_template,
                'is_default_analytics_report_template' => $is_default_analytics_report_template,
            ]
        );
        $report_template_account_group_old_id_hash = [];
        /** @var ReportTemplate $DummyClientReportTemplateObj */
        $DummyClientReportTemplateObj = $this
            ->with('reportTemplateAccountGroups.reportTemplateAccountGroupParent')
            ->with('reportTemplateAccountGroups.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent')
            ->with('reportTemplateAccountGroups.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent')
            ->with('reportTemplateAccountGroups.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent')
            ->with('reportTemplateAccountGroups.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent')
            ->with('reportTemplateAccountGroups.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent')
            ->find(1);

        /**
         * @var ReportTemplateAccountGroup[] $ReportTemplateAccountGroupsArr
         *
         * The order we create these (top to bottom) is important
         */
        $ReportTemplateAccountGroupsArr = $DummyClientReportTemplateObj->reportTemplateAccountGroups->sortBy(
            function ($ReportTemplateAccountGroupObj, $key)
            {
                /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
                return $ReportTemplateAccountGroupObj->get_generations();
            }
        );

        foreach ($ReportTemplateAccountGroupsArr as $DummyClientReportTemplateAccountGroupsObj)
        {
            if (preg_match("/^[37]/", $DummyClientReportTemplateAccountGroupsObj->report_template_account_group_code))
            {
                $account_type_name = NativeAccount::NATIVE_ACCOUNT_TYPE_REVENUE;
            }
            elseif (preg_match("/^[4568]/", $DummyClientReportTemplateAccountGroupsObj->report_template_account_group_code))
            {
                $account_type_name = NativeAccount::NATIVE_ACCOUNT_TYPE_EXPENSES;
            }
            else
            {
                throw new GeneralException('unable to find $account_type_name');
            }

            /** @var NativeAccountTypeRepository $NativeAccountTypeRepositoryObj */
            $NativeAccountTypeRepositoryObj = App::make(NativeAccountTypeRepository::class);
            if ( ! $NativeAccountTypeObj = $NativeAccountTypeRepositoryObj->findWhere(
                [
                    'client_id'                => $client_id,
                    'native_account_type_name' => $account_type_name,
                ])->first())
            {
                $NativeAccountTypeObj = $NativeAccountTypeRepositoryObj->create(
                    [
                        'client_id'                       => $client_id,
                        'native_account_type_description' => $account_type_name,
                        'native_account_type_name'        => $account_type_name,
                    ]
                );
            }

            $parent_report_template_account_group_id = null;
            if (isset($report_template_account_group_old_id_hash[$DummyClientReportTemplateAccountGroupsObj->parent_report_template_account_group_id]))
            {
                $parent_report_template_account_group_id = $report_template_account_group_old_id_hash[$DummyClientReportTemplateAccountGroupsObj->parent_report_template_account_group_id]->id;
            }

            $report_template_account_group_old_id_hash[$DummyClientReportTemplateAccountGroupsObj->id] = $this->ReportTemplateAccountGroupRepositoryObj->create(
                [
                    'report_template_id'                      => $BomaBasedReportTemplateObj->id,
                    'parent_report_template_account_group_id' => $parent_report_template_account_group_id,
                    'native_account_type_id'                  => $NativeAccountTypeObj->id,
                    'is_category'                             => $DummyClientReportTemplateAccountGroupsObj->is_category,
                    'is_major_category'                       => $DummyClientReportTemplateAccountGroupsObj->is_major_category,
                    'is_waypoint_specific'                    => $DummyClientReportTemplateAccountGroupsObj->is_waypoint_specific,
                    'report_template_account_group_code'      => $DummyClientReportTemplateAccountGroupsObj->report_template_account_group_code,
                    'report_template_account_group_name'      => $DummyClientReportTemplateAccountGroupsObj->report_template_account_group_name,
                    'display_name'                            => $DummyClientReportTemplateAccountGroupsObj->display_name,
                    'usage_type'                              => $DummyClientReportTemplateAccountGroupsObj->usage_type,
                    'sorting'                                 => $DummyClientReportTemplateAccountGroupsObj->sorting,
                    'version_num'                             => $DummyClientReportTemplateAccountGroupsObj->version_num,
                    'deprecated_waypoint_code'                => $DummyClientReportTemplateAccountGroupsObj->deprecated_waypoint_code,
                    'boma_account_header_1_code_old'          => $DummyClientReportTemplateAccountGroupsObj->boma_account_header_1_code_old,
                    'boma_account_header_1_name_old'          => $DummyClientReportTemplateAccountGroupsObj->boma_account_header_1_name_old,
                    'boma_account_header_2_code_old'          => $DummyClientReportTemplateAccountGroupsObj->boma_account_header_2_code_old,
                    'boma_account_header_2_name_old'          => $DummyClientReportTemplateAccountGroupsObj->boma_account_header_2_name_old,
                    'boma_account_header_3_code_old'          => $DummyClientReportTemplateAccountGroupsObj->boma_account_header_3_code_old,
                    'boma_account_header_3_name_old'          => $DummyClientReportTemplateAccountGroupsObj->boma_account_header_3_name_old,
                    'boma_account_header_4_code_old'          => $DummyClientReportTemplateAccountGroupsObj->boma_account_header_4_code_old,
                    'boma_account_header_4_name_old'          => $DummyClientReportTemplateAccountGroupsObj->boma_account_header_4_name_old,
                    'boma_account_header_5_code_old'          => $DummyClientReportTemplateAccountGroupsObj->boma_account_header_5_code_old,
                    'boma_account_header_5_name_old'          => $DummyClientReportTemplateAccountGroupsObj->boma_account_header_5_name_old,
                    'boma_account_header_6_code_old'          => $DummyClientReportTemplateAccountGroupsObj->boma_account_header_6_code_old,
                    'boma_account_header_6_name_old'          => $DummyClientReportTemplateAccountGroupsObj->boma_account_header_6_name_old,
                ]
            );
        }

        ReportTemplate::setSuspendValidation(false);
        ReportTemplateAccountGroup::setSuspendValidation(false);
        ReportTemplateMapping::setSuspendValidation(false);

        return $BomaBasedReportTemplateObj;
    }
}
