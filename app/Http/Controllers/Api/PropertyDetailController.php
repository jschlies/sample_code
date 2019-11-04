<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Events\PreCalcPropertyGroupsEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\SmartyStreetsException;
use App\Waypoint\Http\Requests\Api\UpdatePropertyRequest;
use App\Waypoint\Model;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyDetail;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\PropertyDetailRepository;
use App\Waypoint\ResponseUtil;
use Cache;
use Exception;
use function explode;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class PropertyDetailController
 */
class PropertyDetailController extends BaseApiController
{
    /**
     * @var boolean
     */
    protected $controller_allow_cacheing = false;

    /** @var  PropertyDetailRepository */
    private $PropertyDetailRepositoryObj;

    public function __construct(PropertyDetailRepository $PropertyDetailRepository)
    {
        $this->PropertyDetailRepositoryObj = $PropertyDetailRepository;
        parent::__construct($PropertyDetailRepository);
    }

    /**
     * Display a listing of the Property.
     * GET|HEAD /propertyDetails
     *
     * @param \Illuminate\Http\Request $Request
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $Request, $client_id, $property_id_arr = null)
    {
        $this->PropertyDetailRepositoryObj->pushCriteria(new RequestCriteria($Request));
        $this->PropertyDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($Request));
        $PropertyDetailObjArr = $this->PropertyDetailRepositoryObj->findWhere(
            ['client_id' => $client_id]
        );

        /**
         * @todo Hmmmm - maybe we should do this via our own RequestCriteria?????
         */
        if ($property_id_arr)
        {
            $PropertyDetailObjArr = $PropertyDetailObjArr->whereIn('id', explode(',', $property_id_arr));
        }
        return $this->sendResponse($PropertyDetailObjArr->toArray(), 'PropertyDetail(s) retrieved successfully', [], [], []);
    }

    /**
     * @param Request $Request
     * @param integer $client_id
     * @param integer $property_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function showUsers(Request $Request, $client_id, $property_id)
    {
        $this->PropertyDetailRepositoryObj->pushCriteria(new RequestCriteria($Request));
        $this->PropertyDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($Request));

        $minutes     = config('cache.cache_on', false)
            ? config('cache.cache_tags.User.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
            :
            0;
        $key = 'showUsers_property_id_' . $property_id.'_'.md5(__FILE__.__LINE__);
        $PropertyObj = $this->PropertyDetailRepositoryObj->find($property_id);
        $return_me   =
            Cache::tags([
                            'User_' . $PropertyObj->client_id,
                            'Property_' . $PropertyObj->client_id,
                            'AccessList_' . $PropertyObj->client_id,
                            'Non-Session',
                        ])
                 ->remember(
                     $key,
                     $minutes,
                     function () use ($property_id)
                     {
                         $PropertyDetailRepositoryObj = App::make(PropertyDetailRepository::class);
                         $UserObjArr                  = $PropertyDetailRepositoryObj->getUsersOfProperty($property_id);
                         $return_me                   = $UserObjArr->toArray();
                         return $return_me;
                     }
                 );

        return $this->sendResponse($return_me, 'Users(s) retrieved successfully');
    }

    /**
     * @param Request $Request
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function indexForClient(Request $Request, $client_id)
    {
        $this->PropertyDetailRepositoryObj->pushCriteria(new RequestCriteria($Request));
        $this->PropertyDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($Request));

        $PropertyDetailObjArr = $this->PropertyDetailRepositoryObj->findWhere(
            [['client_id', '=', $client_id]]
        );

        return $this->sendResponse($PropertyDetailObjArr, 'Property Summary(s) retrieved successfully', [], [], []);
    }

    /**
     * @param integer $property_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $property_id)
    {
        /** @var PropertyDetail $property */
        $PropertyDetailObj = $this->PropertyDetailRepositoryObj->find($property_id);
        if (empty($PropertyDetailObj))
        {
            return Response::json(ResponseUtil::makeError('PropertyDetail not found'), 404);
        }

        return $this->sendResponse($PropertyDetailObj, 'PropertyDetail retrieved successfully', [], [], []);
    }

    /**
     * @param integer $id
     * @param UpdatePropertyRequest $PropertyRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws SmartyStreetsException
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update($client_id, $property_id, UpdatePropertyRequest $PropertyRequestObj)
    {
        $input       = $PropertyRequestObj->all();
        $PropertyObj = $this->PropertyRepositoryObj->update($input, $property_id);

        event(
            new App\Waypoint\Events\PreCalcPropertiesEvent(
                $PropertyObj->client,
                [
                    'event_trigger_message'         => '',
                    'event_trigger_id'              => waypoint_generate_uuid(),
                    'event_trigger_class'           => self::class,
                    'event_trigger_class_instance'  => get_class($this),
                    'event_trigger_object_class'    => get_class($PropertyObj),
                    'event_trigger_object_class_id' => $PropertyObj->id,
                    'event_trigger_absolute_class'  => __CLASS__,
                    'event_trigger_file'            => __FILE__,
                    'event_trigger_line'            => __LINE__,
                    'wipe_out_list'                 =>
                        [
                            'properties' => [],
                        ],
                    'launch_job_property_id_arr'    => [$PropertyObj->id],

                ]
            )
        );
        event(
            new PreCalcPropertyGroupsEvent(
                $PropertyObj->client,
                [
                    'event_trigger_message'            => '',
                    'event_trigger_id'                 => waypoint_generate_uuid(),
                    'event_trigger_class'              => self::class,
                    'event_trigger_class_instance'     => get_class($this),
                    'event_trigger_object_class'       => get_class($PropertyObj),
                    'event_trigger_object_class_id'    => $PropertyObj->id,
                    'event_trigger_absolute_class'     => __CLASS__,
                    'event_trigger_file'               => __FILE__,
                    'event_trigger_line'               => __LINE__,
                    'wipe_out_list'                    =>
                        [
                            'property_groups' => [],
                        ],
                    'launch_job_property_group_id_arr' => [$PropertyObj->propertyGroups->pluck('id')->toArray()],
                ]
            )
        );

        return $this->sendResponse($PropertyObj, 'Property updated successfully');
    }

    /**
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     *
     * @todo non standard route - fix me
     */
    public function showStandardAttributes()
    {
        return $this->sendResponse(Property::$standard_attributes_arr, 'Property Standard Attribute Namess retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     *
     * @todo non standard route - fix me
     */
    public function showStandardAttributeUniqueValues($client_id)
    {
        return $this->sendResponse(
            App::make(ClientRepository::class)->getStandardAttributeUniqueValues($client_id), 'Property Standard Attribute Values and Counts retrieved successfully'
        );
    }

    /**
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     *
     * @todo non standard route - fix me
     */
    public function showCustomAttributeUniqueValues($client_id)
    {
        return $this->sendResponse(
            App::make(ClientRepository::class)->getCustomAttributeUniqueValues($client_id), 'Property Custom Attribute Values and Counts retrieved successfully'
        );
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param Request $CustomAttributeRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function storeCustomAttributes($client_id, $property_id, Request $CustomAttributeRequestObj)
    {
        try
        {
            $ClientRepositoryObj = App::make(ClientRepository::class);
            if ( ! $ClientRepositoryObj->find($client_id))
            {
                throw new ModelNotFoundException('No such client');
            }

            $input       = $CustomAttributeRequestObj->all();
            $PropertyObj = $this->PropertyDetailRepositoryObj->find($property_id);

            $PropertyObj->setCustomAttribute($input['attribute_name'], $input['attribute_value']);

            event(
                new App\Waypoint\Events\PreCalcPropertiesEvent(
                    $PropertyObj->client,
                    [
                        'event_trigger_message'         => '',
                        'event_trigger_id'              => waypoint_generate_uuid(),
                        'event_trigger_class'           => self::class,
                        'event_trigger_class_instance'  => get_class($this),
                        'event_trigger_object_class'    => get_class($PropertyObj),
                        'event_trigger_object_class_id' => $PropertyObj->id,
                        'event_trigger_absolute_class'  => __CLASS__,
                        'event_trigger_file'            => __FILE__,
                        'event_trigger_line'            => __LINE__,
                        'wipe_out_list'                 => 'wipe_out_list',
                        [
                            'propertys' => [],
                        ],
                        'launch_job_property_id_arr'    => [$PropertyObj->id],

                    ]
                )
            );
            event(
                new PreCalcPropertyGroupsEvent(
                    $PropertyObj->client,
                    [
                        'event_trigger_message'            => '',
                        'event_trigger_id'                 => waypoint_generate_uuid(),
                        'event_trigger_class'              => self::class,
                        'event_trigger_class_instance'     => get_class($this),
                        'event_trigger_object_class'       => get_class($PropertyObj),
                        'event_trigger_object_class_id'    => $PropertyObj->id,
                        'event_trigger_absolute_class'     => __CLASS__,
                        'event_trigger_file'               => __FILE__,
                        'event_trigger_line'               => __LINE__,
                        'wipe_out_list'                    => 'wipe_out_list',
                        [
                            'property_groups' => [],
                        ],
                        'launch_job_property_group_id_arr' => [$PropertyObj->propertyGroups->pluck('id')->toArray()],

                    ]
                )
            );
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 500, $e);
        }
        return $this->sendResponse($PropertyObj, 'Custom Attributes saved successfully');
    }

    /**
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws ModelNotFoundException
     */
    public function showRelatedUserTypes($client_id)
    {
        $ClientRepositoryObj = App::make(ClientRepository::class);
        if ( ! $ClientObj = $ClientRepositoryObj->with('relatedUserTypes')->find($client_id))
        {
            throw new ModelNotFoundException('No such client');
        }
        return $this->sendResponse(
            $ClientObj->getRelatedUserTypes(Property::class), 'Related User Type(s) retrieved successfully'
        );
    }
}
