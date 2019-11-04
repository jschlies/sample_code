<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\DeploymentException;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Requests\Generated\Api\CreateEcmProjectRequest;
use App\Waypoint\Http\Requests\Generated\Api\UpdateEcmProjectRequest;
use App\Waypoint\Models\EcmProject;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Repositories\EcmProjectRepository;
use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Repositories\PropertyRepository;
use function explode;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use App\Waypoint\ResponseUtil;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class EcmProjectController
 */
class EcmProjectPublicController extends BaseApiController
{
    /** @var  EcmProjectRepository */
    private $EcmProjectRepositoryObj;

    /**
     * @var array
     */
    protected $needed_configs = ['FEATURE_PROJECTS'];

    /**
     * EcmProjectPublicController constructor.
     * @param EcmProjectRepository $EcmProjectRepositoryObj
     */
    public function __construct(EcmProjectRepository $EcmProjectRepositoryObj)
    {
        $this->EcmProjectRepositoryObj = $EcmProjectRepositoryObj;
        parent::__construct($EcmProjectRepositoryObj);
    }

    /**
     * @param \Illuminate\Http\Request $RequestObj
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse
     * @throws DeploymentException
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexForClient(Request $RequestObj, $client_id, $ecm_projects_id_arr = null)
    {
        $this->EcmProjectRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->EcmProjectRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        $EcmProjectObjArr = $this->EcmProjectRepositoryObj->findWithClientIdUserId(
            $client_id,
            $this->getCurrentLoggedInUserObj()->id
        );

        /**
         * @todo Hmmmm - maybe we should do this via our own RequestCriteria?????
         */
        if ($ecm_projects_id_arr)
        {
            $EcmProjectObjArr = $EcmProjectObjArr->whereIn('id', explode(',', $ecm_projects_id_arr));
        }
        return $this->sendResponse($EcmProjectObjArr, 'EcmProject(s) retrieved successfully');
    }

    /**
     * @param \Illuminate\Http\Request $RequestObj
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse
     * @throws DeploymentException
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     *
     * @todo non-standard route - fix me
     */
    public function indexForProperty(Request $RequestObj, $client_id, $property_id_arr = null)
    {
        $this->EcmProjectRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->EcmProjectRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /** @var PropertyRepository $PropertyRepositoryObj */
        $PropertyRepositoryObj = App::make(PropertyRepository::class);
        $PropertyObjArr        = $PropertyRepositoryObj->findWhereIn(
            'id',
            explode(',', $property_id_arr)
        );

        $return_me = [];
        /** @var Property $PropertyObj */
        foreach ($PropertyObjArr as $PropertyObj)
        {
            if ($this->getCurrentLoggedInUserObj()->canAccessProperty($PropertyObj->id))
            {
                /**
                 * @todo make this a more standard response
                 */
                $return_me[$PropertyObj->id] = $PropertyObj->ecmProjects->toArray();
            }
        }
        return $this->sendResponse($return_me, 'EcmProject(s) retrieved successfully');
    }

    /**
     * @param \Illuminate\Http\Request $RequestObj
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse
     * @throws DeploymentException
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     *
     * @todo non-standard route - fix me
     */
    public function indexForPropertyGroup(Request $RequestObj, $client_id, $property_group_id_arr = null)
    {
        $this->EcmProjectRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->EcmProjectRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /** @var PropertyRepository $PropertyRepositoryObj */
        $PropertyGroupRepositoryObj = App::make(PropertyGroupRepository::class);
        $PropertyGroupObjArr        = $PropertyGroupRepositoryObj->findByField(
            'id',
            $property_group_id_arr
        );

        $return_me = [];
        /** @var PropertyGroup $PropertyGroupObj */
        foreach ($PropertyGroupObjArr as $PropertyGroupObj)
        {
            foreach ($PropertyGroupObj->properties as $PropertyObj)
            {
                if ($this->getCurrentLoggedInUserObj()->canAccessProperty($PropertyObj->id))
                {
                    $return_me[$PropertyGroupObj->id] = $PropertyObj->ecmProjects;
                }
            }
        }
        return $this->sendResponse($return_me, 'EcmProject(s) retrieved successfully');
    }

    /**
     * @param CreateEcmProjectRequest $EcmProjectRequestObj
     * @param $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function store(CreateEcmProjectRequest $EcmProjectRequestObj, $client_id)
    {
        $input         = $EcmProjectRequestObj->all();
        $EcmProjectObj = $this->EcmProjectRepositoryObj->create($input);

        return $this->sendResponse($EcmProjectObj, 'EcmProject saved successfully');
    }

    /**
     * @param $client_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $property_id, $ecm_project_id)
    {
        /** @var EcmProject $ecmProject */
        $EcmProjectObj = $this->EcmProjectRepositoryObj->findWithoutFail($ecm_project_id);
        if (empty($EcmProjectObj))
        {
            return Response::json(ResponseUtil::makeError('EcmProject not found'), 404);
        }

        return $this->sendResponse($EcmProjectObj, 'EcmProject retrieved successfully');
    }

    /**
     * @param UpdateEcmProjectRequest $EcmProjectRequestObj
     * @param $client_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(UpdateEcmProjectRequest $EcmProjectRequestObj, $client_id, $property_id, $ecm_project_id)
    {
        $input = $EcmProjectRequestObj->all();
        /** @var EcmProject $EcmProjectObj */
        $EcmProjectObj = $this->EcmProjectRepositoryObj->findWithoutFail($ecm_project_id);
        if (empty($EcmProjectObj))
        {
            return Response::json(ResponseUtil::makeError('EcmProject not found'), 404);
        }
        $EcmProjectObj = $this->EcmProjectRepositoryObj->update($input, $ecm_project_id);

        return $this->sendResponse($EcmProjectObj, 'EcmProject updated successfully');
    }

    /**
     * @param $client_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function destroy($client_id, $property_id, $ecm_project_id)
    {
        /** @var EcmProject $EcmProjectObj */
        $EcmProjectObj = $this->EcmProjectRepositoryObj->findWithoutFail($ecm_project_id);
        if (empty($EcmProjectObj))
        {
            return Response::json(ResponseUtil::makeError('EcmProject not found'), 404);
        }
        $EcmProjectObj->delete();

        return $this->sendResponse($ecm_project_id, 'EcmProject deleted successfully');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     *
     * @todo non-standard route - fix me
     */
    public function getAvailableProjectCategories()
    {
        return $this->sendResponse(EcmProject::$project_category_arr, 'EcmProject Category(s) Available');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     *
     * @todo non-standard route - fix me
     */
    public function getAvailableProjectStatuses()
    {
        return $this->sendResponse(EcmProject::$project_status_arr, 'EcmProject Status(s) Available');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     *
     * @todo non-standard route - fix me
     */
    public function getAvailableEnergyUnits()
    {
        return $this->sendResponse(EcmProject::$energy_units_arr, 'EcmProject Energy Units(s) Available');
    }
}
