<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Requests\Generated\Api\CreateAccessListRequest;
use App\Waypoint\Models\AccessListDetail;
use App\Waypoint\Models\Property;
use App\Waypoint\Repositories\AccessListDetailRepository;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\ResponseUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Validator\Exceptions\ValidatorException;
use Response;

/**
 * Class AccessListDetailDeprecatedController
 * @codeCoverageIgnore
 */
class AccessListDetailDeprecatedController extends BaseApiController
{
    /** @var  AccessListDetailRepository */
    private $AccessListDetailRepositoryObj;
    /** @var  UserRepository */
    private $UserRepositoryObj;

    /**
     * AccessListDetailController constructor.
     * @param AccessListDetailRepository $AccessListDetailRepository
     */
    public function __construct(AccessListDetailRepository $AccessListDetailRepository)
    {
        $this->AccessListDetailRepositoryObj = $AccessListDetailRepository;
        $this->UserRepositoryObj             = App::make(UserRepository::class);
        parent::__construct($AccessListDetailRepository);
    }

    /**
     * @param integer $access_list_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function show($access_list_id)
    {
        /** @var AccessListDetail $AccessListDetailObj */
        $AccessListDetailObj =
            $this->AccessListDetailRepositoryObj
                ->with('accessListUsers')
                ->with('accessListProperties')
                ->findWithoutFail($access_list_id);

        return $this->sendResponse($AccessListDetailObj, 'AccessListDetail retrieved successfully');
    }

    /**
     * @param Request $request
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function getAccessListDetailForClient(Request $request, $client_id)
    {
        $this->AccessListDetailRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->AccessListDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));
        $AccessListDetailObjArr =
            $this->AccessListDetailRepositoryObj
                ->with('accessListUsers')
                ->with('accessListProperties')
                ->findWhere(
                    [
                        'client_id' => $client_id,
                    ]
                );
        return $this->sendResponse($AccessListDetailObjArr, 'AccessListDetail(s) retrieved successfully');
    }

    /**
     * @param integer $user_id
     * @return JsonResponse|null
     * @throws GeneralException
     *
     * @todo Huh???
     */
    public function getAccessListDetailForUser($user_id)
    {
        $UserObj =
            App::make(UserRepository::class)
               ->with('accessListDetails.accessListProperties')
               ->with('accessListDetails.accessListUsers')
               ->find($user_id);

        return $this->sendResponse($UserObj->accessListDetails->toArray(), 'AccessListDetail(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function getAccessListDetailForProperty($client_id, $property_id)
    {
        /** @var Property $PropertyObj */
        $PropertyObj =
            App::make(PropertyRepository::class)
               ->with('accessListDetails.accessListUsers')
               ->with('accessListDetails.accessListProperties')
               ->find($property_id);
        return $this->sendResponse($PropertyObj->accessListDetails->toArray(), 'AccessListDetail(s) retrieved successfully');
    }

    /**
     * @param integer $user_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function getAccessiblePropertiesForUser($user_id)
    {

        $AccessiblePropertyObjArr =
            App::make(PropertyRepository::class)
               ->getUserAccessiblePropertyObjArr($user_id);

        return $this->sendResponse($AccessiblePropertyObjArr, 'AccessibleProperty(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function getAccessibleUsersForProperty($client_id, $property_id)
    {
        /** @var Property $PropertyObj */
        $PropertyObj =
            App::make(PropertyRepository::class)
               ->find($property_id);
        return $this->sendResponse($PropertyObj->getAccessibleUserObjArr()->toArray(), 'AccessibleUser(s) retrieved successfully');
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexForClient(Request $RequestObj, $client_id)
    {
        $this->AccessListDetailRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AccessListDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AccessListObjArr = $this->AccessListDetailRepositoryObj->findWhere(
            [
                'client_id' => $client_id,
            ]
        );
        if ( ! $AccessListObjArr->count())
        {
            return Response::json(ResponseUtil::makeError('AccessList(s) not found for client'), 400);
        }

        return $this->sendResponse($AccessListObjArr->toArray(), 'AccessList(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $access_list_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function showAudits($client_id, $access_list_id)
    {
        /** @var AccessListDetail $AccessListDetailObj */
        $AccessListDetailObj = $this->AccessListDetailRepositoryObj->find($access_list_id);
        if (empty($AccessListDetailObj))
        {
            return Response::json(ResponseUtil::makeError('AccessListDetail not found'), 404);
        }

        return $this->sendResponse($AccessListDetailObj->getAuditArr(), 'AccessListDetail audits retrieved successfully');
    }

    /**
     * Store a newly created AccessList in storage.
     *
     * @param CreateAccessListRequest $CreateAccessListRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function store(CreateAccessListRequest $CreateAccessListRequestObj)
    {
        $input               = $CreateAccessListRequestObj->all();
        $AccessListDetailObj = $this->AccessListDetailRepositoryObj->create($input);

        return $this->sendResponse($AccessListDetailObj->toArray(), 'AccessListDetail saved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $access_list_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($client_id, $access_list_id)
    {
        /** @var AccessListDetail $AccessListDetailObj */
        $AccessListDetailObj = $this->AccessListDetailRepositoryObj->findWithoutFail($access_list_id);
        if (empty($AccessListDetailObj))
        {
            return Response::json(ResponseUtil::makeError('AccessList not found'), 404);
        }
        $AccessListDetailObj->delete();

        return $this->sendResponse($access_list_id, 'AccessList deleted successfully');
    }
}
