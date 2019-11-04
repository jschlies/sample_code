<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Events\PreCalcPropertyGroupsEvent;
use App\Waypoint\Models\PropertyGroup;
use Cache;
use Illuminate\Container\Container as Application;
use App\Waypoint\Models\User;
use DB;

/**
 * Class PropertyGroupRepository
 * @package App\Waypoint\Repositories
 */
class PropertyGroupRepository extends PropertyGroupRepositoryBase
{
    /**
     * PropertyRepository constructor.
     * @param \Illuminate\Container\Container $app
     * @throws \App\Waypoint\Exceptions\DeploymentException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return PropertyGroup
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        $PropertyGroupObj = parent::create($attributes);
        /**
         * deal with edge case of new property group
         */
        event(
            new PreCalcPropertyGroupsEvent(
                $PropertyGroupObj->client,
                [
                    'event_trigger_message'            => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($PropertyGroupObj),
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    =>
                        [
                            'property_groups' => [],
                        ],
                    'launch_job_property_group_id_arr' => [$PropertyGroupObj->id],
                ]
            )
        );
        return $PropertyGroupObj;
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param int $property_group_id
     * @return PropertyGroup
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(array $attributes, $property_group_id)
    {
        $PropertyGroupObj = parent::update($attributes, $property_group_id);
        Cache::tags('PropertyGroup_' . $PropertyGroupObj->client_id)->flush();

        return $PropertyGroupObj;
    }

    /**
     * Delete a entity in repository by id
     *
     * @param integer $property_group_id
     * @return bool
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function delete($property_group_id)
    {
        $PropertyGroupObj = $this->find($property_group_id);
        $result           = parent::delete($property_group_id);
        Cache::tags('PropertyGroup_' . $PropertyGroupObj->client_id)->flush();

        return $result;
    }

    /**
     * @param integer $property_group_id
     * @return App\Waypoint\Collection
     * @throws App\Waypoint\Exceptions\GeneralException
     */
    public function getAdvancedVarianceSummaryByPropertyGroupId($property_group_id, $as_of_month = null, $as_of_year = null)
    {
        /** @var PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj =
            $this->find($property_group_id);

        /** @var AdvancedVarianceRepository $AdvancedVarianceSummaryRepositoryObj */
        $AdvancedVarianceSummaryRepositoryObj = App::make(AdvancedVarianceSummaryRepository::class);

        if ($PropertyGroupObj->is_all_property_group)
        {
            $UserRepositoryObj = App::make(UserRepository::class);
            /** @var User $UserObj */
            $UserObj      = $UserRepositoryObj->find($PropertyGroupObj->user_id);
            $query_string = implode(',', $UserObj->getAccessiblePropertyObjArr()->pluck('id')->toArray());
            if ( ! $query_string)
            {
                return new App\Waypoint\Collection();
            }
            if ($as_of_month && $as_of_year)
            {
                $advanced_variance_id_result = DB::select(
                    "
                        SELECT 	advanced_variances.id
                            FROM advanced_variances
                                WHERE   advanced_variances.property_id IN (" . $query_string . ")	AND
                                        as_of_month = :AS_OF_MONTH AND
                                        as_of_year = :AS_OF_YEAR
                    ",
                    [
                        'AS_OF_MONTH' => $as_of_month,
                        'AS_OF_YEAR'  => $as_of_year,
                    ]
                );
            }
            else
            {
                $advanced_variance_id_result = DB::select(
                    "
                        SELECT 	advanced_variances.id
                            FROM advanced_variances
                                WHERE advanced_variances.property_id IN (" . $query_string . ")
                    "
                );
            }
        }
        else
        {
            if ($as_of_month && $as_of_year)
            {
                $advanced_variance_id_result = DB::select(
                    "
                        SELECT 	advanced_variances.id
                            FROM advanced_variances
                            JOIN properties ON advanced_variances.property_id = properties.id
                            JOIN property_group_properties ON property_group_properties.property_id = properties.id
                            JOIN property_groups ON property_group_properties.property_group_id = property_groups.id
                            WHERE   property_groups.id = :PROPERTY_GROUP_ID AND
                                    as_of_month = :AS_OF_MONTH AND
                                    as_of_year = :AS_OF_YEAR
                    ",
                    [
                        'PROPERTY_GROUP_ID' => $property_group_id,
                        'AS_OF_MONTH'       => $as_of_month,
                        'AS_OF_YEAR'        => $as_of_year,
                    ]
                );
            }
            else
            {
                $advanced_variance_id_result = DB::select(
                    "
                        SELECT 	advanced_variances.id
                            FROM advanced_variances
                            JOIN properties ON advanced_variances.property_id = properties.id
                            JOIN property_group_properties ON property_group_properties.property_id = properties.id
                            JOIN property_groups ON property_group_properties.property_group_id = property_groups.id
                            WHERE property_groups.id = :property_group_id
                    ",
                    [
                        'property_group_id' => $property_group_id,
                    ]
                );
            }
        }

        $advanced_variance_id_arr = array_unique(
            array_map(
                function ($value)
                {
                    return $value->id;
                },
                $advanced_variance_id_result
            )
        );

        /**
         * optimistically load relations but we need to trim our fat objects
         */
        return $AdvancedVarianceSummaryRepositoryObj
            ->with('advancedVarianceApprovals')
            ->with('advancedVarianceLineItemsSummary.nativeAccount.nativeAccountType')
            ->with('advancedVarianceLineItemsSummary.reportTemplateAccountGroup.nativeAccountType')
            ->with('advancedVarianceLineItemsSummary.comments')
            ->with('advancedVarianceLineItemsSlim.reportTemplateAccountGroup.nativeAccountType')
            ->with('advancedVarianceLineItemsSlim.nativeAccount.nativeAccountType')
            ->with('advancedVarianceLineItemsSlim.advancedVarianceSkinny')
            ->with('advancedVarianceLineItemsSlim.flaggerUser')
            ->with('property.client')
            ->findWhereIn('id', $advanced_variance_id_arr);
    }

    /**
     * @param $id
     * @param array $columns
     * @return PropertyGroup
     */
    public function find($id, $columns = ['*'])
    {
        return parent::find($id, $columns = ['*']);
    }
}
