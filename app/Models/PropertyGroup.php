<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\AuditableTrait;
use App\Waypoint\CanPreCalcJSONTrait;
use App\Waypoint\CommentableTrait;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Model;
use App\Waypoint\Repositories\PropertyGroupPropertyRepository;
use App\Waypoint\Collection;
use Carbon\Carbon;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;

/**
 * Class PropertyGroup
 * @package App\Waypoint\Models
 */
class PropertyGroup extends PropertyGroupModelBase implements AuditableContract, UserResolver
{
    use AuditableTrait;
    use CommentableTrait;
    use CanPreCalcJSONTrait;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'user_id'                  => 'sometimes|nullable|integer',
        'total_square_footage'     => 'sometimes|nullable|integer',
        'property_id_md5'          => 'sometimes|max:255',
        'description'              => 'sometimes',
        'parent_property_group_id' => 'sometimes|nullable|integer',
    ];

    /** @var Collection */
    protected $all_properties = null;

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'name',
        'user_id',
        'total_square_footage',
        'description',
        'property_id_md5',
        'is_all_property_group',
        'is_public',
        'parent_property_group_id',
    ];

    /**
     * PropertyGroup constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function propertyGroupParent()
    {
        return $this->belongsTo(PropertyGroup::class, 'parent_property_group_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function propertyGroupChildren()
    {
        return $this->hasMany(PropertyGroup::class, 'parent_property_group_id', 'id');
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     * @throws GeneralException
     */
    public function toArray(): array
    {
        return [
            "id"                          => $this->id,
            "name"                        => $this->name,
            "description"                 => $this->description,
            "is_all_property_group"       => $this->is_all_property_group,
            "property_id_md5"             => $this->property_id_md5,
            "total_square_footage"        => $this->total_square_footage,
            "is_public"                   => $this->is_public,
            "user_id"                     => $this->user_id,
            "parent_property_group_id"    => $this->parent_property_group_id,
            "property_count"              => $this->property_count,
            "property_group_property_ids" => $this->property_group_property_ids,
            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return \App\Waypoint\Collection|mixed
     */
    public function getAllProperties()
    {
        if ($this->all_properties)
        {
            return $this->all_properties;
        }

        if ($this->is_all_property_group)
        {
            /**
             * @todo do I really need to do this?? Why check what properties
             *       the user can access here??? Was done in policy section
             */
            if ($this->user_id)
            {
                if ( ! $this->user)
                {
                    throw new GeneralException('PropertyGroup issue' . __LINE__);
                }
                $this->all_properties = $this->user->getAccessiblePropertyObjArr();
            }
            else
            {
                throw new GeneralException('invalid property group' . __LINE__);
            }
        }
        else
        {
            $this->all_properties = $this->properties;
        }

        /** @var  PropertyGroup $PropertyGroupObj */
        foreach ($this->propertyGroupChildren as $PropertyGroupObj)
        {
            /**
             * note - recursive call
             */
            $this->all_properties = $this->all_properties->merge($PropertyGroupObj->getAllProperties());
        }

        /**
         * de-dup
         */
        $this->all_properties = $this->all_properties->unique(
            function ($item)
            {
                return $item->id;
            }
        );
        return $this->all_properties;
    }

    /**
     * @param array $options
     * @return Model|PropertyGroup
     * @throws \App\Waypoint\Exceptions\ValidationException
     */
    public function save(array $options = [])
    {
        return parent::save($options);
    }

    /**
     * @param $PropertyObj
     * @return \App\Waypoint\Collection|mixed
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function addProperty($PropertyObj)
    {
        $PropertyGroupPropertyRepositoryObj = App::make(PropertyGroupPropertyRepository::class);
        if ( ! $PropertyGroupPropertyRepositoryObj->findByField(
            [
                'property_id'       => $PropertyObj->id,
                'property_group_id' => $this->id,
            ]
        )->first())
        {
            $PropertyGroupPropertyRepositoryObj->create(
                [
                    'property_id'       => $PropertyObj->id,
                    'property_group_id' => $this->id,
                ]
            );
        }
    }

    /**
     * @return \App\Waypoint\Collection|mixed
     */
    public function getActiveLeaseDetailObjArr()
    {
        return collect_waypoint(
            $this->properties
                ->map(
                    function (Property $PropertyObj)
                    {
                        return $PropertyObj->getActiveLeaseDetailObjArr();
                    }
                )->flatten()
        );
    }

    /**
     * @return \App\Waypoint\Collection|mixed
     */
    public function getActiveUniqueLeaseDetailObjArr()
    {
        return collect_waypoint(
            $this->properties
                ->map(
                    function (Property $PropertyObj)
                    {
                        return $PropertyObj->getActiveUniqueLeaseDetailObjArr();
                    }
                )->flatten()
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function propertyLeaseRollups()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            PropertyLeaseRollup::class,
            'property_group_properties',
            'property_group_id',
            'property_id'
        );
    }

    /**
     * @return mixed
     */
    public function suppress_pre_calc_events()
    {
        if (isset($this->client->getConfigJSON()->SUPPRESS_PRE_CALC_EVENTS))
        {
            return $this->client->getConfigJSON()->SUPPRESS_PRE_CALC_EVENTS;
        }
        return config('waypoint.suppress_pre_calc_events', true);
    }

    /**
     * @return mixed
     */
    public function suppress_pre_calc_usage()
    {
        if (isset($this->client->getConfigJSON()->SUPPRESS_PRE_CALC_USAGE))
        {
            return $this->client->getConfigJSON()->SUPPRESS_PRE_CALC_USAGE;
        }
        return config('waypoint.suppress_pre_calc_usage', true);
    }

    /**
     * @param Carbon $RequestedFromDateObj
     * @param Carbon $RequestedToDateObj
     * @return mixed
     */
    public function nativeAccountAmountsFilteredObjArr(Carbon $RequestedFromDateObj, Carbon $RequestedToDateObj)
    {
        return $this
            ->properties
            ->map(
                function (Property $PropertyObj) use ($RequestedFromDateObj, $RequestedToDateObj)
                {
                    return $PropertyObj->nativeAccountAmountsFiltered->filter(
                        function (NativeAccountAmount $NativeAccountAmountObj) use ($RequestedFromDateObj, $RequestedToDateObj)
                        {
                            return
                                $NativeAccountAmountObj->month_year_timestamp->greaterThanOrEqualTo($RequestedFromDateObj) &&
                                $NativeAccountAmountObj->month_year_timestamp->lessThanOrEqualTo($RequestedToDateObj);
                        }
                    );
                }
            )
            ->flatten();
    }
}
