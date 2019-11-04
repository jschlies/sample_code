<?php

namespace App\Waypoint\Repositories\Ledger;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Ledger\PropertyGroupCalcStatus;
use App\Waypoint\Repositories\ClientRepository;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class AdjustedRepository
 */
class PropertyGroupCalcStatusRepository extends LedgerRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [

    ];

    /**
     * @return mixed
     **/
    public function model()
    {
        return PropertyGroupCalcStatus::class;
    }

    /**
     * @param integer $client_id
     * @return Collection|array
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function getPropertyGroupCalcStatusArr($client_id)
    {
        if ( ! $ClientObj = App::make(ClientRepository::class)->find($client_id))
        {
            throw new ModelNotFoundException('no such client - client_id = ' . $client_id);
        }

        try
        {
            $GroupCalc_arr   = new Collection();
            $DBConnectionObj = DB::connection('mysql_GROUPS_FOR_CLIENT_' . $ClientObj->client_id_old);

            $where_arr   = [];
            $where_arr[] = ['FK_ACCOUNT_CLIENT_ID', $ClientObj->client_id_old];
            $where_arr[] = ['STATUS_DESCRIPTION', '!=', 'COMPLETED'];

            $group_calculation_client_table = 'GROUP_CALC_CLIENT_' . $ClientObj->client_id_old . '_MONTHLY_STATUS';
            $results                        = $DBConnectionObj->table($group_calculation_client_table)->where($where_arr)->select(
                "FK_ACCOUNT_CLIENT_ID", 'REF_GROUP_ID', 'STEP', 'STEP_DESCRIPTION', 'LINUX_SYSTEM_TIME', 'LOCAL_TIME', 'STATUS', 'STATUS_DESCRIPTION'
            )->get();

            foreach ($results as $result)
            {
                $result          = (array) $result;
                $GroupCalc_arr[] = new PropertyGroupCalcStatus($result);
            }

            $group_calculation_client_table = 'GROUP_CALC_CLIENT_' . $ClientObj->client_id_old . '_YEARLY_STATUS';
            $results                        = $DBConnectionObj->table($group_calculation_client_table)->where($where_arr)->select(
                "FK_ACCOUNT_CLIENT_ID", 'REF_GROUP_ID', 'STEP', 'STEP_DESCRIPTION', 'LINUX_SYSTEM_TIME', 'LOCAL_TIME', 'STATUS', 'STATUS_DESCRIPTION'
            )->get();

            foreach ($results as $result)
            {
                $result          = (array) $result;
                $GroupCalc_arr[] = new PropertyGroupCalcStatus($result);
            }

            /**
             * See HER-1677
             */
            $DBConnectionObj->disconnect();

            return $GroupCalc_arr;
        }
        catch (\Exception $e)
        {
            if (preg_match("/Unknown database/", $e->getMessage()))
            {
                return new Collection();
            }
            if (get_class($e) == \Illuminate\Database\QueryException::class)
            {
                if (preg_match("/Base table or view not found/", $e->getMessage()))
                {
                    /**
                     * at this point - per Peter B, we presume that group has never been run for $ClientObj->client_id_old and thus
                     * re return something to call group calc
                     */
                    return new Collection();
                }
            }
            throw new GeneralException($e->getMessage(), 403, $e);
        }
    }
}
