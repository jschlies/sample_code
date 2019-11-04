<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\PropertyGroupDetail;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\PropertyGroupDetailRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PropertyGroupDetailPublicController
 */
class PropertyGroupDetailController extends BaseApiController
{
    /** @var  PropertyGroupDetailRepository */
    private $PropertyGroupDetailRepositoryObj;

    public function __construct(PropertyGroupDetailRepository $PropertyGroupDetailRepositoryObj)
    {
        $this->PropertyGroupDetailRepositoryObj = $PropertyGroupDetailRepositoryObj;
        parent::__construct($PropertyGroupDetailRepositoryObj);
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $RequestObj, $client_id)
    {
        $this->PropertyGroupDetailRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PropertyGroupDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /** @var PropertyGroupDetail $PropertyGroupDetailObjArr */
        $PropertyGroupDetailObjArr = $this->PropertyGroupDetailRepositoryObj
            ->findWhere(['client_id' => $client_id]);

        return $this->sendResponse($PropertyGroupDetailObjArr, 'PropertyGroupDetail(s) retrieved successfully', [], [], []);
    }

    /**
     * @param integer $client_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $id)
    {
        /** @var PropertyGroupDetail $propertyGroup */
        $PropertyGroupDetailObj = $this->PropertyGroupDetailRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupDetailObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroupDetail not found'), 404);
        }

        return $this->sendResponse($PropertyGroupDetailObj, 'PropertyGroupDetail retrieved successfully', [], [], []);
    }

    /**
     * @param \Illuminate\Http\Request $RequestObj
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexForClient(Request $RequestObj, $client_id)
    {
        $this->PropertyGroupDetailRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PropertyGroupDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /** @var  Client $ClientObj */
        if ( ! $ClientObj = App::make(ClientRepository::class)->find($client_id))
        {
            throw new ModelNotFoundException('No such client');
        }

        return $this->sendResponse($ClientObj->propertyGroups, 'PropertyGroup(s) retrieved successfully', [], [], []);
    }
}
