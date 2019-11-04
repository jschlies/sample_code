<?php

namespace App\Waypoint\Repositories\Ledger;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Ledger\RentRoll;
use App\Waypoint\Repositories\PropertyRepository;
use Carbon\Carbon as Carbon;
use DB;
use function collect_waypoint;
use Exception;
use Illuminate\Container\Container as Application;
use Log;

class RentRollRepository extends LedgerRepository
{
    private $PropertyRepositoryObj;
    public $PropertyObj;
    private $payload;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->PropertyRepositoryObj = App::make(PropertyRepository::class);
    }

    /**
     * @param int $property_id
     * @param array $native_account_codes_array
     * @param Carbon $date
     * @param bool $quarterly
     * @return array
     */
    public function getRentRoll(int $property_id): App\Waypoint\Collection
    {
        if (empty($property_id))
        {
            throw new GeneralException('missing property id' . ' ' . __FILE__ . ':' . __LINE__);
        }

        if ( ! $this->PropertyObj = $this->PropertyRepositoryObj->find($property_id))
        {
            throw new GeneralException('Could not find property from property_id = ' . $property_id . ' ' . __FILE__ . ':' . __LINE__);
        }

        $RentRollResultArrArr = collect_waypoint($this->performQueryToStaging());

        $RentRollObjArr = new App\Waypoint\Collection();
        foreach ($RentRollResultArrArr as $RentRollResultArr)
        {
            if (
                ! $RentRollResultArr->AS_OF_DATE ||
                ! preg_match("/^(20|19)\d\d\-\d\d\-\d\d/", $RentRollResultArr->AS_OF_DATE)

            )
            {
                /**
                 * default to now()
                 */
                $RentRollResultArr->AS_OF_DATE = Carbon::create()->format('Y-m-d H:i:s');
            }
            if (
                ! $RentRollResultArr->LEASE_FROM ||
                ! preg_match("/^(20|19)\d\d\-\d\d\-\d\d/", $RentRollResultArr->LEASE_FROM)

            )
            {
                /**
                 * with resent changes to staging server, this is unreachable but ya know, just in case
                 */
                $RentRollResultArr->LEASE_FROM = null;
            }
            if (
                ! $RentRollResultArr->LEASE_TO ||
                ! preg_match("/^(20|19)\d\d\-\d\d\-\d\d/", $RentRollResultArr->LEASE_TO)

            )
            {
                /**
                 * with resent changes to staging server, this is unreachable but ya know, just in case
                 */
                $RentRollResultArr->LEASE_TO = null;
            }

            $this->payload = [
                'rent_roll_id'           => $RentRollResultArr->RENT_ROLL_ID,
                'property_name'          => $RentRollResultArr->PROPERTY_NAME,
                'property_code'          => $RentRollResultArr->PROPERTY_CODE,
                'as_of_date'             => $RentRollResultArr->AS_OF_DATE,
                'original_property_code' => $RentRollResultArr->ORIGINAL_PROPERTY_CODE,
                'tenant_industry'        => $RentRollResultArr->TENANT_INDUSTRY,
                'rent_unit_id'           => $RentRollResultArr->RENT_UNIT_ID,
                'suite_id_code'          => $RentRollResultArr->UNIT_NO,
                'suite_name'             => $RentRollResultArr->UNIT_NO,
                'lease_id_code'          => isset($RentRollResultArr->LEASE_ID) ? $RentRollResultArr->LEASE_ID : $RentRollResultArr->LEASE_NAME,
                'lease_name'             => isset($RentRollResultArr->LEASE_NAME) ? $RentRollResultArr->LEASE_NAME : 'Unknown',
                'lease_type'             => $RentRollResultArr->LEASE_TYPE,
                'square_footage'         => $RentRollResultArr->AREA_C ? $RentRollResultArr->AREA_C : 0,
                'lease_start_date'       => $RentRollResultArr->LEASE_FROM,
                'lease_expiration_date'  => $RentRollResultArr->LEASE_TO,
                'lease_term'             => $RentRollResultArr->TERM,
                'tenancy_year'           => $RentRollResultArr->TENANCY_YEAR,
                'monthly_rent'           => $RentRollResultArr->MONTHLY_RENT,
                'monthly_rent_area'      => $RentRollResultArr->MONTHLY_RENT_AREA,
                'annual_rent'            => $RentRollResultArr->ANNUAL_RENT,
                'annual_rent_area'       => $RentRollResultArr->ANNUAL_RENT_AREA,
                'security_deposit'       => $RentRollResultArr->SECURITY_DEPOSIT,
                'letter_cr_amt'          => $RentRollResultArr->LETTER_CR_AMT,
                'updated_datetime'       => isset($RentRollResultArr->UPDATED_DATETIME) ? $RentRollResultArr->UPDATED_DATETIME : null,
                'raw_upload'             => json_encode($RentRollResultArr),
            ];

            try
            {
                $RentRollObj = new App\Waypoint\Models\Ledger\RentRoll($this->payload);
                $RentRollObj->validate();
            }
            catch (GeneralException $e)
            {
                Log::warning('Rent Roll record invalid ' . print_r($this->payload, 1) . ' ' . __FILE__ . ':' . __LINE__);
                continue;
            }
            catch (Exception $e)
            {
                Log::warning('Rent Roll record invalid ' . print_r($this->payload, 1) . ' ' . __FILE__ . ':' . __LINE__);
                continue;
            }
            $RentRollObjArr[] = $RentRollObj;
        }
        return $RentRollObjArr;
    }

    /**
     * @return []
     * @throws GeneralException
     */
    protected function performQueryToStaging()
    {
        $property_codes_array          = explode(',', $this->PropertyObj->property_code);
        $original_property_codes_array = explode(',', $this->PropertyObj->original_property_code);

        $this->ClientObj                = $this->PropertyObj->client;
        $property_codes_array_as_string = "'" . implode("','", array_unique(array_merge($property_codes_array, $original_property_codes_array))) . "'";

        $results = $this
            ->getStagingDatabaseConnection()
            ->select(
            /**
             * DO NOT use binding for $property_codes_array_as_string. Quirk in MySql/PDO
             */
                DB::raw(
                    "SELECT * FROM RENT_ROLL
                        JOIN RENT_ROLL_UNIT on RENT_ROLL_UNIT.FK_RENT_ROLL_ID = RENT_ROLL.RENT_ROLL_ID
                        WHERE (
                                PROPERTY_CODE IN (" . $property_codes_array_as_string . ")
                                OR
                                ORIGINAL_PROPERTY_CODE IN (" . $property_codes_array_as_string . ")
                )
                            AND RENT_ROLL_UNIT.UPDATED_DATETIME > :PROCESSED_THROUGH_DATE
                            AND AS_OF_DATE != '1970-01-01'
                            AND (RENT_ROLL_UNIT.LEASE_TO != '1970-01-01' OR RENT_ROLL_UNIT.LEASE_TO IS NULL)
                        ORDER BY RENT_ROLL_UNIT.RENT_UNIT_ID
                    "
                ),
                [
                    'PROCESSED_THROUGH_DATE' => $this->getProcessedThroughDate(),
                ]
            );

        return $results;
    }

    /**
     * @param $enable_query_log bool
     * @return \Illuminate\Database\Connection|null
     */
    public function getStagingDatabaseConnection(bool $enable_query_log = false)
    {
        if ( ! $this->ClientObj)
        {
            throw new GeneralException('unusable client object' . ' ' . __FILE__ . ':' . __LINE__);
        }
        if ($this->StagingDatabaseConnectionObj)
        {
            if ($enable_query_log)
            {
                $this->StagingDatabaseConnectionObj->enableQueryLog();
                return $this->StagingDatabaseConnectionObj;
            }
            return $this->StagingDatabaseConnectionObj;
        }
        return $this->setStagingDatabaseConnection($enable_query_log);
    }

    /**
     * @param $enable_query_log bool
     * @return \Illuminate\Database\Connection|null
     */
    protected function setStagingDatabaseConnection(bool $enable_query_log = false)
    {
        if ( ! $this->ClientObj)
        {
            throw new GeneralException('unusable client object' . ' ' . __FILE__ . ':' . __LINE__);
        }

        try
        {
            $this->StagingDatabaseConnectionObj = DB::connection('mysql_WAYPOINT_STAGING_FOR_CLIENT_' . $this->ClientObj->client_id_old);
            $this->StagingDatabaseConnectionObj->getPdo();
            if ($enable_query_log)
            {
                $this->StagingDatabaseConnectionObj->enableQueryLog();
            }
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('could not find staging database - client_id_old = ' . $this->ClientObj->client_id_old . ' ' . __FILE__ . ':' . __LINE__);
        }
        return $this->StagingDatabaseConnectionObj;
    }

    /**
     * @return mixed
     **/
    public function model()
    {
        return RentRoll::class;
    }

    /**
     * @return string
     * @throws \InvalidArgumentException
     */
    private function getProcessedThroughDate()
    {
        $results = DB::select(
            DB::raw(
                "
                    SELECT
                        max(updated_datetime) as processed_through_date
                        FROM lease_schedules
                        WHERE

                            property_id = :PROPERTY_ID
                "
            ),
            [
                'PROPERTY_ID' => $this->PropertyObj->id,
            ]
        );

        if ($results[0]->processed_through_date)
        {
            $processed_through_date =
                Carbon::createFromFormat('Y-m-d H:i:s', $results[0]->processed_through_date)
                      ->toDateTimeString();
        }
        else
        {
            $processed_through_date = Carbon::create(1970, 1, 1, 0, 0, 0)->toDateTimeString();
        }
        return $processed_through_date;
    }
}
