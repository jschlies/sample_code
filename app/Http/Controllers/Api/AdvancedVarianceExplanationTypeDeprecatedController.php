<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\JsonResponse;

use App\Waypoint\Http\Requests\Generated\Api\CreateAdvancedVarianceExplanationTypeRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAdvancedVarianceExplanationTypeRequest;
use App\Waypoint\Models\AdvancedVarianceExplanationType;
use App\Waypoint\Repositories\AdvancedVarianceExplanationTypeRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AdvancedVarianceExplanationTypeController
 * @codeCoverageIgnore
 */
class AdvancedVarianceExplanationTypeDeprecatedController extends BaseApiController
{
    /** @var  AdvancedVarianceExplanationTypeRepository */
    private $AdvancedVarianceExplanationTypeRepositoryObj;

    public function __construct(AdvancedVarianceExplanationTypeRepository $AdvancedVarianceExplanationTypeRepositoryObj)
    {
        $this->AdvancedVarianceExplanationTypeRepositoryObj = $AdvancedVarianceExplanationTypeRepositoryObj;
        parent::__construct($AdvancedVarianceExplanationTypeRepositoryObj);
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $RequestObj, $client_id)
    {
        $this->AdvancedVarianceExplanationTypeRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AdvancedVarianceExplanationTypeRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AdvancedVarianceExplanationTypeObjArr = $this->AdvancedVarianceExplanationTypeRepositoryObj->findByField('client_id', $client_id);

        return $this->sendResponse($AdvancedVarianceExplanationTypeObjArr, 'AdvancedVarianceExplanationType(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $explanation_type_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $advanced_variance_explanation_type_arr)
    {
        $advanced_variance_explanation_type_arr = explode(',', $advanced_variance_explanation_type_arr);
        /** @var AdvancedVarianceExplanationType $AdvancedVarianceExplanationTypeObj */
        $AdvancedVarianceExplanationTypeObj = $this->AdvancedVarianceExplanationTypeRepositoryObj->findWhereIn('id', $advanced_variance_explanation_type_arr);
        if (empty($AdvancedVarianceExplanationTypeObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceExplanationType not found'), 404);
        }

        return $this->sendResponse($AdvancedVarianceExplanationTypeObj, 'AdvancedVarianceExplanationType retrieved successfully');
    }

    /**
     * @param CreateAdvancedVarianceExplanationTypeRequest $AdvancedVarianceExplanationTypeRequestObj
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function store(CreateAdvancedVarianceExplanationTypeRequest $AdvancedVarianceExplanationTypeRequestObj, $client_id)
    {
        $input              = $AdvancedVarianceExplanationTypeRequestObj->all();
        $input['client_id'] = $client_id;

        $AdvancedVarianceExplanationTypeObj = $this->AdvancedVarianceExplanationTypeRepositoryObj->create($input);

        return $this->sendResponse($AdvancedVarianceExplanationTypeObj, 'AdvancedVarianceExplanationType saved successfully');
    }

    /**
     * Update the specified AdvancedVarianceExplanationType in storage.
     * PUT/PATCH /advancedVarianceExplanationTypes/{id}
     *
     * @param integer $id
     * @param UpdateAdvancedVarianceExplanationTypeRequest $AdvancedVarianceExplanationTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function update($id, UpdateAdvancedVarianceExplanationTypeRequest $AdvancedVarianceExplanationTypeRequestObj)
    {
        $input = $AdvancedVarianceExplanationTypeRequestObj->all();
        /** @var AdvancedVarianceExplanationType $AdvancedVarianceExplanationTypeObj */
        $AdvancedVarianceExplanationTypeObj = $this->AdvancedVarianceExplanationTypeRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceExplanationTypeObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceExplanationType not found'), 404);
        }
        $AdvancedVarianceExplanationTypeObj = $this->AdvancedVarianceExplanationTypeRepositoryObj->update($input, $id);

        return $this->sendResponse($AdvancedVarianceExplanationTypeObj, 'AdvancedVarianceExplanationType updated successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $advanced_variance_explanation_type_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function destroy($client_id, $advanced_variance_explanation_type_id)
    {
        /** @var AdvancedVarianceExplanationType $AdvancedVarianceExplanationTypeObj */
        $AdvancedVarianceExplanationTypeObj = $this->AdvancedVarianceExplanationTypeRepositoryObj->findWithoutFail($advanced_variance_explanation_type_id);
        if (empty($AdvancedVarianceExplanationTypeObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceExplanationType not found'), 404);
        }
        $AdvancedVarianceExplanationTypeObj->delete();

        return $this->sendResponse($advanced_variance_explanation_type_id, 'AdvancedVarianceExplanationType deleted successfully');
    }
}
