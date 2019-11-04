<?php namespace App\Waypoint\Models;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Model;
use App\Waypoint\ModelDateFormatterTrait;
use App\Waypoint\Models\Entrust\Role as EntrustRole;
use App\Waypoint\ModelSaveAndValidateTrait;
use DB;

/**
 * @method static Role find($id, $columns = ['*']) desc
 * @method static Role where($column, $operator = null, $value = null, $boolean = 'and') desc
 * @method static Role first() desc
 */
class Role extends EntrustRole
{
    use ModelSaveAndValidateTrait;
    use ModelDateFormatterTrait;

    /**
     * @var array $rules
     */
    public static $rules = [];

    /**
     * Validation rules which get 'merged' with self::$baseRules into self::$rules at $this::__constructor() time
     *
     * @var array
     */
    public static $baseRules = [
        'id'           => 'sometimes|nullable|integer',
        'name'         => 'sometimes|max:255',
        'display_name' => 'sometimes|max:255',
        'description'  => 'sometimes',
        'created_at'   => 'sometimes',
        'updated_at'   => 'sometimes',
    ];

    /** @var string $table */
    protected $table = "roles";

    /** @var array $fillable */
    protected $fillable = [
        "id",
        "name",
        "display_name",
        "description",
    ];
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        "id"           => "integer",
        "name"         => "string",
        "display_name" => "string",
        "description"  => "string",
    ];

    const WAYPOINT_ROOT_ROLE                 = 'Root';
    const WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE = 'WaypointSystemAdministrator';
    const WAYPOINT_ASSOCIATE_ROLE            = 'WaypointAssociate';
    const CLIENT_ADMINISTRATIVE_USER_ROLE    = 'ClientAdmin';
    const CLIENT_GENERIC_USER_ROLE           = 'ClientUser';
    public static $waypoint_system_roles = [
        self::WAYPOINT_ROOT_ROLE,
        self::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE,
        self::WAYPOINT_ASSOCIATE_ROLE,
        self::CLIENT_ADMINISTRATIVE_USER_ROLE,
        self::CLIENT_GENERIC_USER_ROLE,
    ];

    /**
     * @param Role ::class $role
     * @throws \Exception
     */
    public static function deleteHasMany(Role $role)
    {
        /** @var PermissionRole $permissionRole */
        foreach ($role->permissionRoles as $permissionRole)
        {
            $permissionRole->delete();
        }

        /** @var RoleUser $roleUser */
        foreach ($role->roleUsers as $roleUser)
        {
            $roleUser->delete();
        }
    }

    /**
     * @param $MODELNAME $::class, $role
     * @throws \Exception
     */
    public static function deleteHasOne(Role $role)
    {

    }

    public static function boot()
    {
        parent::boot();
        self::deleting(
            function (Role $role)
            {
                self::deleteHasMany($role);
                self::deleteHasOne($role);
            }
        );
    }

    /**
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(self::$baseRules, self::$rules);
        }

        Model::unset_if_set(['created_at', 'updated_at'], $rules);

        return $rules;
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"           => $this->id,
            "name"         => $this->name,
            "display_name" => $this->display_name,
            "description"  => $this->description,
            "model_name"   => self::class,
        ];
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass()
    {
        return Role::class;
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param array $models
     * @return \App\Waypoint\Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    /**
     * @return string
     *
     * @todo deal with this - getShortModelName is here since this does not inherit App/Waypoint/Repository for Entrust reasons
     */
    public function getShortModelName()
    {
        $model_name = explode('\\', get_class($this));
        $return_me  = array_pop($model_name);
        return $return_me;
    }

    /**
     * because Role is an oddball object
     * @return bool|null
     * @throws \Exception
     */
    public function delete(array $options = [])
    {
        $this->name = substr(sha1(time() . mt_rand()), 0, 40);
        $this->save();
        /**
         * shameless hack to get arount entrust soft delete issue
         * return parent::delete();
         */

        DB::delete(
            DB::raw(
                'DELETE
                        FROM roles
                        WHERE id= :role_id'
            ),
            [
                'role_id' => $this->id,
            ]
        );
        return true;
    }

    /**
     * @var array
     * @todo deal with this - This is here since this does not inherit App/Waypoint/Model for Entrust reasons
     */
    public static $hasMany_arr = [
    ];

    /**
     * @var array
     * @todo deal with this - This is here since this does not inherit App/Waypoint/Model for Entrust reasons
     */
    public static $hasOne_arr = [
        /**
         * Remember this is an oddball class
         */
    ];

    /**
     * @var array
     * @todo deal with this - This is here since this does not inherit App/Waypoint/Model for Entrust reasons
     */
    public static $belongsTo_arr = [
        /**
         * Remember this is an oddball class
         */
    ];

    /**
     * @var array
     * @todo deal with this - This is here since this does not inherit App/Waypoint/Model for Entrust reasons
     */
    public static $belongsToMany_arr = [
        /**
         * Remember this is an oddball class
         */
    ];

    /**
     * @return array
     */
    public function getHasManyArr()
    {
        return self::$hasMany_arr;
    }

    /**
     * @return array
     */
    public function getHasOneArr()
    {
        return self::$hasOne_arr;
    }

    /**
     * @return array
     */
    public function getBelongsToArr()
    {
        return self::$belongsTo_arr;
    }

    /**
     * @return array
     */
    public function getBelongsToManyArr()
    {
        return self::$belongsToMany_arr;
    }

    /**
     * @return array
     */
    public function parentToArray()
    {
        return parent::attributesToArray();
    }
}
