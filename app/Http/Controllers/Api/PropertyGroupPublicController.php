<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Generated\Api\CreatePropertyGroupRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdatePropertyGroupRequest;
use App\Waypoint\Models\AdvancedVarianceSummary;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\PropertyGroupRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PropertyGroupPublicController
 */
class PropertyGroupPublicController extends BaseApiController
{
    /** @var  PropertyGroupRepository */
    private $PropertyGroupRepositoryObj;

    public function __construct(PropertyGroupRepository $PropertyGroupRepositoryObj)
    {
        $this->PropertyGroupRepositoryObj = $PropertyGroupRepositoryObj;
        parent::__construct($PropertyGroupRepositoryObj);
    }

    /**
     * Display a listing of the PropertyGroup.
     * GET|HEAD /propertyGroups
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $RequestObj)
    {
        $this->PropertyGroupRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PropertyGroupRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $PropertyGroupObjArr = $this->PropertyGroupRepositoryObj->all();

        return $this->sendResponse($PropertyGroupObjArr, 'PropertyGroup(s) retrieved successfully', [], [], []);
    }

    /**
     * @param \Illuminate\Http\Request $RequestObj
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexForClient(Request $RequestObj, $client_id)
    {
        $this->PropertyGroupRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PropertyGroupRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        $PropertyGroupObjArr = $this->PropertyGroupRepositoryObj->findWhere(
            [
                'client_id' => $client_id,
            ]
        );
        if ( ! $PropertyGroupObjArr->count())
        {
            return Response::json(ResponseUtil::makeError('PropertyGroup(s) not found for client'), 400);
        }

        return $this->sendResponse($PropertyGroupObjArr, 'PropertyGroup(s) retrieved successfully', [], [], []);
    }

    /**
     * Store a newly created PropertyGroup in storage.
     * POST /propertyGroups
     *
     * @param CreatePropertyGroupRequest $PropertyGroupRequestObj
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function store(CreatePropertyGroupRequest $PropertyGroupRequestObj, $client_id)
    {
        $input = $PropertyGroupRequestObj->all();
        if ( ! isset($input['client_id']) || ! $input['client_id'])
        {
            $input['client_id'] = $this->getCurrentLoggedInUserObj()->client_id;
        }
        if ( ! isset($input['user_id']) || ! $input['user_id'])
        {
            $input['user_id'] = $this->getCurrentLoggedInUserObj()->id;
        }
        $PropertyGroupObj = $this->PropertyGroupRepositoryObj->create($input);

        return $this->sendResponse($PropertyGroupObj, 'PropertyGroup saved successfully');
    }

    /**
     * @param integer $client_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $id)
    {
        /** @var PropertyGroup $propertyGroup */
        $PropertyGroupObj = $this->PropertyGroupRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroup not found'), 404);
        }

        return $this->sendResponse($PropertyGroupObj, 'PropertyGroup retrieved successfully', [], [], []);
    }

    /**
     * @param integer $client_id
     * @param $id
     * @param UpdatePropertyGroupRequest $PropertyGroupRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update($client_id, $id, UpdatePropertyGroupRequest $PropertyGroupRequestObj)
    {
        $input = $PropertyGroupRequestObj->all();
        if ( ! isset($input['client_id']) || ! $input['client_id'])
        {
            $input['client_id'] = $this->getCurrentLoggedInUserObj()->client_id;
        }
        if ( ! isset($input['user_id']) || ! $input['user_id'])
        {
            $input['user_id'] = $this->getCurrentLoggedInUserObj()->id;
        }
        /** @var PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj = $this->PropertyGroupRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroup not found'), 404);
        }
        $PropertyGroupObj = $this->PropertyGroupRepositoryObj->update($input, $id);

        return $this->sendResponse($PropertyGroupObj, 'PropertyGroup updated successfully');
    }

    /**
     * @param integer $client_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function destroy($client_id, $id)
    {
        /** @var PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj = $this->PropertyGroupRepositoryObj->findWithoutFail($id);
        if (empty($PropertyGroupObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyGroup not found'), 404);
        }
        $PropertyGroupObj->delete();

        return $this->sendResponse($id, 'PropertyGroup deleted successfully');
    }

    /**
     * @param integer $client_id_old
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function list_property_groups_by_client_id_old($client_id_old)
    {
        $ClientRepositoryObj = $this->PropertyGroupRepositoryObj->makeRepository(ClientRepository::class);
        $PropertyGroupObjArr = new Collection();

        foreach ($ClientRepositoryObj->findWhere(['client_id_old' => $client_id_old]) as $ClientObj)
        {
            foreach ($ClientObj->property_groups as $PropertyGroupObj)
            {
                $PropertyGroupObjArr[] = $PropertyGroupObj;
            }
        }

        return $this->sendResponse($PropertyGroupObjArr, 'PropertyGroup(s) retrieved successfully', [], [], []);
    }

    /**
     * @param UpdatePropertyGroupRequest $PropertyGroupRequestObj
     * @param integer $client_id
     * @param integer $property_group_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function advanced_variance_by_property_group(UpdatePropertyGroupRequest $PropertyGroupRequestObj, $client_id, $property_group_id)
    {
        $input            = $PropertyGroupRequestObj->all();
        $PropertyGroupObj = $this->PropertyGroupRepositoryObj->find($property_group_id);

        if (
            isset($input['as_of_year']) &&
            isset($input['as_of_month'])
        )
        {
            $AsOfDateObj = Carbon::create($input['as_of_year'], $input['as_of_month'], 1, 0, 0, 0);
            $key         = 'AdvancedVarianceSummaryByPropertyGroupId_' . $AsOfDateObj->format('Y') . '_' . $AsOfDateObj->format('m') . '_' . $property_group_id;
        }
        else
        {
            $key = 'AdvancedVarianceSummaryByPropertyGroupId_' . $property_group_id;
        }
        $advanced_variance_summary_arr = $PropertyGroupObj->getPreCalcValue($key);
        if ($advanced_variance_summary_arr === null)
        {
            $queryObj = AdvancedVarianceSummary::byPropertyGroup($property_group_id)->withChildren();
            if (
                isset($input['as_of_year']) &&
                isset($input['as_of_month'])
            )
            {
                $AsOfDateObj = Carbon::create($input['as_of_year'], $input['as_of_month'], 1, 0, 0, 0);
                $key         = 'AdvancedVarianceSummaryByPropertyGroupId_' . $AsOfDateObj->format('Y') . '_' . $AsOfDateObj->format('m') . '_' . $property_group_id;

                $queryObj->where('as_of_year', $input['as_of_year'])
                         ->where('as_of_month', $input['as_of_month']);
            }
            else
            {
                $key = 'AdvancedVarianceSummaryByPropertyGroupId_' . $PropertyGroupObj->id;
            }

            $advanced_variance_summary_arr = $queryObj->get()->toArray();
            $PropertyGroupObj->updatePreCalcValue(
                $key,
                $advanced_variance_summary_arr
            );
        }

        return $this->sendResponse($advanced_variance_summary_arr, 'AdvancedSummary(s) retrieved successfully');
    }
}
