<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\JsonResponse;
use App\Waypoint\Http\Requests\Generated\Api\CreateCalculatedFieldEquationPropertyRequest;
use App\Waypoint\Models\CalculatedFieldEquationProperty;
use App\Waypoint\Repositories\CalculatedFieldEquationPropertyRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class CalculatedFieldEquationPropertyController
 */
class CalculatedFieldEquationPropertyController extends BaseApiController
{
    /** @var  CalculatedFieldEquationPropertyRepository */
    private $CalculatedFieldEquationPropertyRepositoryObj;

    public function __construct(CalculatedFieldEquationPropertyRepository $CalculatedFieldEquationPropertyRepositoryObj)
    {
        $this->CalculatedFieldEquationPropertyRepositoryObj = $CalculatedFieldEquationPropertyRepositoryObj;
        parent::__construct($CalculatedFieldEquationPropertyRepositoryObj);
    }

    /**
     * Display a listing of the CalculatedFieldEquationProperty.
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function index(
        $client_id,
        $report_template_id,
        $calculated_field_id,
        $calculated_field_equation_id,
        Request $RequestObj
    ) {
        $this->CalculatedFieldEquationPropertyRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->CalculatedFieldEquationPropertyRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        $CalculatedFieldEquationPropertyObjArr = $this->CalculatedFieldEquationPropertyRepositoryObj->findWhere(
            [
                'calculated_field_equation_id' => $calculated_field_equation_id,
            ]
        );

        return $this->sendResponse($CalculatedFieldEquationPropertyObjArr, 'CalculatedFieldEquationProperty(s) retrieved successfully');
    }

    /**
     * Store a newly created CalculatedFieldEquationProperty in storage.
     *
     * @param integer $client_id
     * @param integer $report_template_id
     * @param integer $calculated_field_id
     * @param integer $calculated_field_equation_id
     * @param CreateCalculatedFieldEquationPropertyRequest $CalculatedFieldEquationPropertyRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function store(
        $client_id,
        $report_template_id,
        $calculated_field_id,
        $calculated_field_equation_id,
        CreateCalculatedFieldEquationPropertyRequest $CalculatedFieldEquationPropertyRequestObj
    ) {
        $input                                 = $CalculatedFieldEquationPropertyRequestObj->all();
        $input['calculated_field_equation_id'] = $calculated_field_equation_id;

        $CalculatedFieldEquationPropertyObj = $this->CalculatedFieldEquationPropertyRepositoryObj->create($input);

        return $this->sendResponse($CalculatedFieldEquationPropertyObj, 'CalculatedFieldEquationProperty saved successfully');
    }

    /**
     * Remove the specified CalculatedFieldEquationProperty from storage.
     * DELETE /calculatedFieldEquationProperties/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy(
        $client_id,
        $report_template_id,
        $calculated_field_id,
        $calculated_field_equation_id,
        $calculated_field_equation_property_id
    ) {
        /** @var CalculatedFieldEquationProperty $CalculatedFieldEquationPropertyObj */
        $CalculatedFieldEquationPropertyObj = $this->CalculatedFieldEquationPropertyRepositoryObj->findWithoutFail($calculated_field_equation_property_id);
        if (empty($CalculatedFieldEquationPropertyObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedFieldEquationProperty not found'), 404);
        }

        $this->CalculatedFieldEquationPropertyRepositoryObj->delete($calculated_field_equation_property_id);

        return $this->sendResponse($calculated_field_equation_property_id, 'CalculatedFieldEquationProperty deleted successfully');
    }
}
