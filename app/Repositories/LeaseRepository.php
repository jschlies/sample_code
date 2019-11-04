<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\LeaseSchedule;
use App\Waypoint\Models\Ledger\RentRoll;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\Suite;
use App\Waypoint\Models\SuiteLease;
use App\Waypoint\Models\Tenant;
use App\Waypoint\Models\TenantIndustry;
use App\Waypoint\Repositories\Ledger\RentRollRepository;
use App\Waypoint\Tests\Mocks\RentRollMockRepository;
use Carbon\Carbon;
use Exception;
use Rollbar\Payload\Level;

/**
 * Class LeaseRepository
 * @package App\Waypoint\Repositories
 */
class LeaseRepository extends LeaseRepositoryBase
{
    /** @var RentRollRepository|RentRollMockRepository */
    static $RentRollRepositoryObj;

    /**
     * @return mixed
     */
    public static function getRentRollRepositoryObj()
    {
        if ( ! self::$RentRollRepositoryObj)
        {
            self::$RentRollRepositoryObj = App::make(RentRollRepository::class);
        }
        return self::$RentRollRepositoryObj;
    }

    /**
     * @param mixed $RentRollRepositoryObj
     */
    public static function setRentRollRepositoryObj($RentRollRepositoryObj): void
    {
        self::$RentRollRepositoryObj = $RentRollRepositoryObj;
    }

    /**
     * @param integer $property_id
     * @return mixed
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function upload_leases_for_property($property_id, $how_far_back_date = null)
    {
        /** @var PropertyRepository $PropertyRepositoryObj */
        /** @var  RentRollRepository|RentRollMockRepository $RentRollRepositoryObj */
        $PropertyRepositoryObj = App::make(PropertyRepository::class);
        $RentRollRepositoryObj = self::getRentRollRepositoryObj();

        $RentRollObjArr =
            $RentRollRepositoryObj
                ->getRentRoll($property_id)
                ->sortBy(function ($RentRollObj)
                {
                    return
                        ($RentRollObj->lease_start_date ?: Carbon::create(1970, 1, 1, 0, 0, 0)->format('Y-m-d H:i:s')) .
                        ($RentRollObj->lease_expiration_date ?: Carbon::create(2525, 1, 1, 0, 0, 0)->format('Y-m-d H:i:s'));
                });

        /**
         * Regarding dates
         * - $RentRollObj->as_of_date - now() if unreadable data received from staging in  $RentRollRepositoryObjObj->getRentRoll()
         * - $RentRollObj->lease_start_date - null if unreadable data received from staging in  $RentRollRepositoryObjObj->getRentRoll()
         * - $RentRollObj->lease_expiration_date - null if unreadable data received from staging in  $RentRollRepositoryObjObj->getRentRoll()
         */
        try
        {
            /** @var Property $PropertyObj */
            $PropertyObj = $PropertyRepositoryObj
                ->with('suites')
                ->with('leases.suites')
                ->find($property_id);
            /** @var RentRoll $RentRollObj */
            /** @var Lease $LeaseObj */
            foreach ($RentRollObjArr as $RentRollObj)
            {
                /**
                 * since we may encounter the same lease in the same run of this script....
                 */
                if ($RentRollObj->lease_id_code == null || strtoupper($RentRollObj->lease_id_code) == 'VACANT')
                {
                    $LeaseObj = null;
                }
                else
                {
                    $LeaseObj = $this->processLease($RentRollObj, $PropertyObj);
                }

                /**
                 * if we find a suite_id_code, create/update a suite
                 */
                if ($RentRollObj->suite_id_code == null || ! $RentRollObj->suite_id_code)
                {
                    $SuiteObj = null;
                }
                else
                {
                    $SuiteObj = $this->processSuite($RentRollObj, $PropertyObj);
                }

                /**
                 * if we find a lease_name, create/update a tenant
                 */
                if ($RentRollObj->lease_name == null || ! $RentRollObj->lease_name || strtoupper($RentRollObj->lease_name) == 'VACANT')
                {
                    $TenantObj = null;
                }
                else
                {
                    $this->processTenant($RentRollObj, $PropertyObj, $LeaseObj, $SuiteObj);
                }

                /**
                 * good, now that we have a $LeaseObj, a $SuiteObj
                 * lets make sure about the ties that bind
                 */
                if ($LeaseObj && $SuiteObj)
                {
                    $this->processSuiteLease($SuiteObj, $LeaseObj);
                    /**
                     * only processLeaseSchedule if both suite and lease exist
                     */
                    $this->processLeaseSchedule($RentRollObj, $PropertyObj, $SuiteObj, $LeaseObj);
                }
                else
                {
                    $this->logToGraylog(
                        Level::ALERT,
                        'Unable to detect suite/lease from RentRoll ' . print_r($RentRollObj->toArray(), 1) . ' continuing to process'
                    );
                }
            }

            return $PropertyObj->refresh()->leaseDetails;
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('upload_leases_for_property failed' . __FILE__ . ':' . __LINE__, 500, $e);
        }
    }

    /**
     * @param RentRoll $RentRollObj
     * @param Property $PropertyObj
     * @return Lease|null
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    private function processLease(RentRoll $RentRollObj, Property $PropertyObj)
    {
        /** @var Lease $LeaseObj */
        if ($LeaseObj = $PropertyObj->load('leases')->leases->filter(
            function ($LeaseObj) use ($RentRollObj, $PropertyObj)
            {
                /**
                 * See https://stackoverflow.com/questions/6448825/sql-unique-varchar-case-sensitivity-question
                 */
                return strtolower($LeaseObj->lease_id_code) == strtolower($RentRollObj->lease_id_code) &&
                       $LeaseObj->property_id == $PropertyObj->id;
            }
        )->first())
        {
            if (strtoupper($RentRollObj->lease_id_code) == 'VACANT')
            {
                /**
                 * per HER-3207
                 */
                $this->delete($LeaseObj->id);
                return null;
            }
            else
            {
                /**
                 * update the lease if needed
                 */
                $attributes = $RentRollObj->toArray();
                $LeaseObj   = $this->update($attributes, $LeaseObj->id);
            }
        }
        else
        {
            /**
             * ok, create it
             */
            $attributes                = $RentRollObj->toArray();
            $attributes['property_id'] = $PropertyObj->id;
            $LeaseObj                  = $this->create($attributes);
        }
        return $LeaseObj;
    }

    /**
     * @param RentRoll $RentRollObj
     * @param Property $PropertyObj
     * @return Suite
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    private function processSuite(RentRoll $RentRollObj, Property $PropertyObj): Suite
    {
        $SuiteRepositoryObj = App::make(SuiteRepository::class);
        /**
         * now see suite exists, if no,
         * make it so
         */
        /** @var Suite $SuiteObj */
        if ($SuiteObj = $PropertyObj->load('suites')->suites->filter(
            function ($SuiteObj) use ($RentRollObj, $PropertyObj)
            {
                return
                    strtolower($SuiteObj->suite_id_code) == strtolower($RentRollObj->suite_id_code) &&
                    strtolower($SuiteObj->original_property_code) == strtolower($RentRollObj->original_property_code) &&
                    $SuiteObj->property_id == $PropertyObj->id;
            }
        )->first())
        {
            $SuiteObj = $SuiteRepositoryObj->update(
                [
                    'suite_id_code'   => $RentRollObj->suite_id_code,
                    'suite_id_number' => $RentRollObj->suite_id_code,
                    'name'            => $RentRollObj->suite_id_code,
                    'square_footage'  => $RentRollObj->square_footage,
                ],
                $SuiteObj->id
            );

        }
        else
        {
            $SuiteObj = $SuiteRepositoryObj->create(
                [
                    'property_id'            => $PropertyObj->id,
                    'suite_id_code'          => $RentRollObj->suite_id_code,
                    'suite_id_number'        => $RentRollObj->suite_id_code,
                    'name'                   => $RentRollObj->suite_id_code,
                    'square_footage'         => $RentRollObj->square_footage,
                    'original_property_code' => $RentRollObj->original_property_code,
                ]
            );
        }
        return $SuiteObj;
    }

    /**
     * @param RentRoll $RentRollObj
     * @param Property $PropertyObj
     * @return Suite
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    private function processTenant(RentRoll $RentRollObj, Property $PropertyObj, $LeaseObj, $SuiteObj): Tenant
    {
        $TenantRepositoryObj      = App::make(TenantRepository::class);
        $LeaseTenantRepositoryObj = App::make(LeaseTenantRepository::class);
        $SuiteTenantRepositoryObj = App::make(SuiteTenantRepository::class);

        $TenantIndustryObj = $this->processTenantImdustry($RentRollObj, $PropertyObj);

        /**
         * now see Tenant exists, if no,
         * make it so
         */
        /** @var Tenant $TenantObj */
        if ($TenantObj = $TenantRepositoryObj->findWhere(
            [
                'name'      => $RentRollObj->lease_name,
                'client_id' => $PropertyObj->client_id,
            ]
        )->first()
        )
        {

            $TenantObj = $TenantRepositoryObj->update(
                [
                    'tenant_industry_id' => $TenantIndustryObj->id,
                ],
                $TenantObj->id
            );
        }
        else
        {
            $TenantObj = $TenantRepositoryObj->create(
                [
                    'client_id'          => $PropertyObj->client_id,
                    'name'               => $RentRollObj->lease_name,
                    'tenant_industry_id' => $TenantIndustryObj->id,
                ]
            );
        }

        if ($LeaseObj)
        {
            /**
             * tie tenant to lease
             */
            if ( ! $LeaseTenantRepositoryObj->findWhere(
                [
                    'lease_id'  => $LeaseObj->id,
                    'tenant_id' => $TenantObj->id,
                ]
            )->first()
            )
            {
                $LeaseTenantRepositoryObj->create(
                    [
                        'lease_id'  => $LeaseObj->id,
                        'tenant_id' => $TenantObj->id,
                    ]
                );
            }
        }

        if ($SuiteObj)
        {
            /**
             * tie tenant to suite
             */
            if ( ! $SuiteTenantRepositoryObj->findWhere(
                [
                    'suite_id'  => $SuiteObj->id,
                    'tenant_id' => $TenantObj->id,
                ]
            )->first()
            )
            {
                $SuiteTenantRepositoryObj->create(
                    [
                        'suite_id'  => $SuiteObj->id,
                        'tenant_id' => $TenantObj->id,
                    ]
                );
            }
        }
        return $TenantObj;
    }

    /**
     * @param RentRoll $RentRollObj
     * @param Property $PropertyObj
     * @return TenantIndustry
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    private function processTenantImdustry(RentRoll $RentRollObj, Property $PropertyObj): TenantIndustry
    {
        if ( ! empty($RentRollObj->tenant_industry))
        {
            if (
            ! $TenantIndustryObj = TenantIndustry::where('client_id', '=', $PropertyObj->client_id)
                                                 ->where('name', '=', $RentRollObj->tenant_industry)
                                                 ->get()->first()
            )
            {
                $TenantIndustryObj = App::make(TenantIndustryRepository::class)->create(
                    [
                        'name'      => $RentRollObj->tenant_industry,
                        'client_id' => $PropertyObj->client_id,
                    ]
                );

            }
        }
        else
        {
            $TenantIndustryObj =
                TenantIndustry::where('client_id', '=', $PropertyObj->client_id)
                              ->where('name', '=', App\Waypoint\Models\TenantIndustry::TENANT_TYPE_DEFAULT)
                              ->get()->first();
        }

        return $TenantIndustryObj;
    }

    /**
     * @param Suite $SuiteObj
     * @param Lease $LeaseObj
     * @return SuiteLease
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    private function processSuiteLease(Suite $SuiteObj, Lease $LeaseObj): SuiteLease
    {
        /** @var SuiteLeaseRepository $SuiteLeaseRepositoryObj */
        $SuiteLeaseRepositoryObj = App::make(SuiteLeaseRepository::class);
        /**
         * suite_lease
         * if no connection .....
         */
        if ( ! $SuiteLeaseObj = $SuiteLeaseRepositoryObj->findWhere(
            [
                'suite_id' => $SuiteObj->id,
                'lease_id' => $LeaseObj->id,
            ]
        )->first())
        {
            /**
             * and tie it to the lease
             */
            return $SuiteLeaseRepositoryObj->create(
                [
                    'suite_id' => $SuiteObj->id,
                    'lease_id' => $LeaseObj->id,
                ]
            );
        }
        return $SuiteLeaseObj;
    }

    /**
     * @param RentRoll $RentRollObj
     * @param Property $PropertyObj
     * @param null $SuiteObj
     * @param null $LeaseObj
     * @return LeaseSchedule
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    private function processLeaseSchedule(RentRoll $RentRollObj, Property $PropertyObj, $SuiteObj = null, $LeaseObj = null): LeaseSchedule
    {
        /** @var LeaseScheduleRepository $LeaseScheduleRepositoryObj */
        $LeaseScheduleRepositoryObj = App::make(LeaseScheduleRepository::class);

        /**
         * now that we have a lease, add inbound data to LeaseSchedule
         */
        $attributes                = $RentRollObj->toArray();
        $attributes['lease_id']    = $LeaseObj ? $LeaseObj->id : null;
        $attributes['suite_id']    = $SuiteObj ? $SuiteObj->id : null;
        $attributes['property_id'] = $PropertyObj->id;

        return $LeaseScheduleRepositoryObj->create($attributes);
    }
}
