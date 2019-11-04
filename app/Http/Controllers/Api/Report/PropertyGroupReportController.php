<?php

namespace App\Waypoint\Http\Controllers\Api\Report;

use App\Waypoint\Models\Client;
use Illuminate\Http\Request;
use App\Waypoint\Collection;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\PropertyGroupRepository;
use App\Waypoint\Http\ApiController as BaseApiController;

/**
 * Class PropertyGroupPublicController
 */
class PropertyGroupReportController extends BaseApiController
{
    /** @var  PropertyGroupRepository */
    private $PropertyGroupRepositoryObj;

    public function __construct(PropertyGroupRepository $PropertyGroupRepositoryObj)
    {
        $this->PropertyGroupRepositoryObj = $PropertyGroupRepositoryObj;
        parent::__construct($PropertyGroupRepositoryObj);
    }

    /**
     * @param Request $RequestObj
     * @param $client_id
     * @param $client_id_old
     * @return \Illuminate\Http\JsonResponse|null
     * @throws \App\Waypoint\Exceptions\DeploymentException
     * @throws \App\Waypoint\Exceptions\GeneralException
     */
    public function list_property_groups_by_client_id_old(Request $RequestObj, $client_id, $client_id_old)
    {
        $ClientRepositoryObj = $this->PropertyGroupRepositoryObj->makeRepository(ClientRepository::class);
        $PropertyGroupObjArr = new Collection();

        /** @var Client $ClientObj */
        foreach ($ClientRepositoryObj->findWhere(['client_id_old' => $client_id_old]) as $ClientObj)
        {
            foreach ($ClientObj->propertyGroups as $PropertyGroupObj)
            {
                $PropertyGroupObjArr[] = $PropertyGroupObj;
            }
        }
        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($PropertyGroupObjArr, 'PropertyGroup(s) retrieved successfully');
        }
        collect_waypoint_spreadsheet($PropertyGroupObjArr)->toCSVReport($this->PropertyGroupRepositoryObj->model() . ' Report Generated at ' . date('Y-m-d H:i:s'));
    }
}
