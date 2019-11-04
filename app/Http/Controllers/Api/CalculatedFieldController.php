<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\CalculatedFieldDetailRepository;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\JsonResponse;
use App\Waypoint\Http\Requests\Generated\Api\CreateCalculatedFieldRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateCalculatedFieldRequest;
use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Repositories\CalculatedFieldRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class CalculatedFieldController
 */
class CalculatedFieldController extends BaseApiController
{
    /** @var  CalculatedFieldRepository */
    private $CalculatedFieldRepositoryObj;

    /** @var  CalculatedFieldDetailRepository */
    private $CalculatedFieldDetailRepositoryObj;

    public function __construct(CalculatedFieldRepository $CalculatedFieldRepositoryObj)
    {
        $this->CalculatedFieldRepositoryObj       = $CalculatedFieldRepositoryObj;
        $this->CalculatedFieldDetailRepositoryObj = \App::make(CalculatedFieldDetailRepository::class);
        parent::__construct($CalculatedFieldRepositoryObj);
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
     * @param integer $client_id
     * @param integer $report_template_id
     * @param CreateCalculatedFieldRequest $CalculatedFieldRequestObj
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function store($client_id, $report_template_id, CreateCalculatedFieldRequest $CalculatedFieldRequestObj)
    {
        $input                       = $CalculatedFieldRequestObj->all();
        $input['client_id']          = $client_id;
        $input['report_template_id'] = $report_template_id;

        $CalculatedFieldObj = $this->CalculatedFieldRepositoryObj->create($input);

        return $this->sendResponse($this->CalculatedFieldDetailRepositoryObj->find($CalculatedFieldObj->id), 'CalculatedField saved successfully');
    }

    /**
     * Display the specified CalculatedField.
     * GET|HEAD /calculatedFields/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function show($client_id, $report_template_id, $calculated_field_id_arr)
    {
        /** @var CalculatedField $calculatedField */
        $CalculatedFieldObj = $this->CalculatedFieldRepositoryObj->findWhereIn('id', explode(',', $calculated_field_id_arr));
        if (empty($CalculatedFieldObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedField not found'), 404);
        }

        return $this->sendResponse($CalculatedFieldObj, 'CalculatedField retrieved successfully');
    }

    /**
     * Update the specified CalculatedField in storage.
     * PUT/PATCH /calculatedFields/{id}
     *
     * @param integer $id
     * @param UpdateCalculatedFieldRequest $CalculatedFieldRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function update(UpdateCalculatedFieldRequest $CalculatedFieldRequestObj, $client_id, $report_template_id, $calculated_field_id)
    {
        $input = $CalculatedFieldRequestObj->all();
        /** @var CalculatedField $CalculatedFieldObj */
        $CalculatedFieldObj = $this->CalculatedFieldRepositoryObj->findWithoutFail($calculated_field_id);
        if (empty($CalculatedFieldObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedField not found'), 404);
        }
        $CalculatedFieldObj = $this->CalculatedFieldRepositoryObj->update($input, $calculated_field_id);

        return $this->sendResponse($CalculatedFieldObj, 'CalculatedField updated successfully');
    }

    /**
     * Remove the specified CalculatedField from storage.
     * DELETE /calculatedFields/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($client_id, $report_template_id, $calculated_field_id)
    {
        /** @var CalculatedField $CalculatedFieldObj */
        $CalculatedFieldObj = $this->CalculatedFieldRepositoryObj->findWithoutFail($calculated_field_id);
        if (empty($CalculatedFieldObj))
        {
            return Response::json(ResponseUtil::makeError('CalculatedField not found'), 404);
        }
        $this->CalculatedFieldRepositoryObj->delete($calculated_field_id);

        return $this->sendResponse($calculated_field_id, 'CalculatedField deleted successfully');
    }
}
