<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Generated\Api\UpdateClientRequest;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use App\Waypoint\Models\ClientDetail;
use App\Waypoint\Repositories\ClientDetailRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\ResponseUtil;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;
use stdClass;
use View;

/**
 * Class ClientDetailController
 * @codeCoverageIgnore
 */
class ClientDetailDeprecatedController extends BaseApiController
{
    /** @var  ClientDetailRepository */
    private $ClientDetailRepositoryObj;

    public function __construct(ClientDetailRepository $ClientDetailRepository)
    {
        $this->ClientDetailRepositoryObj = $ClientDetailRepository;
        parent::__construct($this->ClientDetailRepositoryObj);
    }

    /**
     * Display a listing of the ClientDetail.
     * GET|HEAD /clientDetails
     *
     * @param Request $Request
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $Request)
    {
        $this->ClientDetailRepositoryObj->pushCriteria(new RequestCriteria($Request));
        $this->ClientDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($Request));
        $ClientDetailObjArr = $this->ClientDetailRepositoryObj->all();

        return $this->sendResponse($ClientDetailObjArr, 'ClientDetail(s) retrieved successfully');
    }

    /**
     * Display the specified ClientDetail.
     * GET|HEAD /clientDetails/{id}
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse|null
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function show($id)
    {
        /** @var ClientDetail $ClientDetailObj */
        $ClientDetailObj = $this->ClientDetailRepositoryObj
            ->with('reportTemplates')
            ->with('advancedVarianceThresholds')
            ->find($id);
        if (empty($ClientDetailObj))
        {
            return Response::json(ResponseUtil::makeError('ClientDetail not found'), 404);
        }

        return $this->sendResponse($ClientDetailObj->toArray(), 'ClientDetail retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function indexForClient($client_id)
    {
        $ClientDetailObjArr = $this->ClientDetailRepositoryObj->findWhere(
            [
                'client_id' => $client_id,
            ]
        );
        if ( ! $ClientDetailObjArr->count())
        {
            return Response::json(ResponseUtil::makeError('ClientDetail(s) not found'), 404);
        }

        return $this->sendResponse($ClientDetailObjArr, 'ClientDetail(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param UpdateClientRequest $ClientRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws \Exception
     */
    public function updateClientConfig($client_id, UpdateClientRequest $ClientRequestObj)
    {
        if ( ! $ClientDetailsObj = $this->ClientDetailRepositoryObj->find($client_id))
        {
            return Response::json(ResponseUtil::makeError('ClientDetail(s) not found'), 404);
        }

        if (empty($ClientRequestObj->all()))
        {
            return Response::json(ResponseUtil::makeError('request payload empty'), 404);
        }

        foreach ($ClientRequestObj->all() as $config_name => $config_value)
        {
            $ClientDetailsObj->updateConfig($config_name, $config_value);
        }

        return $this->sendResponse($ClientRequestObj->toArray(), 'client config updated successfully');
    }

    /**
     * @param integer $client_id
     * @param UpdateClientRequest $ClientRequestObj
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse|null
     */
    public function renderPreCalcStatusClient($client_id, UpdateClientRequest $ClientRequestObj)
    {
        $pre_calc_status_arr  = collect_waypoint(
            DB::select(
                DB::raw(
                    '
                    select pre_calc_status.*
                        FROM pre_calc_status
                        WHERE pre_calc_status.client_id = :CLIENT_ID1 AND is_soiled
                    union
                    select pre_calc_status.*
                        FROM pre_calc_status
                        join properties on properties.id = pre_calc_status.property_id
                        WHERE properties.client_id = :CLIENT_ID2 AND is_soiled
                    union
                    select pre_calc_status.*
                        FROM pre_calc_status
                        join property_groups on property_groups.id = pre_calc_status.property_group_id
                        WHERE property_groups.client_id = :CLIENT_ID3 AND is_soiled
                    union
                    select pre_calc_status.*
                        FROM pre_calc_status
                        join users on users.id = pre_calc_status.user_id
                        WHERE users.client_id = :CLIENT_ID4 AND is_soiled
                 '
                ),
                [
                    'CLIENT_ID1' => $client_id,
                    'CLIENT_ID2' => $client_id,
                    'CLIENT_ID3' => $client_id,
                    'CLIENT_ID4' => $client_id,
                ]
            )
        );
        $client_count         = $pre_calc_status_arr->whereNotIn('client_id', [null])->count();
        $property_count       = $pre_calc_status_arr->whereNotIn('property_id', [null])->count();
        $property_group_count = $pre_calc_status_arr->whereNotIn('property_group_id', [null])->count();
        $user_count           = $pre_calc_status_arr->whereNotIn('property_group_id', [null])->count();
        $pre_calc_status_arr->each(
            function (stdClass $pre_calc_status)
            {
                $pre_calc_status->one_day_old   = null;
                $pre_calc_status->one_week_old  = null;
                $pre_calc_status->two_weeks_old = null;
                if (Carbon::createFromFormat('Y-m-d H:i:s', $pre_calc_status->updated_at)
                          ->diffInDays(Carbon::now()) >= 1)
                {
                    $pre_calc_status->one_day_old = 'X';
                }
                elseif (Carbon::createFromFormat('Y-m-d H:i:s', $pre_calc_status->updated_at)
                              ->diffInDays(Carbon::now()) >= 7)
                {
                    $pre_calc_status->one_day_old  = 'X';
                    $pre_calc_status->one_week_old = 'X';
                }
                elseif (Carbon::createFromFormat('Y-m-d H:i:s', $pre_calc_status->updated_at)
                              ->diffInDays(Carbon::now()) >= 14)
                {
                    $pre_calc_status->one_day_old   = 'X';
                    $pre_calc_status->one_week_old  = 'X';
                    $pre_calc_status->two_weeks_old = 'X';
                }
            }
        );

        $pre_calc_status_arr = $pre_calc_status_arr->map(
            function (stdClass $pre_calc_status)
            {
                return stdToArray($pre_calc_status);
            }
        );

        if ($pre_calc_status_arr->count())
        {
            $pre_calc_status_arr = $pre_calc_status_arr->toArray();
            return View::make(
                'pages.pre_calc_status',
                [
                    'keys'                 => array_keys($pre_calc_status_arr[0]),
                    'pre_calc_status_arr'  => $pre_calc_status_arr,
                    'client_count'         => $client_count,
                    'property_count'       => $property_count,
                    'property_group_count' => $property_group_count,
                    'user_count'           => $user_count,
                ]
            );
        }
        return $this->sendResponse([], 'No soiled items found');
    }
}
