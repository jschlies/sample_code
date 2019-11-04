<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Requests\Generated\Api\CreateCalculatedFieldEquationRequest;
use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Models\CalculatedFieldEquation;
use App\Waypoint\Repositories\CalculatedFieldEquationRepository;
use App\Waypoint\Repositories\CalculatedFieldRepository;
use App\Waypoint\ResponseUtil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Validator\Exceptions\ValidatorException;
use Response;

/**
 * Class CalculatedFieldEquationController
 * @codeCoverageIgnore
 */
class CalculatedFieldEquationDeprecatedController extends BaseApiController
{
    /** @var  CalculatedFieldEquationRepository */
    private $CalculatedFieldRepositoryObj;

    /** @var  CalculatedFieldEquationRepository */
    private $CalculatedFieldEquationRepositoryObj;

    public function __construct(CalculatedFieldEquationRepository $CalculatedFieldEquationRepositoryObj)
    {
        $this->CalculatedFieldEquationRepositoryObj = $CalculatedFieldEquationRepositoryObj;
        parent::__construct($CalculatedFieldEquationRepositoryObj);
    }

    /**
     * Display a listing of the CalculatedField.
     * GET|HEAD /calculatedFields
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function index($client_id, $report_template_id, Request $RequestObj)
    {
        $this->CalculatedFieldRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->CalculatedFieldRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $CalculatedFieldObjArr = $this->CalculatedFieldRepositoryObj->findWhere(
            [
                'report_template_id' => $report_template_id,
            ]
        );

        return $this->sendResponse($CalculatedFieldObjArr, 'CalculatedField(s) retrieved successfully');
    }

    /**
     * Display a listing of the CalculatedField.
     * GET|HEAD /calculatedFields
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Exception
     */
    public function indexForCalculatedField($client_id, $report_template_id, $calculated_field_id, Request $RequestObj)
    {
        $this->CalculatedFieldRepositoryObj = App::make(CalculatedFieldRepository::class);
        $this->CalculatedFieldRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->CalculatedFieldRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        /** @var CalculatedField $CalculatedFieldObj */
        $CalculatedFieldObj = $this->CalculatedFieldRepositoryObj->find($calculated_field_id);

        return $this->sendResponse($CalculatedFieldObj->calculatedFieldEquations, 'CalculatedField(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $report_template_id
     * @param integer $calculated_field_id
     * @param CreateCalculatedFieldEquationRequest $CalculatedFieldEquationRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function store($client_id, $report_template_id, $calculated_field_id, CreateCalculatedFieldEquationRequest $CalculatedFieldEquationRequestObj)
    {
        $input = $CalculatedFieldEquationRequestObj->all();

        $CalculatedFieldEquationObj = $this->CalculatedFieldEquationRepositoryObj->create($input);

        return $this->sendResponse($CalculatedFieldEquationObj, 'CalculatedFieldEquation saved successfully');
    }

    /**
     * @param integer $client_id
     * @param $report_template_id
     * @param $calculated_field_id
     * @param $calculated_field_equation_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function destroy($client_id, $report_template_id, $calculated_field_id, $calculated_field_equation_id)
    {
        /** @var CalculatedFieldEquation $CalculatedFieldEquationObj */
        $CalculatedFieldEquationObj = $this->CalculatedFieldEquationRepositoryObj->findWithoutFail($calculated_field_equation_id);
        if (empty($CalculatedFieldEquationObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedFieldEquation not found'), 404);
        }
        $this->CalculatedFieldEquationRepositoryObj->delete($calculated_field_equation_id);

        return $this->sendResponse($calculated_field_equation_id, 'CalculatedFieldEquation deleted successfully');
    }

    /**
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function show($client_id, $report_template_id, $calculated_field_id, $calculated_field_equation_id_arr)
    {
        /** @var CalculatedFieldEquation $CalculatedFieldEquationObj */
        $CalculatedFieldEquationObjArr = $this->CalculatedFieldEquationRepositoryObj->findWhereIn('id', explode(',', $calculated_field_equation_id_arr));
        return $this->sendResponse($CalculatedFieldEquationObjArr, 'CalculatedField(s) retrieved successfully');
    }
}
