<?php

namespace App\Waypoint\Models;

use App\Waypoint\Collection;
use App\Waypoint\Model;
use App\Waypoint\Models\Entrust\Permission as EntrustPermission;
use App\Waypoint\ModelSaveAndValidateTrait;

/**
 * @method static Permission find($id, $columns = ['*']) desc
 */
class Permission extends EntrustPermission
{
    use ModelSaveAndValidateTrait;

    /** @var string $table */
    protected $table = 'permissions';

    /** @var string $fillable */
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

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_roles', 'permission_id', 'role_id');
    }

    public function permissionRoles()
    {
        return $this->hasMany(PermissionRole::class, 'permission_id', 'id');
    }

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

        'id' => 'sometimes|nullable|integer',
    ];

    /**
     * Validation rules which get 'merged' with self::$baseRules into self::$rules at $this::__constructor() time
     *
     * @var array
     */
    public static $baseRules = [
        'id'           => 'sometimes|integer',
        'name'         => 'sometimes|max:255',
        'display_name' => 'sometimes|max:255',
        'description'  => 'sometimes',
        'created_at'   => 'sometimes',
        'updated_at'   => 'sometimes',
    ];

    /**
     * @param Permission $permission
     * @throws \Exception
     */
    public static function deleteHasMany(Permission $permission)
    {
        /** @var PermissionRole $permissionRole */
        foreach ($permission->permissionRoles as $permissionRole)
        {
            $permissionRole->delete();
        }
    }

    /**
     * @param $MODELNAME $::class, $permission
     * @throws \Exception
     */
    public static function deleteHasOne(Permission $permission)
    {

    }

    /**
     *
     */
    public static function boot()
    {
        parent::boot();
        self::deleting(
            function (Permission $permission)
            {
                self::deleteHasMany($permission);
                self::deleteHasOne($permission);
            }
        );
    }

    /**
     * @param null $rules
     * @param null $object_id
     * @return array|null
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
    function toArray()
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
        return Permission::class;
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
     * because Permossion is an oddball object
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {
        $this->name = substr(sha1(time() . mt_rand()), 0, 40);
        $this->save();
        return parent::delete();
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
