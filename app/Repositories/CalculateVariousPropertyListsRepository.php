<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\AllRepositoryTrait;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Models\AccessListUser;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\PropertyGroupProperty;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\Ledger\BenchmarkGenerationDateRepository;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Container\Container as Application;
use Log;
use Prettus\Validator\Exceptions\ValidatorException;
use Throwable;

/**
 * Class CalculateVariousPropertyListsRepository
 * @package App\Waypoint\Repositories
 */
class CalculateVariousPropertyListsRepository extends PropertyGroupRepository
{
    use AllRepositoryTrait;
    /** @var Client */
    public $ClientObj;
    /** @var  bool */
    public $need_to_trigger_property_group_calc_status = false;
    /** @var string */
    private $benchmark_generation_date;
    /** @var string */
    private $benchmark_peer_calc_date;

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
     * @param integer $client_id
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function CalculateVariousPropertyListsJobProcessor($client_id, $skinny = false)
    {
        /**
         * what's the point??
         */
        if ($client_id == 1)
        {
            return;
        }

        if ( ! $this->ClientObj = Client::find($client_id))
        {
            Log::error('Unknown $client_id = ' . $client_id . ' at ' . __CLASS__ . ':' . __LINE__);
            return;
        }

        $this->benchmark_generation_date = null;
        $this->benchmark_peer_calc_date  = null;
        $this->client_id                 = $client_id;

        $this->ensure_client_all_access_list();
        $this->ClientObj->refresh();

        $this->ensure_user_all_property_groups();
        $this->ClientObj->refresh();

        $this->deal_with_sq_ft();
        $this->ClientObj->refresh();

        $this->deal_with_md5();
        $this->ClientObj->refresh();

        /**
         * did something change
         */
        if ($this->need_to_trigger_property_group_calc_status)
        {
            $this->trigger_property_group_calc_status();
        }
    }

    /**
     * ensure that:
     *  - this client has one and only 1 'All Access List'
     *  - ensure 'All Access List' contains ALL client properties
     *  - ensure that all admins are on 'All Access List'
     */
    private function ensure_client_all_access_list()
    {
        $AccessListRepositoryObj = App::make(AccessListRepository::class);
        if ( ! $this->ClientObj->allAccessList)
        {
            /** @var User $ClientGenericUserObj */
            $this->ClientObj->setRelation('allAccessList',
                                          $AccessListRepositoryObj->create(
                                              [
                                                  'client_id'          => $this->ClientObj->id,
                                                  'name'               => '* All Access List for ' . $this->ClientObj->name,
                                                  'description'        => '* All Access List for ' . $this->ClientObj->name,
                                                  'is_all_access_list' => true,
                                              ]
                                          ));
        }

        /** @var Property $PropertyObj */
        $all_access_property_id_arr = $this->ClientObj->allAccessList->properties->pluck('id')->toArray();
        $bulk_update_arr            = [];
        foreach ($this->ClientObj->properties as $PropertyObj)
        {
            if ( ! in_array($PropertyObj->id, $all_access_property_id_arr))
            {
                $bulk_update_arr [] = [
                    'access_list_id' => $this->ClientObj->allAccessList->id,
                    'property_id'    => $PropertyObj->id,
                ];
            }
        }
        if ($bulk_update_arr)
        {
            AccessListProperty::insert($bulk_update_arr);
        }

        $bulk_update_arr = [];
        /** @var User $UserObj */
        foreach ($this->ClientObj->users as $UserObj)
        {
            if ($UserObj->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
            {
                if ( ! in_array($UserObj->id, $this->ClientObj->allAccessList->users->pluck('id')->toArray()))
                {
                    $bulk_update_arr[] = [
                        'access_list_id' => $this->ClientObj->allAccessList->id,
                        'user_id'        => $UserObj->id,
                    ];
                }
            }
        }
        /**
         * now see if something was deleted
         */
        if ($bulk_update_arr)
        {
            AccessListUser::insert($bulk_update_arr);
        }
    }

    private function ensure_user_all_property_groups()
    {
        $all_access_list_users = $this->ClientObj->allAccessList->accessListUsers->pluck('id')->toArray();
        /** @var User $UserObj */
        foreach ($this->ClientObj->users as $UserObj)
        {
            if ( ! $UserObj->allPropertyGroup)
            {
                /**
                 * make sure user has a 'AllProperty Group
                 */
                $UserObj->setRelation(
                    'allPropertyGroup',
                    $this->create(
                        [
                            'name'                  => 'All Property Group for ' . $UserObj->email . ' ' . $UserObj->id,
                            'client_id'             => $UserObj->client_id,
                            'user_id'               => $UserObj->id,
                            'is_all_property_group' => true,
                        ]
                    )
                );
            }
            if ($UserObj->active_status !== User::ACTIVE_STATUS_ACTIVE)
            {
                continue;
            }

            $existing_property_ids_in_allPropertyGroup = array_values($UserObj->allPropertyGroup->properties->pluck('id')->toArray());
            if (
                $UserObj->isAdmin() ||
                in_array($UserObj->id, $all_access_list_users)
            )
            {
                $bulk_update_arr = [];

                $accessable_properties_id_dedup = [];

                foreach ($this->ClientObj->properties as $PropertyObj)
                {
                    /**
                     * make sure user has a properly outfitted 'AllProperty Group
                     */
                    if (
                        ! in_array($PropertyObj->id, $existing_property_ids_in_allPropertyGroup) &&
                        ! isset($accessable_properties_id_dedup[$PropertyObj->id][$UserObj->allPropertyGroup->id])
                    )
                    {
                        $bulk_update_arr[]                                                                = [
                            'property_group_id' => $UserObj->allPropertyGroup->id,
                            'property_id'       => $PropertyObj->id,
                        ];
                        $accessable_properties_id_dedup[$PropertyObj->id][$UserObj->allPropertyGroup->id] = true;
                    }
                }
                if (count($bulk_update_arr))
                {
                    PropertyGroupProperty::insert($bulk_update_arr);
                }
            }
            else
            {
                /**
                 * deal with non-admins
                 */
                $accessable_properties_id_arr =
                    array_values($UserObj->accessLists
                                     ->map(
                                         function (AccessList $AccessListObj)
                                         {
                                             return $AccessListObj->accessListProperties->pluck('property_id');
                                         }
                                     )->flatten()
                                     ->toArray());

                $bulk_update_arr = [];

                try
                {
                    $accessable_properties_id_dedup = [];
                    foreach ($accessable_properties_id_arr as $accessable_properties_id)
                    {
                        /**
                         * make sure user has a properly outfitted 'AllProperty Group
                         */
                        if (
                            ! in_array($accessable_properties_id, $existing_property_ids_in_allPropertyGroup) &&
                            ! isset($accessable_properties_id_dedup[$accessable_properties_id][$UserObj->allPropertyGroup->id])
                        )
                        {
                            $bulk_update_arr[] = [
                                'property_group_id' => $UserObj->allPropertyGroup->id,
                                'property_id'       => $accessable_properties_id,
                            ];

                            $accessable_properties_id_dedup[$accessable_properties_id][$UserObj->allPropertyGroup->id] = 1;
                        }
                    }

                    if (count($bulk_update_arr))
                    {
                        foreach (array_chunk($bulk_update_arr, 200) as $bulk_update_arr_chunk)
                        {
                            PropertyGroupProperty::insert($bulk_update_arr_chunk);
                        }
                    }
                    $UserObj->refresh();;
                    /**
                     * now for my next trick, delete all propertyGroupProperties from this users allPropertyGroup that ARE NOT IN
                     */
                    foreach ($UserObj->allPropertyGroup->propertyGroupProperties as $PropertyGroupPropertyObj)
                    {
                        if ( ! in_array($PropertyGroupPropertyObj->property_id, $accessable_properties_id_arr))
                        {
                            $PropertyGroupPropertyObj->delete();
                        }
                    }
                }
                catch (GeneralException $e)
                {
                    throw $e;
                }
                catch (Exception $e)
                {
                    throw new GeneralException(__CLASS__ . ' Event ');
                }

                $UserObj->allPropertyGroup->refresh();
                /**
                 * perhaps access was removed
                 */
                foreach ($UserObj->allPropertyGroup->propertyGroupProperties as $PropertyGroupPropertiesObj)
                {
                    if ( ! in_array($PropertyGroupPropertiesObj->property_id, $accessable_properties_id_arr))
                    {
                        PropertyGroupProperty::destroy($PropertyGroupPropertiesObj->id);
                    }
                }
            }

            unset($UserObj);
        }
    }

    private function deal_with_sq_ft()
    {
        DB::update(
            "
                UPDATE property_groups
                    SET property_groups.total_square_footage = (
                        IFNULL (
                            (
                                SELECT
                                    sum(properties.square_footage) AS square_footage 
                                    FROM property_group_properties, properties
                                    WHERE
                                        property_groups.id = property_group_properties.property_group_id AND
                                        properties.id = property_group_properties.property_id 
                            ),0
                        )
                    )  
                    WHERE
                        property_groups.client_id = :CLIENT_ID 
             ",
            [
                'CLIENT_ID' => $this->ClientObj->id,
            ]
        );
    }

    private function deal_with_md5()
    {
        $need_to_trigger_property_group_calc_status = false;

        if ( ! $benchmark_generation_date = $this->getBenchmarkGenerationDate())
        {
            return;
        }
        if ( ! $benchmark_peer_calc_date = $this->getBenchmarkPeerCalcDate())
        {
            return;
        }

        /**
         * deal with clients that client_id_old is null
         */
        if ( ! $this->ClientObj->client_id_old)
        {
            DB::update(
                "  
                    UPDATE property_groups
                        SET property_groups.property_id_md5 = null
                            where property_groups.client_id = :CLIENT_ID
                ",
                [
                    'CLIENT_ID' => $this->ClientObj->id,
                ]
            );
            return;
        }

        /**
         * @todo this whole loop could be done in one update query
         */
        /** @var PropertyGroup $PropertyGroupObj */
        foreach ($this->ClientObj->propertyGroups as $PropertyGroupObj)
        {
            /**
             * Get $propertyGroup_total_md5
             */
            if ($PropertyGroupObj->propertyGroupProperties->count() == 0)
            {
                $property_group_total_md5 = null;
            }
            else
            {
                $property_group_total_md5 = md5(
                    implode(',', $PropertyGroupObj->propertyGroupProperties->pluck('property_id')->toArray()) .
                    'GoPackers' .
                    $benchmark_generation_date .
                    $benchmark_peer_calc_date,
                    false
                );
            }

            if ($PropertyGroupObj->property_id_md5 !== $property_group_total_md5)
            {
                $need_to_trigger_property_group_calc_status = true;
                DB::update(
                    "  
                        UPDATE property_groups
                            SET property_id_md5 = :PROPERTY_ID_MD5
                                where property_groups.id = :PROPERTY_GROUP_ID
                    ",
                    [
                        'PROPERTY_ID_MD5'   => $property_group_total_md5,
                        'PROPERTY_GROUP_ID' => $PropertyGroupObj->id,
                    ]
                );

            }
        }

        if ($need_to_trigger_property_group_calc_status)
        {
            $this->need_to_trigger_property_group_calc_status = true;
        }
        else
        {
            $this->need_to_trigger_property_group_calc_status = false;
        }

        return;
    }

    /**
     * @throws ValidatorException
     */
    public function trigger_property_group_calc_status()
    {
        /** @var ClientRepository $ClientRepositoryObj */
        $ClientRepositoryObj = App::make(ClientRepository::class)->setSuppressEvents(true);
        $ClientRepositoryObj->update(
            [
                'property_group_calc_status'         => Client::PROPERTY_GROUP_CALC_STATUS_WAITING,
                'property_group_calc_last_requested' => Carbon::now(),
            ],
            $this->ClientObj->id
        );
    }

    /**
     * @param integer $client_id
     * @return string
     * @throws GeneralException
     */
    public function getBenchmarkGenerationDate()
    {
        if ($this->benchmark_generation_date)
        {
            return $this->benchmark_generation_date;
        }

        try
        {
            /** @var BenchmarkGenerationDateRepository $BenchmarkGenerationDateRepositoryObj */
            $BenchmarkGenerationDateRepositoryObj = App::make(BenchmarkGenerationDateRepository::class);
            if ( ! $this->ClientObj->client_id_old)
            {
                if ( ! defined('PHPUNIT_COMPOSER_INSTALL'))
                {
                    return Carbon::today()->format(('Y-m-d H:i:s'));
                }
                else
                {
                    return Carbon::today()->format(('Y-m-d'));
                }
            }
            elseif ( ! $this->benchmark_generation_date = $BenchmarkGenerationDateRepositoryObj->getBenchmarkGenerationDate($this->client_id))
            {
                if ( ! defined('PHPUNIT_COMPOSER_INSTALL'))
                {
                    return Carbon::today()->format(('Y-m-d H:i:s'));
                }
                else
                {
                    return Carbon::today()->format(('Y-m-d'));
                }
            }
        }
        catch (GeneralException $e)
        {
            return false;
        }
        catch (Exception $e)
        {
            return false;
        }
        catch (Throwable $e)
        {
            return false;
        }

        if ($this->benchmark_generation_date)
        {
            return $this->benchmark_generation_date;
        }

        return false;
    }

    /**
     * @param integer $client_id
     * @return string
     * @throws GeneralException
     */
    public function getBenchmarkPeerCalcDate()
    {
        if ($this->benchmark_peer_calc_date)
        {
            return $this->benchmark_peer_calc_date;
        }

        try
        {
            /** @var BenchmarkGenerationDateRepository $BenchmarkGenerationDateRepositoryObj */
            $BenchmarkGenerationDateRepositoryObj = App::make(BenchmarkGenerationDateRepository::class);
            if ( ! $this->ClientObj->client_id_old)
            {
                if ( ! defined('PHPUNIT_COMPOSER_INSTALL'))
                {
                    return Carbon::today()->format(('Y-m-d H:i:s'));
                }
                else
                {
                    return Carbon::today()->format(('Y-m-d'));
                }
            }
            elseif ( ! $this->benchmark_peer_calc_date = $BenchmarkGenerationDateRepositoryObj->getBenchmarkPeerCalcTimeStamp($this->client_id))
            {
                if ( ! defined('PHPUNIT_COMPOSER_INSTALL'))
                {
                    return Carbon::today()->format(('Y-m-d H:i:s'));
                }
                else
                {
                    return Carbon::today()->format(('Y-m-d'));
                }
            }
        }
        catch (GeneralException $e)
        {
            return false;
        }
        catch (Exception $e)
        {
            return false;
        }
        catch (Throwable $e)
        {
            return false;
        }

        if ($this->benchmark_peer_calc_date)
        {
            return $this->benchmark_peer_calc_date;
        }

        return false;
    }
}
