<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Generated\Api\CreateRelatedUserTypeRequest;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\RelatedUserType;
use App\Waypoint\Repositories\RelatedUserTypeRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class RelatedUserTypePublicDeprecatedController
 * @codeCoverageIgnore
 */
class RelatedUserTypePublicDeprecatedController extends BaseApiController
{
    /** @var  RelatedUserTypeRepository */
    private $RelatedUserTypeRepositoryObj;

    public function __construct(RelatedUserTypeRepository $RelatedUserTypeRepositoryObj)
    {
        $this->RelatedUserTypeRepositoryObj = $RelatedUserTypeRepositoryObj;
        parent::__construct($RelatedUserTypeRepositoryObj);
    }

    /**
     * Display a listing of the RelatedUserType.
     * GET|HEAD /properties
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function index(Request $RequestObj, $client_id)
    {
        $this->RelatedUserTypeRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->RelatedUserTypeRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $RelatedUserTypeObjArr = $this->RelatedUserTypeRepositoryObj->findWhere(
            [
                'client_id' => $client_id,
            ]
        );

        return $this->sendResponse($RelatedUserTypeObjArr, 'RelatedUserType(s) retrieved successfully');
    }

    /**
     * Display a listing of the RelatedUserType.
     * GET|HEAD /properties
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function indexProperty(Request $RequestObj, $client_id)
    {
        $this->RelatedUserTypeRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->RelatedUserTypeRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $RelatedUserTypeObjArr = $this->RelatedUserTypeRepositoryObj->findWhere(
            [
                'client_id'           => $client_id,
                'related_object_type' => Property::class,
            ]
        );

        return $this->sendResponse($RelatedUserTypeObjArr, 'RelatedUserType(s) retrieved successfully');
    }

    /**
     * Display a listing of the RelatedUserType.
     * GET|HEAD /properties
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function indexOpportunity(Request $RequestObj, $client_id)
    {
        $this->RelatedUserTypeRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->RelatedUserTypeRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $RelatedUserTypeObjArr = $this->RelatedUserTypeRepositoryObj->findWhere(
            [
                'client_id'           => $client_id,
                'related_object_type' => Opportunity::class,
            ]
        );

        return $this->sendResponse($RelatedUserTypeObjArr, 'RelatedUserType(s) retrieved successfully');
    }

    /**
     * Store a newly created RelatedUserType in storage.
     *
     * @param CreateRelatedUserTypeRequest $RelatedUserTypeRequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @throws \Exception
     */
    public function store(CreateRelatedUserTypeRequest $RelatedUserTypeRequestObj)
    {
        $input              = $RelatedUserTypeRequestObj->all();
        $RelatedUserTypeObj = $this->RelatedUserTypeRepositoryObj->create($input);

        return $this->sendResponse($RelatedUserTypeObj, 'RelatedUserType saved successfully');
    }

    /**
     * @param CreateRelatedUserTypeRequest $RelatedUserTypeRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function storeProperty(CreateRelatedUserTypeRequest $RelatedUserTypeRequestObj)
    {
        $input                        = $RelatedUserTypeRequestObj->all();
        $input['related_object_type'] = Property::class;
        $RelatedUserTypeObj           = $this->RelatedUserTypeRepositoryObj->create($input);

        return $this->sendResponse($RelatedUserTypeObj, 'RelatedUserType saved successfully');
    }

    /**
     * @param CreateRelatedUserTypeRequest $RelatedUserTypeRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function storeOpportunity(CreateRelatedUserTypeRequest $RelatedUserTypeRequestObj)
    {
        $input                        = $RelatedUserTypeRequestObj->all();
        $input['related_object_type'] = Opportunity::class;
        $RelatedUserTypeObj           = $this->RelatedUserTypeRepositoryObj->create($input);

        return $this->sendResponse($RelatedUserTypeObj, 'RelatedUserType saved successfully');
    }

    /**
     * Remove the specified RelatedUserType from storage.
     * DELETE /properties/{id}
     *
     * @param integer $id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($client_id, $related_user_type_id)
    {
        /** @var RelatedUserType $RelatedUserTypeObj */
        $RelatedUserTypeObj = $this->RelatedUserTypeRepositoryObj->findWithoutFail($related_user_type_id);
        if (empty($RelatedUserTypeObj))
        {
            return Response::json(ResponseUtil::makeError('RelatedUserType not found'), 404);
        }
        $RelatedUserTypeObj->delete();

        return $this->sendResponse($related_user_type_id, 'RelatedUserType deleted successfully');
    }
}
