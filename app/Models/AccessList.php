<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\AuditableTrait;
use App\Waypoint\CommentableTrait;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\AccessListPropertyRepository;
use App\Waypoint\Repositories\AccessListUserRepository;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;

/**
 * Class AccessList
 * @package App\Waypoint\Models
 */
class AccessList extends AccessListModelBase implements AuditableContract, UserResolver
{
    use AuditableTrait;

    use CommentableTrait;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'client_id'   => 'required|integer|unique_with:access_lists,name,object_id',
        /**
         * min length 2 to allow state abbr's
         */
        'name'        => 'required|min:2|max:255',
        'description' => 'sometimes|nullable|min:3|max:255',
    ];

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'name',
        'description',
    ];

    /**
     * AccessList constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id"                       => $this->id,
            "name"                     => $this->name,
            "client_id"                => $this->client_id,
            "description"              => $this->description,
            "access_list_property_ids" => $this->accessListProperties->pluck('property_id'),
            "num_properties"           => $this->accessListProperties ? $this->accessListProperties->count() : 0,
            "num_users"                => $this->users ? $this->users->count() : 0,
            "is_all_access_list"       => $this->is_all_access_list,
            "comments"                 => $this->getComments()->toArray(),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accessListPropertiesSummary()
    {
        return $this->hasMany(
            AccessListPropertySummary::class,
            'access_list_id',
            'id'
        );
    }

    /**
     * @param $PropertyObj
     */
    public function addProperty($PropertyObj)
    {
        $AccessListPropertyRepositoryObj = App::make(AccessListPropertyRepository::class);
        if ( ! $AccessListPropertyRepositoryObj->findByField(
            [
                'property_id'    => $PropertyObj->id,
                'access_list_id' => $this->id,
            ]
        )->first())
        {
            $AccessListPropertyRepositoryObj->create(
                [
                    'property_id'    => $PropertyObj->id,
                    'access_list_id' => $this->id,
                ]
            );
        }
    }

    /**
     * @param $PropertyObj
     */
    public function removeProperty($PropertyObj)
    {
        $AccessListPropertyRepositoryObj = App::make(AccessListPropertyRepository::class);
        if ($AccessListPropertyObj = $AccessListPropertyRepositoryObj->findByField(
            [
                'property_id'    => $PropertyObj->id,
                'access_list_id' => $this->id,
            ]
        )->first())
        {
            $AccessListPropertyObj->delete();
        }
    }

    public function addUser($UserObj)
    {
        $AccessListUserRepositoryObj = App::make(AccessListUserRepository::class);
        if ( ! $AccessListUserRepositoryObj->findByField(
            [
                'user_id'        => $UserObj->id,
                'access_list_id' => $this->id,
            ]
        )->first())
        {
            $AccessListUserRepositoryObj->create(
                [
                    'user_id'        => $UserObj->id,
                    'access_list_id' => $this->id,
                ]
            );
        }
    }

    /**
     * @param $UserObj
     */
    public function removeUser($UserObj)
    {
        $AccessListUserRepositoryObj = App::make(AccessListUserRepository::class);
        if ($AccessListUserObj = $AccessListUserRepositoryObj->findByField(
            [
                'user_id'        => $UserObj->id,
                'access_list_id' => $this->id,
            ]
        )->first())
        {
            $AccessListUserObj->delete();
        }
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function accessListUsers()
    {
        if (
            self::$requesting_user_role === null ||
            self::$requesting_user_role == App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE ||
            self::$requesting_user_role == App\Waypoint\Models\Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE
        )
        {
            return $this->hasMany(
                AccessListUser::class,
                'access_list_id',
                'id'
            );
        }
        else
        {
            $hidden_user_id_arr = User::where('is_hidden', true)->get()->pluck('id')->toArray();
            return $this->hasMany(
                AccessListUser::class,
                'access_list_id',
                'id'
            )->whereNotIn('user_id', $hidden_user_id_arr);
        }
    }
}
