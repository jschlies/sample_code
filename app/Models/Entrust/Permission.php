<?php namespace App\Waypoint\Models\Entrust;

use App\Waypoint\Models\PermissionRole;
use Zizaco\Entrust\EntrustPermission;
use Zizaco\Entrust\Traits\EntrustPermissionTrait;

/**
 * Class Permission
 */
class Permission extends EntrustPermission
{
    use EntrustPermissionTrait;

    public static $rules = [];

    protected $table = 'permissions';
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
}
