<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\JsonResponse;
use App\Waypoint\Http\Requests\Generated\Api\CreateAssetTypeRequest;
use App\Waypoint\Models\AssetType;
use App\Waypoint\Repositories\AssetTypeRepository;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class AssetTypeController
 * @codeCoverageIgnore
 */
class AssetTypeDeprecatedController extends BaseApiController
{
    /** @var  AssetTypeRepository */
    private $AssetTypeRepositoryObj;

    public function __construct(AssetTypeRepository $AssetTypeRepositoryObj)
    {
        $this->AssetTypeRepositoryObj = $AssetTypeRepositoryObj;
        parent::__construct($AssetTypeRepositoryObj);
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
        $this->AssetTypeRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AssetTypeRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $AssetTypeObjArr = $this->AssetTypeRepositoryObj->findWhere(['client_id' => $client_id]);

        return $this->sendResponse($AssetTypeObjArr, 'AssetType(s) retrieved successfully');
    }

    /**
     * Store a newly created AssetType in storage.
     *
     * @param CreateAssetTypeRequest $AssetTypeRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function store(CreateAssetTypeRequest $AssetTypeRequestObj, $client_id)
    {
        $input        = $AssetTypeRequestObj->all();
        $AssetTypeObj = $this->AssetTypeRepositoryObj->create($input);

        return $this->sendResponse($AssetTypeObj, 'AssetType saved successfully');
    }

    /**
     * Display the specified AssetType.
     * GET|HEAD /assetTypes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function show($client_id, $id)
    {
        /** @var AssetType $assetType */
        $AssetTypeObj = $this->AssetTypeRepositoryObj->findWithoutFail($id);
        if (empty($AssetTypeObj))
        {
            return Response::json(ResponseUtil::makeError('AssetType not found'), 404);
        }

        return $this->sendResponse($AssetTypeObj, 'AssetType retrieved successfully');
    }

    /**
     * Remove the specified AssetType from storage.
     * DELETE /assetTypes/{id}
     *
     * @param integer $id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($client_id, $id)
    {
        /** @var AssetType $AssetTypeObj */
        $AssetTypeObj = $this->AssetTypeRepositoryObj->findWithoutFail($id);
        if (empty($AssetTypeObj))
        {
            return Response::json(ResponseUtil::makeError('AssetType not found'), 404);
        }
        $AssetTypeObj->delete();

        return $this->sendResponse($id, 'AssetType deleted successfully');
    }
}
