<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\JsonResponse;
use App\Waypoint\Http\Requests\Generated\Api\CreatePropertyNativeCoaRequest;
use App\Waypoint\Models\PropertyNativeCoa;
use App\Waypoint\Repositories\PropertyNativeCoaRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PropertyNativeCoaController
 */
class PropertyNativeCoaController extends BaseApiController
{
    /** @var  PropertyNativeCoaRepository */
    private $PropertyNativeCoaRepositoryObj;

    public function __construct(PropertyNativeCoaRepository $PropertyNativeCoaRepositoryObj)
    {
        $this->PropertyNativeCoaRepositoryObj = $PropertyNativeCoaRepositoryObj;
        parent::__construct($PropertyNativeCoaRepositoryObj);
    }

    /**
     * @param Request $RequestObj
     * @param $client_id
     * @param $property_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $RequestObj, $client_id, $property_id)
    {
        $this->PropertyNativeCoaRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PropertyNativeCoaRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $PropertyNativeCoaObjArr = $this->PropertyNativeCoaRepositoryObj
            ->all()
            ->filter(
                function ($item) use ($property_id)
                {
                    return $item->property_id == $property_id;
                }
            );

        return $this->sendResponse($PropertyNativeCoaObjArr, 'PropertyNativeCoa(s) retrieved successfully');
    }

    /**
     * @param CreatePropertyNativeCoaRequest $PropertyNativeCoaRequestObj
     * @param $client_id
     * @param $property_id
     * @return JsonResponse|null
     * @throws GeneralException
     * @throws ValidatorException
     */
    public function store(CreatePropertyNativeCoaRequest $PropertyNativeCoaRequestObj, $client_id, $property_id)
    {
        $input = $PropertyNativeCoaRequestObj->all();

        $PropertyNativeCoaObj = $this->PropertyNativeCoaRepositoryObj->create($input);

        return $this->sendResponse($PropertyNativeCoaObj, 'PropertyNativeCoa saved successfully');
    }

    /**
     * @param $client_id
     * @param $property_id
     * @param $property_native_coa_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $property_id, $property_native_coa_id)
    {
        /** @var PropertyNativeCoa $PropertyNativeCoaObj */
        $PropertyNativeCoaObj = $this->PropertyNativeCoaRepositoryObj->findWithoutFail($property_native_coa_id);
        if (empty($PropertyNativeCoaObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyNativeCoa not found'), 404);
        }

        return $this->sendResponse($PropertyNativeCoaObj, 'PropertyNativeCoa retrieved successfully');
    }

    /**
     * @param $client_id
     * @param $property_id
     * @param $property_native_coa_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function destroy($client_id, $property_id, $property_native_coa_id)
    {
        /** @var PropertyNativeCoa $PropertyNativeCoaObj */
        $PropertyNativeCoaObj = $this->PropertyNativeCoaRepositoryObj->findWithoutFail($property_native_coa_id);
        if (empty($PropertyNativeCoaObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyNativeCoa not found'), 404);
        }
        $PropertyNativeCoaObj->delete();

        return $this->sendResponse($property_native_coa_id, 'PropertyNativeCoa deleted successfully');
    }
}
