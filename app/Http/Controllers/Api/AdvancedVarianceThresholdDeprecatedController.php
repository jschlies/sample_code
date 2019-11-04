<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Generated\Api\UpdateAdvancedVarianceThresholdRequest;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\JsonResponse;

use App\Waypoint\Http\Requests\Generated\Api\CreateAdvancedVarianceThresholdRequest;
use App\Waypoint\Models\AdvancedVarianceThreshold;
use App\Waypoint\Repositories\AdvancedVarianceThresholdRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AdvancedVarianceThresholdDeprecatedController
 * @codeCoverageIgnore
 */
class AdvancedVarianceThresholdDeprecatedController extends BaseApiController
{
    /** @var  AdvancedVarianceThresholdRepository */
    private $AdvancedVarianceThresholdRepositoryObj;

    public function __construct(AdvancedVarianceThresholdRepository $AdvancedVarianceThresholdRepositoryObj)
    {
        $this->AdvancedVarianceThresholdRepositoryObj = $AdvancedVarianceThresholdRepositoryObj;
        parent::__construct($AdvancedVarianceThresholdRepositoryObj);
    }

    /**
     * Display a listing of the AdvancedVarianceThreshold.
     * GET|HEAD /advancedVarianceThresholds
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function index(Request $RequestObj, $client_id)
    {
        $this->AdvancedVarianceThresholdRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AdvancedVarianceThresholdRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AdvancedVarianceThresholdObjArr = $this->AdvancedVarianceThresholdRepositoryObj->findWhere(
            [
                'client_id' => $client_id,
            ]
        );

        return $this->sendResponse($AdvancedVarianceThresholdObjArr, 'AdvancedVarianceThreshold(s) retrieved successfully');
    }

    /**
     * @param CreateAdvancedVarianceThresholdRequest $AdvancedVarianceThresholdRequestObj
     * @param $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function store(CreateAdvancedVarianceThresholdRequest $AdvancedVarianceThresholdRequestObj, $client_id)
    {
        $input = $AdvancedVarianceThresholdRequestObj->all();

        $AdvancedVarianceThresholdObj = $this->AdvancedVarianceThresholdRepositoryObj->create($input);

        return $this->sendResponse($AdvancedVarianceThresholdObj, 'AdvancedVarianceThreshold saved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $id
     * @param UpdateAdvancedVarianceThresholdRequest $AdvancedVarianceThresholdRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function update($client_id, $id, UpdateAdvancedVarianceThresholdRequest $AdvancedVarianceThresholdRequestObj)
    {
        $input = $AdvancedVarianceThresholdRequestObj->all();
        /** @var AdvancedVarianceThreshold $AdvancedVarianceThresholdObj */
        $AdvancedVarianceThresholdObj = $this->AdvancedVarianceThresholdRepositoryObj->findWithoutFail($id);
        if (empty($AdvancedVarianceThresholdObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceThreshold not found'), 404);
        }
        $AdvancedVarianceThresholdObj = $this->AdvancedVarianceThresholdRepositoryObj->update($input, $id);

        return $this->sendResponse($AdvancedVarianceThresholdObj->toArray(), 'AdvancedVarianceThreshold updated successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $advanced_variance_threshold_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function destroy($client_id, $advanced_variance_threshold_id)
    {
        /** @var AdvancedVarianceThreshold $AdvancedVarianceThresholdObj */
        $AdvancedVarianceThresholdObj = $this->AdvancedVarianceThresholdRepositoryObj->findWithoutFail($advanced_variance_threshold_id);
        if (empty($AdvancedVarianceThresholdObj))
        {
            return Response::json(ResponseUtil::makeError('AdvancedVarianceThreshold not found'), 404);
        }
        $this->AdvancedVarianceThresholdRepositoryObj->delete($advanced_variance_threshold_id);

        return $this->sendResponse($advanced_variance_threshold_id, 'AdvancedVarianceThreshold deleted successfully');
    }
}
