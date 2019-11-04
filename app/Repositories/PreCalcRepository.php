<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\AllRepositoryTrait;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\GetPropertySuitesMetadataTrait;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\User;
use App\Waypoint\WeightedAverageLeaseExpirationTrait;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;
use Log;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class PreCalcRepository
 * @package App\Waypoint\Repositories
 */
class PreCalcRepository extends PropertyGroupRepository
{
    use AllRepositoryTrait;
    use GetPropertySuitesMetadataTrait;
    use WeightedAverageLeaseExpirationTrait;

    /**
     * PropertyRepository constructor.
     * @param \Illuminate\Container\Container $app
     * @throws \App\Waypoint\Exceptions\DeploymentException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->loadAllRepositories(true);
    }

    /**
     * @param $client_id
     */
    public function PreCalcClientJobProcessor($client_id)
    {
        /**
         * many events kick this off, not just PreCalc*Events
         */
        if ( ! $ClientObj = Client::find($client_id))
        {
            Log::error('Unknown $client_id = ' . $client_id . ' at ' . __CLASS__ . ':' . __LINE__);
            return;
        }

        if (
            $ClientObj->suppress_pre_calc_events() ||
            $ClientObj->suppress_pre_calc_usage()
        )
        {
            return;
        }

        if (
            $ClientObj->suppress_pre_calc_events() ||
            $ClientObj->suppress_pre_calc_usage()
        )
        {
            return;
        }

        if ($ClientObj->suppress_pre_calc_usage())
        {
            return null;
        }

        $this->build_client_pre_calcs($ClientObj);
    }

    /**
     * @param integer $user_id
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function PreCalcUsersJobProcessor($user_id)
    {
        if ( ! $UserObj = User::with('relatedUsers')->with('client.users')->find($user_id))
        {
            Log::error('Unknown user_id = ' . $user_id . ' at ' . __CLASS__ . ':' . __LINE__);
            return;
        }

        if (
            $UserObj->suppress_pre_calc_events() ||
            $UserObj->suppress_pre_calc_usage()
        )
        {
            return;
        }

        $this->build_user_pre_calcs($UserObj);
    }

    /**
     * @param integer $client_id
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function PreCalcPropertiesJobProcessor($property_id)
    {
        /** @var Property $PropertyObj */
        if ( ! $PropertyObj = Property::with('advancedVarianceSummaries')->with('advancedVariances')->with('assetType')->find($property_id))
        {
            Log::error('Unknown $property_id = ' . $property_id . ' at ' . __CLASS__ . ':' . __LINE__);
            return;
        }

        if (
            $PropertyObj->suppress_pre_calc_events() ||
            $PropertyObj->suppress_pre_calc_usage()
        )
        {
            return;
        }
        $this->build_property_pre_calcs($PropertyObj);
    }

    /**
     * @param $property_group_id
     * @throws GeneralException
     */
    public function PreCalcPropertyGroupsJobProcessor($property_group_id)
    {
        if ( ! $PropertyGroupObj = PropertyGroup::with('properties.advancedVariances')
                                                ->with('propertyGroupProperties')
                                                ->with('client.relatedUserTypesSlimForProperty')
                                                ->find($property_group_id))
        {
            Log::error('Unknown $property_group_id = ' . $property_group_id . ' at ' . __CLASS__ . ':' . __LINE__);
            return;
        }

        if (
            $PropertyGroupObj->suppress_pre_calc_events() ||
            $PropertyGroupObj->suppress_pre_calc_usage()
        )
        {
            return;
        }
        $this->build_property_groups_pre_calcs($PropertyGroupObj);
    }

    /**
     * @param Client $ClientObj
     * @throws GeneralException
     */
    public function build_client_pre_calcs(Client $ClientObj)
    {
        $ReportTemplateFullRepositoryObj = App::make(ReportTemplateFullRepository::class);
        /**
         * relatedUserTypes_client_
         */
        $key = 'relatedUserTypes_client_' . $ClientObj->id;
        if ( ! $ClientObj->getPreCalcValue($key, true))
        {
            $ClientObj->updatePreCalcValue(
                $key,
                $ClientObj->getRelatedUserTypes()->toArray()
            );
        }

        /**
         * standard_attribute_unique_values_client_
         */
        $key = 'standard_attribute_unique_values_client_' . $ClientObj->id;
        if ( ! $ClientObj->getPreCalcValue($key, true))
        {
            $ClientObj->updatePreCalcValue(
                $key,
                $ClientObj->getStandardAttributeUniqueValues()
            );
        }

        /**
         * custom_attribute_unique_values_client_
         */
        $key = 'custom_attribute_unique_values_client_' . $ClientObj->id;
        if ( ! $ClientObj->getPreCalcValue($key, true))
        {
            $ClientObj->updatePreCalcValue(
                $key,
                $ClientObj->getCustomAttributeUniqueValues()
            );
        }

        /**
         * defaultAdvancedVarianceThresholds_
         */
        $key = 'defaultAdvancedVarianceThresholds_' . $ClientObj->id;
        if ( ! $ClientObj->getPreCalcValue($key, true))
        {
            $defaultAdvancedVarianceThresholds = $ClientObj->getDefaultAdvancedVarianceThresholds()->toArray();
            $ClientObj->updatePreCalcValue(
                $key,
                $defaultAdvancedVarianceThresholds
            );
        }

        /**
         * report_template_full_arr_client_
         */
        foreach ($ClientObj->reportTemplates as $ReportTemplateObj)
        {
            $key = 'report_template_full_arr_client_' . $ClientObj->id . '_report_template_' . $ReportTemplateObj->id;
            if ( ! $ClientObj->getPreCalcValue($key, true))
            {
                $ReportTemplateFullObj = $ReportTemplateFullRepositoryObj
                    ->with('reportTemplateAccountGroupsChildrenFull.reportTemplateMappingsFull.nativeAccountDetail.nativeAccountTypeDetail.nativeAccountTypeTrailers')
                    ->find($ReportTemplateObj->id);

                $report_template_full_arr = collect_waypoint([$ReportTemplateFullObj->toArray()])->toArray();

                $ClientObj->updatePreCalcValue(
                    $key,
                    $report_template_full_arr
                );
            }
        }
    }

    /**
     * @param User $UserObj
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \BadMethodCallException
     */
    public function build_user_pre_calcs(User $UserObj)
    {
        if (
            $UserObj->active_status == User::ACTIVE_STATUS_INACTIVE ||
            $UserObj->user_invitation_status == User::USER_INVITATION_STATUS_PENDING
        )
        {
            return;
        }

        /**
         * related_users_user_
         */
        $key = 'related_users_user_' . $UserObj->id;
        if ( ! $UserObj->getPreCalcValue($key, true))
        {
            $UserObj->updatePreCalcValue(
                $key,
                $UserObj->relatedUsers->toArray()
            );
        }

        /**
         * assetTypesOfProperties_user_
         */
        $key = 'assetTypesOfProperties_user_' . $UserObj->id;
        if ( ! $UserObj->getPreCalcValue($key, true))
        {
            $UserObj->updatePreCalcValue(
                $key,
                $UserObj->getAssetTypesOfAccessibleProperties()
            );

            if ($UserObj->isAdmin())
            {
                $s3_file = $UserObj->readPreCalcTable($key)->s3_location;
                /** @var User $InnerUserObj */
                foreach ($UserObj->client->users as $InnerUserObj)
                {
                    if ($InnerUserObj->isAdmin())
                    {
                        if ( ! $InnerUserObj->getPreCalcValue($key, true))
                        {
                            $key = 'assetTypesOfProperties_user_' . $InnerUserObj->id;
                            $InnerUserObj->updatePreCalcTable($key, $s3_file);
                        }
                    }
                }
            }
        }

        /**
         * accessiblePropertyGroups_user_
         */
        $key = 'accessiblePropertyGroups_user_' . $UserObj->id;
        if ( ! $UserObj->getPreCalcValue($key, true))
        {
            $UserObj->updatePreCalcValue(
                $key,
                $UserObj->getAccessiblePropertyGroupObjArr()->toArray()
            );

            if ($UserObj->isAdmin())
            {
                $s3_file = $UserObj->readPreCalcTable($key)->s3_location;
                /** @var User $InnerUserObj */
                foreach ($UserObj->client->users as $InnerUserObj)
                {
                    if ($InnerUserObj->isAdmin())
                    {
                        if ( ! $InnerUserObj->getPreCalcValue($key, true))
                        {
                            $key = 'assetTypesOfProperties_user_' . $InnerUserObj->id;
                            $InnerUserObj->updatePreCalcTable($key, $s3_file);
                        }
                    }
                }
            }
        }

        /**
         * accessible_property_arr_user_
         */
        $key = 'accessible_property_arr_user_' . $UserObj->id;
        if ( ! $UserObj->getPreCalcValue($key, true))
        {
            $UserObj->updatePreCalcValue(
                $key,
                $UserObj->getAccessiblePropertyObjArr()->toArray()
            );

            if ($UserObj->isAdmin())
            {
                $s3_file = $UserObj->readPreCalcTable($key)->s3_location;
                /** @var User $InnerUserObj */
                foreach ($UserObj->client->users as $InnerUserObj)
                {
                    if ($InnerUserObj->isAdmin())
                    {
                        if ( ! $InnerUserObj->getPreCalcValue($key, true))
                        {
                            $key = 'accessible_property_arr_user_' . $InnerUserObj->id;
                            $InnerUserObj->updatePreCalcTable($key, $s3_file);
                        }
                    }
                }
            }
        }

        /**
         * standardAttributesOfProperties_user_
         */
        $key = 'standardAttributesOfProperties_user_' . $UserObj->id;
        if ( ! $UserObj->getPreCalcValue($key, true))
        {
            $UserObj->updatePreCalcValue(
                $key,
                $UserObj->getStandardAttributesOfAccessibleProperties()
            );

            if ($UserObj->isAdmin())
            {
                $s3_file = $UserObj->readPreCalcTable($key)->s3_location;
                /** @var User $InnerUserObj */
                foreach ($UserObj->client->users as $InnerUserObj)
                {
                    if ($InnerUserObj->isAdmin())
                    {
                        if ( ! $InnerUserObj->getPreCalcValue($key, true))
                        {
                            $key = 'standardAttributesOfProperties_user_' . $InnerUserObj->id;
                            $InnerUserObj->updatePreCalcTable($key, $s3_file);
                        }
                    }
                }
            }
        }
        /**
         * customAttributesOfProperties_user_
         */
        $key = 'customAttributesOfProperties_user_' . $UserObj->id;
        if ( ! $UserObj->getPreCalcValue($key, true))
        {
            $UserObj->updatePreCalcValue(
                $key,
                $UserObj->getCustomAttributesOfAccessibleProperties()
            );

            if ($UserObj->isAdmin())
            {
                $s3_file = $UserObj->readPreCalcTable($key)->s3_location;
                /** @var User $InnerUserObj */
                foreach ($UserObj->client->users as $InnerUserObj)
                {
                    if ($InnerUserObj->isAdmin())
                    {
                        if ( ! $InnerUserObj->getPreCalcValue($key, true))
                        {
                            $key = 'customAttributesOfProperties_user_' . $InnerUserObj->id;
                            $InnerUserObj->updatePreCalcTable($key, $s3_file);
                        }
                    }
                }
            }
        }

        $key             = 'user_detail_user_' . $UserObj->id;
        $user_detail_arr = $UserObj->getPreCalcValue($key, true);
        if ( ! $user_detail_arr)
        {
            /** @var User $user */
            $UserDetailObj = $this->UserDetailRepositoryObj
                ->with('client.properties.assetType')
                ->with('userInvitations')
                ->with('relatedUsers')
                ->with('accessLists.properties.assetType')
                ->findWithoutFail($UserObj->id);

            $user_detail_arr = $UserDetailObj->toArray();
            $UserDetailObj->updatePreCalcValue(
                $key,
                $user_detail_arr
            );
        }
    }

    /**
     * @throws \Exception
     */
    public function build_property_pre_calcs(Property $PropertyObj)
    {
        /**
         * relatedUserTypes_property_
         */
        $key = 'relatedUserTypes_property_' . $PropertyObj->id;
        /** @var Property $PropertyObj */
        if ( ! $PropertyObj->getPreCalcValue($key, true))
        {
            $relatedUserTypes = $PropertyObj->getRelatedUserTypes(Property::class, $PropertyObj->id)->toArray();
            $PropertyObj->updatePreCalcValue(
                $key,
                $relatedUserTypes
            );
        }

        /**
         * advancedVarianceSummaries_property_
         */
        $GroupedAllAdvancedVarianceObjArr =
            $PropertyObj->advancedVariances
                ->groupBy(
                    function (AdvancedVariance $AdvancedVarianceObj)
                    {
                        return $AdvancedVarianceObj->as_of_month . '_' . $AdvancedVarianceObj->as_of_year;
                    }
                );
        /**
         * remember $key is of format MM_YYYY
         */
        foreach ($GroupedAllAdvancedVarianceObjArr as $key => $data)
        {
            preg_match("/^(\d*)_(\d*)$/", $key, $gleaned);
            $as_of_month = sprintf('%02d', $gleaned[1]);
            $as_of_year  = $gleaned[2];
            $key         = 'advancedVarianceSummaries_property_' . $PropertyObj->id . '_' . $as_of_year . '_' . $as_of_month;
            if ( ! $PropertyObj->getPreCalcValue($key, true))
            {
                $advancedVarianceSummaries = $PropertyObj->advancedVarianceSummaries
                    ->where('as_of_year', '=', $as_of_year)
                    ->where('as_of_month', '=', $as_of_month
                    )->toArray();
                $PropertyObj->updatePreCalcValue(
                    $key,
                    $advancedVarianceSummaries
                );
            }
        }

        $key = 'advancedVarianceSummaries_property_' . $PropertyObj->id;
        if ( ! $PropertyObj->getPreCalcValue($key, true))
        {
            $advancedVarianceSummaries = $PropertyObj->advancedVarianceSummaries->toArray();
            $PropertyObj->updatePreCalcValue(
                $key,
                $advancedVarianceSummaries
            );
        }
    }

    /**
     * @param PropertyGroup $PropertyGroupObj
     * @throws \Exception
     */
    public function build_property_groups_pre_calcs(PropertyGroup $PropertyGroupObj)
    {
        $GroupedAllAdvancedVarianceObjArr =
            $PropertyGroupObj->properties
                ->map(
                    function (Property $PropertyObj)
                    {
                        return $PropertyObj->advancedVariances;
                    }
                )
                ->flatten()
                ->groupBy(
                    function (AdvancedVariance $AdvancedVarianceObj, $key)
                    {
                        return $AdvancedVarianceObj->as_of_month . '_' . $AdvancedVarianceObj->as_of_year;
                    }
                );

        /**
         * remember $key is of format MM_YYYY
         */
        foreach ($GroupedAllAdvancedVarianceObjArr as $key => $data)
        {
            preg_match("/^(\d*)_(\d*)$/", $key, $gleaned);
            $as_of_month = sprintf('%02d', $gleaned[1]);
            $as_of_year  = $gleaned[2];

            /**
             * skip is_all_property_group of inactive users
             */
            if (
                $PropertyGroupObj->is_all_property_group &&
                (
                    $PropertyGroupObj->user->active_status == User::ACTIVE_STATUS_INACTIVE ||
                    $PropertyGroupObj->user->user_invitation_status == User::USER_INVITATION_STATUS_PENDING
                )
            )
            {
                continue;
            }
            /**
             * skip is_all_property_group of never logged in
             */
            if (
                $PropertyGroupObj->is_all_property_group &&
                ! $PropertyGroupObj->user->first_login_date)
            {
                continue;
            }
            $AsOfDateObj = Carbon::create($as_of_year, $as_of_month, 1, 0, 0, 0);
            $key         = 'AdvancedVarianceSummaryByPropertyGroupId_' . $AsOfDateObj->format('Y') . '_' . $AsOfDateObj->format('m') . '_' . $PropertyGroupObj->id;
            if ( ! $PropertyGroupObj->getPreCalcValue($key, true))
            {
                $AdvancedVarianceSummaryObjArr = $this->PropertyGroupRepositoryObj
                    ->getAdvancedVarianceSummaryByPropertyGroupId(
                        $PropertyGroupObj->id,
                        $as_of_month,
                        $as_of_year
                    );

                $PropertyGroupObj->updatePreCalcValue(
                    $key,
                    $AdvancedVarianceSummaryObjArr->toArray()
                );
            }

        }

        /**
         * unique_advanced_variance_dates_property_group_
         */
        $key = 'unique_advanced_variance_dates_property_group_' . $PropertyGroupObj->id;
        if ( ! $PropertyGroupObj->getPreCalcValue($key, true))
        {
            $unique_advanced_variance_dates_property_group =
                $this->AdvancedVarianceRepositoryObj->get_unique_advanced_variance_dates_property_group($PropertyGroupObj->client_id, $PropertyGroupObj->id);

            $PropertyGroupObj->updatePreCalcValue(
                $key,
                $unique_advanced_variance_dates_property_group->toArray()
            );
        }

        /**
         * AdvancedVarianceSummaryByPropertyGroupId_
         */
        $key = 'AdvancedVarianceSummaryByPropertyGroupId_' . $PropertyGroupObj->id;
        if ( ! $PropertyGroupObj->getPreCalcValue($key, true))
        {
            $AdvancedVarianceSummaryObjArr = $this->PropertyGroupRepositoryObj
                ->getAdvancedVarianceSummaryByPropertyGroupId(
                    $PropertyGroupObj->id
                );

            $PropertyGroupObj->updatePreCalcValue(
                $key,
                $AdvancedVarianceSummaryObjArr->toArray()
            );
        }
    }
}
