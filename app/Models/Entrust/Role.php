<?php namespace App\Waypoint\Models\Entrust;

use App\Waypoint\Models\PermissionRole;
use App\Waypoint\Models\RoleUser;
use Zizaco\Entrust\EntrustRole;
use Zizaco\Entrust\Traits\EntrustRoleTrait;

/**
 * Class Role
 * @package App\Waypoint\Models\Entrust
 */
class Role extends EntrustRole
{
    use EntrustRoleTrait;

    /**
     * @var array $rules
     */
    public static $rules = [];

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class, 'permission_roles', 'role_id', 'permission_id'
        );
    }

    public function permissionRoles()
    {
        return $this->hasMany(PermissionRole::class, 'role_id', 'id');
    }

    public function roleUsers()
    {
        return $this->hasMany(RoleUser::class, 'role_id', 'id');
    }
}
