<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use Exception;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\JsonResponse;
use App\Waypoint\Http\Requests\Generated\Api\CreateCustomReportTypeRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateCustomReportTypeRequest;
use App\Waypoint\Models\CustomReportType;
use App\Waypoint\Repositories\CustomReportTypeRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\ResponseUtil;
use Response;

/**
 * Class CustomReportTypeController
 */
class CustomReportTypeController extends BaseApiController
{
    /** @var  CustomReportTypeRepository */
    private $CustomReportTypeRepositoryObj;

    /**
     * CustomReportTypeController constructor.
     * @param CustomReportTypeRepository $CustomReportTypeRepositoryObj
     */
    public function __construct(CustomReportTypeRepository $CustomReportTypeRepositoryObj)
    {
        $this->CustomReportTypeRepositoryObj = $CustomReportTypeRepositoryObj;
        parent::__construct($CustomReportTypeRepositoryObj);
    }

    /**
     * Display a listing of the CustomReportType.
     * GET|HEAD /customReportTypes
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws Exception
     */
    public function index($client_id, Request $RequestObj)
    {
        $this->CustomReportTypeRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->CustomReportTypeRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $CustomReportTypeObjArr = $this->CustomReportTypeRepositoryObj->findWhere(
            [
                'client_id' => $client_id,
            ]
        );

        return $this->sendResponse($CustomReportTypeObjArr, 'CustomReportType(s) retrieved successfully');
    }

    /**
     * Store a newly created CustomReportType in storage.
     *
     * @param integer $client_id
     * @param CreateCustomReportTypeRequest $CustomReportTypeRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function store($client_id, CreateCustomReportTypeRequest $CustomReportTypeRequestObj)
    {
        $input = $CustomReportTypeRequestObj->all();

        if (isset($input['period_type']))
        {
            $input['period_type'] = strtolower($input['period_type']);
            $CustomReportTypeRequestObj->replace($input);
        }
        $CustomReportTypeObj = $this->CustomReportTypeRepositoryObj->create($input);

        return $this->sendResponse($CustomReportTypeObj, 'CustomReportType saved successfully');
    }

    /**
     * Display the specified CustomReportType.
     * GET|HEAD /customReportTypes/{id}
     *
     * @param integer $client_id
     * @param integer $custom_report_type_id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function show($client_id, $custom_report_type_id)
    {
        /** @var CustomReportType $customReportType */
        $CustomReportTypeObj = $this->CustomReportTypeRepositoryObj->findWithoutFail($custom_report_type_id);
        if (empty($CustomReportTypeObj))
        {
            return Response::json(ResponseUtil::makeError('CustomReportType not found'), 404);
        }
        return $this->sendResponse($CustomReportTypeObj, 'CustomReportType retrieved successfully');
    }

    /**
     * Update the specified CustomReportType in storage.
     * PUT/PATCH /customReportTypes/{id}
     *
     * @param integer $client_id
     * @param integer $custom_report_type_id
     * @param UpdateCustomReportTypeRequest $CustomReportTypeRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function update($client_id, $custom_report_type_id, UpdateCustomReportTypeRequest $CustomReportTypeRequestObj)
    {
        $input = $CustomReportTypeRequestObj->all();

        if (isset($input['period_type']))
        {
            $input['period_type'] = strtolower($input['period_type']);
            $CustomReportTypeRequestObj->replace($input);
        }

        /** @var CustomReportType $CustomReportTypeObj */
        $CustomReportTypeObj = $this->CustomReportTypeRepositoryObj->findWithoutFail($custom_report_type_id);
        if (empty($CustomReportTypeObj))
        {
            return Response::json(ResponseUtil::makeError('CustomReportType not found'), 404);
        }

        $CustomReportTypeObj = $this->CustomReportTypeRepositoryObj->update($input, $custom_report_type_id);

        return $this->sendResponse($CustomReportTypeObj, 'CustomReportType updated successfully');
    }

    /**
     * Remove the specified CustomReportType from storage.
     * DELETE /customReportTypes/{id}
     *
     * @param integer $client_id
     * @param integer $custom_report_type_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function destroy($client_id, $custom_report_type_id)
    {
        /** @var CustomReportType $CustomReportTypeObj */
        $CustomReportTypeObj = $this->CustomReportTypeRepositoryObj->findWithoutFail($custom_report_type_id);
        if (empty($CustomReportTypeObj))
        {
            return Response::json(ResponseUtil::makeError('CustomReportType not found'), 404);
        }
        $this->CustomReportTypeRepositoryObj->delete($custom_report_type_id);

        return $this->sendResponse($custom_report_type_id, 'CustomReportType deleted successfully');
    }
}
