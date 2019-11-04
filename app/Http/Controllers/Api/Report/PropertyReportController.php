<?php

namespace App\Waypoint\Http\Controllers\Api\Report;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Models\Property;
use App\Waypoint\SpreadsheetCollection;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Waypoint\Repositories\PropertyRepository;

class PropertyReportController extends BaseApiController
{
    /**
     * @todo - either merge this (and other Report controllers) into app/Http/Controllers/ApiRequest or
     *       come up w/ a naming system for all comtrollers
     */
    /** @var  PropertyRepository */
    private $PropertyRepositoryObj;

    /**
     * PropertyReportController constructor.
     * @param PropertyRepository $PropertyRepositoryObj
     */
    public function __construct(
        PropertyRepository $PropertyRepositoryObj
    ) {
        $this->PropertyRepositoryObj = $PropertyRepositoryObj;

        parent::__construct($PropertyRepositoryObj);
    }

    /**
     * Display a report of the Properties.
     *
     * @param Request $RequestObj
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $RequestObj, $client_id)
    {
        $this->PropertyRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->PropertyRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /** @var Collection $PropertyObjArr */
        $PropertyObjArr = $this->PropertyRepositoryObj->findByField('client_id', $client_id)->sortBy('name');

        $return_me = new  SpreadsheetCollection();
        /** @var Property $Property */
        foreach ($PropertyObjArr as $Property)
        {
            $return_me[] = [
                'id'                  => $Property->id,
                'name'                => $Property->name,
                'display_name'        => $Property->display_name,
                'property_code'       => $Property->property_code,
                'description'         => $Property->description,
                'accounting_system'   => $Property->accounting_system,
                'street_address'      => $Property->street_address,
                'display_address'     => $Property->display_address,
                'city'                => $Property->city,
                'state'               => $Property->state,
                'country'             => $Property->country,
                'country_code'        => $Property->country_code,
                'square_footage'      => $Property->square_footage,
                'asset_type'          => isset($Property->assetType) ? $Property->assetType->asset_type_name : null,
                'year_built'          => $Property->year_built,
                'management_type'     => $Property->management_type,
                'lease_type'          => $Property->lease_type,
                'time_zone'           => $Property->time_zone,
                'property_groups'     => $Property->propertyGroups->implode('name', ','),
                'access_lists'        => $Property->accessLists->implode('name', ','),
                'property_class'      => $Property->property_class,
                'year_renovated'      => $Property->year_renovated,
                'number_of_buildings' => $Property->number_of_buildings,
                'number_of_floors'    => $Property->number_of_floors,
            ];
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($PropertyObjArr, 'Property data retrieved successfully');
        }
        $return_me->toCSVReport(
            $this->PropertyRepositoryObj->model() . ' Report Generated at ' . date('Y-m-d H:i:s')
        );
    }
}
