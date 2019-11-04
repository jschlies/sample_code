<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\AuditableTrait;
use App\Waypoint\CanCommentTrait;
use App\Waypoint\CanConfigJSONTrait;
use App\Waypoint\CanImageJSONTrait;
use App\Waypoint\CanPreCalcJSONTrait;
use App\Waypoint\Collection;
use App\Waypoint\Events\CalculateVariousPropertyListsEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\ValidationException;
use App\Waypoint\GetEntityTagsTrait;
use App\Waypoint\Model;
use App\Waypoint\ModelDateFormatterTrait;
use App\Waypoint\Models\Entrust\User as EntrustUser;
use App\Waypoint\ModelSaveAndValidateTrait;
use App\Waypoint\RelatedUserTrait;
use App\Waypoint\Repositories\NativeAccountTypeSummaryRepository;
use Auth0\Login\Auth0User;
use Cache;
use Carbon\Carbon;
use Config;
use DB;
use function get_class;
use function is_object;
use function json_decode;
use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;

/**
 * @method static User find($id, $columns = ['*'])
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and')
 */
class User extends EntrustUser implements AuditableContract, UserResolver
{
    use ModelSaveAndValidateTrait;
    use GetEntityTagsTrait;
    use AuditableTrait;
    use CanCommentTrait;
    use Notifiable;
    use CanConfigJSONTrait;
    use CanImageJSONTrait;
    use CanPreCalcJSONTrait;
    use RelatedUserTrait;
    use ModelDateFormatterTrait;

    /** @var null|EntityTagEntity */
    protected $UserConfigObj = null;

    /**
     * Route notifications for the mail channel.
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        return $this->email;
    }

    /**
     * @return bool needed for comments
     */
    public function isAdmin()
    {
        return
            $this->roleIsAtLeast(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
    }

    const  SUPERUSER_EMAIL = 'superuser@waypointbuilding.com';

    /** @var string $table */
    public $table = "users";
    /** @var [] */
    protected $role_named_arr = null;
    /** @var PropertyGroup */
    protected $AllPropertyGroupObj = null;

    /** @var string $fillable */
    public $fillable = [
        "id",
        "firstname",
        "lastname",
        "email",
        "active_status",
        "active_status_date",
        "client_id",
        "config_json",
        "image_json",
        "user_name",
        "is_hidden",
        'salutation',
        'suffix',
        'work_number',
        'mobile_number',
        'company',
        'location',
        'job_title',
        'user_invitation_status',
        'user_invitation_status_date',
        'last_login_date',
        'first_login_date',
        'authenticating_entity_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        "id"                          => "integer",
        "firstname"                   => "string",
        "lastname"                    => "string",
        "email"                       => "string",
        "password"                    => "string",
        'user_name'                   => "string",
        "remember_token"              => "string",
        "active_status"               => "string",
        "client_id"                   => "integer",
        "config_json"                 => "string",
        "image_json"                  => "string",
        "creation_auth0_response"     => "string",
        "one_time_token"              => "string",
        "one_time_token_expiry"       => "string",
        'salutation'                  => "string",
        'suffix'                      => "string",
        'work_number'                 => "string",
        'mobile_number'               => "string",
        'company'                     => "string",
        'location'                    => "string",
        'job_title'                   => "string",
        'user_invitation_status'      => "string",
        'user_invitation_status_date' => "datetime",
        'last_login_date'             => "datetime",
        'first_login_date'            => "datetime",
        'authenticating_entity_id'    => "integer",
    ];

    protected $Auth0UserObj = null;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'id'                          => 'sometimes|nullable|integer',
        'client_id'                   => 'required|integer',
        'firstname'                   => 'required|min:2|max:255',
        'lastname'                    => 'required|min:2|max:255',
        'user_name'                   => 'sometimes|max:255',
        'display_email'               => 'sometimes|max:255',
        'active_status'               => 'required|max:255',
        'user_invitation_status'      => 'sometimes|max:255',
        'user_invitation_status_date' => 'sometimes|nullable|date',
        'email'                       => 'required|email|max:255',
    ];

    /**
     * Validation rules which get 'merged' with self::$baseRules into self::$rules at $this::__constructor() time
     *
     * @var array
     */
    public static $baseRules = [
        'id'                          => 'sometimes|integer',
        'client_id'                   => 'sometimes|integer',
        'firstname'                   => 'sometimes|max:255',
        'lastname'                    => 'sometimes|max:255',
        'user_name'                   => 'sometimes|max:255',
        'email'                       => 'sometimes|max:255',
        'display_email'               => 'sometimes|max:255',
        'active_status'               => 'sometimes|max:255',
        'user_invitation_status'      => 'sometimes|max:255',
        'user_invitation_status_date' => 'sometimes|max:255',
        'created_at'                  => 'sometimes',
        'updated_at'                  => 'sometimes',
    ];

    const ACTIVE_STATUS_ACTIVE   = 'active';
    const ACTIVE_STATUS_INACTIVE = 'inactive';
    const ACTIVE_STATUS_LOCKED   = 'locked';
    public static $active_status_values = [
        self::ACTIVE_STATUS_ACTIVE,
        self::ACTIVE_STATUS_INACTIVE,
        self::ACTIVE_STATUS_LOCKED,
    ];

    const USER_INVITATION_STATUS_NEVER_INVITED   = 'never_invited';
    const USER_INVITATION_STATUS_PENDING         = 'invite_pending';
    const USER_INVITATION_STATUS_EXPIRED         = 'invite_expired';
    const USER_INVITATION_STATUS_ACCEPTED        = 'invite_accepted';
    const USER_INVITATION_STATUS_REVOKED         = 'invite_revoked';
    const USER_INVITATION_STATUS_ADDED_VIA_ADMIN = 'added_via_admin';
    public static $user_invitation_status_values = [
        self::USER_INVITATION_STATUS_NEVER_INVITED,
        self::USER_INVITATION_STATUS_PENDING,
        self::USER_INVITATION_STATUS_EXPIRED,
        self::USER_INVITATION_STATUS_ACCEPTED,
        self::USER_INVITATION_STATUS_REVOKED,
        self::USER_INVITATION_STATUS_ADDED_VIA_ADMIN,
    ];

    const MENTIONED_NOTIFICATIONS_FLAG               = 'MENTIONED_NOTIFICATIONS';
    const VARIANCE_MENTIONED_NOTIFICATIONS_FLAG      = 'VARIANCE_MENTIONED_NOTIFICATIONS';
    const VARIANCE_COMMENTED_NOTIFICATIONS_FLAG      = 'VARIANCE_COMMENTED_NOTIFICATIONS';
    const VARIANCE_APPROVED_NOTIFICATIONS_FLAG       = 'VARIANCE_APPROVED_NOTIFICATIONS';
    const VARIANCE_LOCKED_NOTIFICATIONS_FLAG         = 'VARIANCE_LOCKED_NOTIFICATIONS';
    const VARIANCE_MARKED_NOTIFICATIONS_FLAG         = 'VARIANCE_MARKED_NOTIFICATIONS';
    const VARIANCE_EXPLAINED_NOTIFICATIONS_FLAG      = 'VARIANCE_EXPLAINED_NOTIFICATIONS';
    const VARIANCE_RESOLVED_NOTIFICATIONS_FLAG       = 'VARIANCE_RESOLVED_NOTIFICATIONS';
    const OPPORTUNITIES_CREATED_NOTIFICATIONS_FLAG   = 'OPPORTUNITIES_CREATED_NOTIFICATIONS';
    const OPPORTUNITIES_UPDATED_NOTIFICATIONS_FLAG   = 'OPPORTUNITIES_UPDATED_NOTIFICATIONS';
    const OPPORTUNITIES_MENTIONED_NOTIFICATIONS_FLAG = 'OPPORTUNITIES_MENTIONED_NOTIFICATIONS';
    const OPPORTUNITIES_COMMENTED_NOTIFICATIONS_FLAG = 'OPPORTUNITIES_COMMENTED_NOTIFICATIONS';
    public static $user_notification_flags = [
        self::VARIANCE_MENTIONED_NOTIFICATIONS_FLAG,
        self::VARIANCE_COMMENTED_NOTIFICATIONS_FLAG,
        self::VARIANCE_APPROVED_NOTIFICATIONS_FLAG,
        self::VARIANCE_LOCKED_NOTIFICATIONS_FLAG,
        self::VARIANCE_MARKED_NOTIFICATIONS_FLAG,
        self::VARIANCE_EXPLAINED_NOTIFICATIONS_FLAG,
        self::VARIANCE_RESOLVED_NOTIFICATIONS_FLAG,
        self::OPPORTUNITIES_CREATED_NOTIFICATIONS_FLAG,
        self::OPPORTUNITIES_UPDATED_NOTIFICATIONS_FLAG,
        self::OPPORTUNITIES_MENTIONED_NOTIFICATIONS_FLAG,
        self::OPPORTUNITIES_COMMENTED_NOTIFICATIONS_FLAG,
    ];
    const DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG = 'DEFAULT_ANALYTICS_REPORT_TEMPLATE';

    /** @var boolean */
    public static $suppress_use_of_pre_calcs = false;

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'firstname',
        'lastname',
        'email',
        'active_status',
        'active_status_date',
        'user_name',
        'is_hidden',
        'salutation',
        'suffix',
        'work_number',
        'mobile_number',
        'company',
        'location',
        'user_invitation_status',
        'user_invitation_status_date',
        'job_title',
        'first_login_date',
        'authenticating_entity_id',
    ];

    /**
     * User constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @return bool
     */
    public static function boot()
    {
        parent::boot();
    }

    /**
     * this is here because Entrust trait's delete() method does not return true upon successful. Sigh
     *
     * @param array $options
     * @return bool
     */
    public function delete(array $options = [])
    {
        $UserRepositoryObj = App::make(App\Waypoint\Repositories\UserRepository::class);
        $UserRepositoryObj->delete($this->id);
        return true;
    }

    /**
     * @param array|string $ability
     * @param array $arguments
     * @return bool
     * @todo move this logic into Policy classes
     *       see AuthServiceProvider and https://laravel.com/docs/5.1/authorization
     *
     */
    public function can($ability, $arguments = [])
    {
        return parent::can($ability, $arguments);
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        $access_list_names_arr = $this->accessLists->pluck('name')->toArray();

        return [
            "id"                          => $this->id,
            "firstname"                   => $this->firstname,
            "lastname"                    => $this->lastname,
            "client_id"                   => $this->client_id,
            "email"                       => $this->email,
            "active_status"               => $this->active_status,
            "active_status_date"          => is_object($this->active_status_date) ? $this->active_status_date->format('Y-m-d H:i:s') : $this->active_status_date,
            "first_login_date"            => $this->perhaps_format_date($this->first_login_date),
            "last_login_date"             => $this->perhaps_format_date($this->last_login_date),
            "user_name"                   => $this->user_name,
            "is_hidden"                   => $this->is_hidden ? true : false,
            'salutation'                  => $this->salutation,
            'suffix'                      => $this->suffix,
            'work_number'                 => $this->work_number,
            'mobile_number'               => $this->mobile_number,
            'company'                     => $this->company,
            'location'                    => $this->location,
            'job_title'                   => $this->job_title,
            'user_invitation_status'      => $this->user_invitation_status,
            "user_invitation_status_date" => is_object($this->user_invitation_status_date) ? $this->user_invitation_status_date->format('Y-m-d H:i:s') : $this->user_invitation_status_date,

            "config_json" => $this->config_json ? json_decode($this->config_json, true) : [],
            "image_json"  => $this->image_json ? json_decode($this->image_json, true) : [],

            "roles"                    => $this->getRoleNamesArr(),
            'highest_role'             => $this->getHighestRole(),
            "authenticating_entity_id" => $this->authenticating_entity_id,
            'access_list_names_arr'    => $access_list_names_arr,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
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

    public function getAuth0UserObj()
    {
        return $this->Auth0UserObj;
    }

    public function setAuth0UserObj(Auth0User $Auth0UserObj)
    {
        $this->Auth0UserObj = $Auth0UserObj;
    }

    /**
     * @return array
     */
    public function getRoleNamesArr()
    {
        if ($this->role_named_arr == null)
        {
            $this->role_named_arr = $this->cachedRoles()->pluck('name')->toArray();
        }
        return $this->role_named_arr;
    }

    /**
     * @return null|boolean
     *
     */
    public function getHighestRole()
    {
        if ($this->hasRole(Role::WAYPOINT_ROOT_ROLE))
        {
            return Role::WAYPOINT_ROOT_ROLE;
        }
        if ($this->hasRole(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE))
        {
            return Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE;
        }
        if ($this->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE))
        {
            return Role::WAYPOINT_ASSOCIATE_ROLE;
        }
        if ($this->hasRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE))
        {
            return Role::CLIENT_ADMINISTRATIVE_USER_ROLE;
        }
        if ($this->hasRole(Role::CLIENT_GENERIC_USER_ROLE))
        {
            return Role::CLIENT_GENERIC_USER_ROLE;
        }
        return null;
    }

    /**
     * @param $least_role
     * @return bool
     * @throws GeneralException
     */
    public function roleIsAtLeast($least_role)
    {
        switch ($least_role)
        {
            case Role::WAYPOINT_ROOT_ROLE:
                return $this->hasRole(Role::WAYPOINT_ROOT_ROLE);

            case Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE:
                return
                    $this->hasRole(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE) ||
                    $this->hasRole(Role::WAYPOINT_ROOT_ROLE);

            case Role::WAYPOINT_ASSOCIATE_ROLE:
                return
                    $this->hasRole(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE) ||
                    $this->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE) ||
                    $this->hasRole(Role::WAYPOINT_ROOT_ROLE);

            case Role::CLIENT_ADMINISTRATIVE_USER_ROLE:
                return
                    $this->hasRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE) ||
                    $this->hasRole(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE) ||
                    $this->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE) ||
                    $this->hasRole(Role::WAYPOINT_ROOT_ROLE);

            case Role::CLIENT_GENERIC_USER_ROLE:
                return
                    true;

            default:
                throw new GeneralException('Invalid role encountered');
        }
    }

    /**
     * @param PropertyGroup $AllPropertyGroupObj
     */
    public function setAllPropertyGroupObj($AllPropertyGroupObj)
    {
        $this->AllPropertyGroupObj = $AllPropertyGroupObj;
    }

    /**
     * @return string
     */
    public function getRolesAsString()
    {
        return $this->cachedRoles()->implode('name', ',');
    }

    /**
     * @return int
     */
    public static function generate_api_key()
    {
        return mt_rand(10000000, 99999999);
    }

    /**
     * @return array
     */
    public function getFavoriteThings()
    {
        $return_me = [];
        /** @var FavoriteGroup $FavoriteGroupObj */
        foreach ($this->getFavoriteGroups() as $FavoriteGroupObj)
        {
            /** @var Favorite $FavoriteObj */
            foreach ($FavoriteGroupObj->getFavoritesForUser($this->id) as $FavoriteObj)
            {
                if ($FavoriteGroupObj->entity_model == Property::class && $PropertyObj = Property::find($FavoriteObj->entity_id))
                {
                    $return_me[$FavoriteGroupObj->entity_model][] = [
                        'property_id'          => $FavoriteObj->entity_id,
                        'name'                 => $PropertyObj->name,
                        'favorite_id'          => $FavoriteObj->id,
                        'entity_tag_entity_id' => $FavoriteObj->id,
                    ];
                }
                elseif ($FavoriteGroupObj->entity_model == PropertyGroup::class && $PropertyGroupObj = PropertyGroup::find($FavoriteObj->entity_id))
                {
                    $return_me[$FavoriteGroupObj->entity_model][] = [
                        'property_group_id'    => $FavoriteObj->entity_id,
                        'name'                 => $PropertyGroupObj->name,
                        'favorite_id'          => $FavoriteObj->id,
                        'entity_tag_entity_id' => $FavoriteObj->id,
                    ];
                }
            }
        }
        return $return_me;
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

        $rules['active_status']          = 'required|string|max:255|in:' . implode(',', User::$active_status_values);
        $rules['user_invitation_status'] = 'required|string|max:255|in:' . implode(',', User::$user_invitation_status_values);

        Model::unset_if_set(['id', 'created_at', 'updated_at'], $rules);

        foreach ($rules as $field => $rule)
        {
            $rule_as_array = explode(',', $rule);
            if ($rule_as_array[count($rule_as_array) - 1] == 'object_id')
            {
                if ($object_id)
                {
                    $rule_as_array[count($rule_as_array) - 1] = $object_id;
                }
                else
                {
                    array_pop($rule_as_array);
                }
                $rules[$field] = implode(',', $rule_as_array);
            }

        }

        return $rules;
    }

    /**
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_create_request_rules($rules = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(self::$baseRules, self::$rules);
        }

        $rules['active_status'] = 'required|string|max:255|in:' . implode(',', User::$active_status_values);

        /**
         * clear out Timestamp stuff
         */
        Model::unset_if_set(['created_at', 'updated_at'], $rules);

        foreach ($rules as $field => $rule)
        {
            $rule_as_array = explode(',', $rule);
            if ($rule_as_array[count($rule_as_array) - 1] == 'object_id')
            {
                array_pop($rule_as_array);
                $rules[$field] = implode(',', $rule_as_array);
            }

        }
        return $rules;
    }

    /**
     * @param null $rules
     * @return array
     * @throws GeneralException
     */
    public static function get_update_request_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(self::$baseRules, self::$rules);
        }

        $rules['active_status'] = 'required|string|max:255|in:' . implode(',', User::$active_status_values);
        Model::unset_if_set(['created_at', 'updated_at'], $rules);

        /**
         * @todo mostly for unit test reasons, we do not require foreign keys when posting an update. Fix this
         * by changing how 'update' unit tests build sample data
         */
        foreach ($rules as $var => $rule)
        {
            if (preg_match("/_id$/", $var))
            {
                Model::unset_if_set([$var], self::$rules);
            }
        }

        foreach ($rules as $field => $rule)
        {
            $rule_as_array = explode(',', $rule);
            if ($rule_as_array[count($rule_as_array) - 1] == 'object_id')
            {
                if ($object_id)
                {
                    $rule_as_array[count($rule_as_array) - 1] = $object_id;
                }
                else
                {
                    array_pop($rule_as_array);
                }
                $rules[$field] = implode(',', $rule_as_array);
            }

        }

        return $rules;
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass()
    {
        return User::class;
    }

    /**
     * @return array
     * @todo deal with this - This is here since this does not inherit App/Waypoint/Model for Entrust reasons
     */
    public function getBelongsToArr()
    {
        return ['client'];
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
    public function getBelongsToManyArr()
    {
        return self::$belongsToMany_arr;
    }

    /**
     * Save a new or updated model and return the instance.
     *
     * @param array $options
     * @return $this
     * @throws GeneralException
     * @throws ValidationException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function save(array $options = [])
    {
        if ( ! self::isSuspendValidation())
        {
            if ($rules = $this::get_model_rules(null, $this->id ?: null))
            {
                $thing_to_validate = $this->parentToArray();
                $ValidatorObj      = \Validator::make($thing_to_validate, $rules);

                if ($ValidatorObj->fails())
                {
                    $this->errors           = $ValidatorObj->errors();
                    $ValidationExceptionObj = new ValidationException($ValidatorObj->errors());
                    $ValidationExceptionObj->setValidationErrors($ValidatorObj->errors());
                    throw $ValidationExceptionObj;
                }
            }
        }
        parent::save($options);

        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function propertyDetails()
    {
        return $this->hasMany(
            PropertyDetail::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function propertyGroupDetails()
    {
        return $this->hasMany(
            PropertyGroupDetail::class,
            'user_id',
            'id'
        );
    }

    /**
     * README README
     * You may think this goes into either App\Waypoint\Models\Entrust\User or
     * App\Waypoint/EntrustUserTrait but given how traits work (and how
     * overloads in traits are dealt with in php), this is the place.
     * See http://php.net/manual/en/language.oop5.traits.php Precedence Order
     *
     * @param mixed $RoleObj
     * @param bool $suppress_event - needed to speed up DataSeeder
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function attachRole($RoleObj, $suppress_event = false)
    {
        if (Cache::getStore() instanceof TaggableStore)
        {
            Cache::tags(Config::get('entrust.role_user_table'))->flush();
        }
        if ( ! $this->hasRole($RoleObj->name))
        {
            parent::attachRole($RoleObj);
        }

        if ($RoleObj->name == Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE)
        {
            $this->attachRole(Role::where('name', Role::WAYPOINT_ASSOCIATE_ROLE)->first(), $suppress_event);
            $this->attachRole(Role::where('name', Role::CLIENT_ADMINISTRATIVE_USER_ROLE)->first(), $suppress_event);
            $this->attachRole(Role::where('name', Role::CLIENT_GENERIC_USER_ROLE)->first(), $suppress_event);
        }
        elseif ($RoleObj->name == Role::WAYPOINT_ASSOCIATE_ROLE)
        {
            $this->attachRole(Role::where('name', Role::CLIENT_ADMINISTRATIVE_USER_ROLE)->first(), $suppress_event);
            $this->attachRole(Role::where('name', Role::CLIENT_GENERIC_USER_ROLE)->first(), $suppress_event);
        }
        elseif ($RoleObj->name == Role::CLIENT_ADMINISTRATIVE_USER_ROLE)
        {
            $this->attachRole(Role::where('name', Role::CLIENT_GENERIC_USER_ROLE)->first(), $suppress_event);
        }
        if (Cache::getStore() instanceof TaggableStore)
        {
            Cache::tags(Config::get('entrust.role_user_table'))->flush();
        }
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $RoleObj
     */
    public function detachRole($RoleObj)
    {
        parent::detachRole($RoleObj);

        DB::delete(
            DB::raw(
                '
                    DELETE FROM access_list_users 
                        WHERE 
                            user_id = :USER_ID AND
                            access_list_id = :ACCESS_LIST_ID
                '
            ),
            [
                'USER_ID'        => $this->id,
                'ACCESS_LIST_ID' => $this->client->allAccessList->id,
            ]
        );
        if (Cache::getStore() instanceof TaggableStore)
        {
            Cache::tags(Config::get('entrust.role_user_table'))->flush();
        }

        $this->refresh();
        event(
            new CalculateVariousPropertyListsEvent(
                $this->client,
                [
                    'event_trigger_message'        => 'Triggered at ' . __CLASS__ . ':' . __LINE__,
                    'event_trigger_id'             => waypoint_generate_uuid(),
                    'event_trigger_class'          => self::class,
                    'event_trigger_class_instance' => get_class($this),
                    'event_trigger_object_class'   => get_class($this->client),
                    'event_trigger_file'           => __FILE__,
                    'event_trigger_line'           => __LINE__,
                ]
            )
        );
    }

    /**
     * @return bool
     * @throws GeneralException
     * @throws \Exception
     */
    public function canReceiveOpportunitiesNotification($notification_type)
    {
        if ( ! isset($this->getConfigJSON(true)[$notification_type]))
        {
            $this->updateConfig($notification_type, true);
        }
        return (bool) $this->getConfigJSON()->{$notification_type};
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function createdOpportunities()
    {
        return $this->hasMany(
            Opportunity::class,
            'created_by_user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function assignedOpportunities()
    {
        return $this->hasMany(
            Opportunity::class,
            'assigned_to_user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function authenticatingEntity()
    {
        return $this->belongsTo(
            AuthenticatingEntity::class, 'authenticating_entity_id', 'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function notificationLogs()
    {
        return $this->hasMany(
            NotificationLog::class,
            'user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function relatedUsers()
    {
        return $this->hasMany(
            RelatedUser::class,
            'user_id',
            'id'
        );
    }

    /**
     * @return mixed
     */
    public function getAssetTypesOfAccessibleProperties()
    {
        $return_me_arr = [];
        /** @var RelatedUser $RelatedUserObj */
        foreach ($this->getAccessiblePropertyObjArr() as $PropertyObj)
        {
            if ($PropertyObj->asset_type_id && $PropertyObj->assetType->asset_type_name)
            {
                if ( ! isset($return_me_arr[$PropertyObj->assetType->asset_type_name]))
                {
                    $return_me_arr[$PropertyObj->assetType->asset_type_name] = [];
                }
                $return_me_arr[$PropertyObj->assetType->asset_type_name][] = $PropertyObj->id;
            }
        }
        return $return_me_arr;
    }

    /**
     * @return mixed
     */
    public function getStandardAttributesOfAccessibleProperties()
    {
        $return_me_arr = [];
        /** @var Property $PropertyObj */
        foreach ($this->getAccessiblePropertyObjArr() as $PropertyObj)
        {
            foreach (Property::$standard_attributes_arr as $standard_attribute)
            {
                $standard_attribute_value = $PropertyObj->$standard_attribute;
                if (is_object($standard_attribute_value) && get_class($standard_attribute_value) == Carbon::class)
                {
                    $standard_attribute_value = $standard_attribute_value->format('Y-m-d');
                }
                if ( ! isset($return_me_arr[$standard_attribute][$standard_attribute_value]))
                {
                    $return_me_arr[$standard_attribute][$standard_attribute_value] = [];
                }
                $return_me_arr[$standard_attribute][$standard_attribute_value][] = $PropertyObj->id;
            }
        }
        return $return_me_arr;
    }

    /**
     * @return mixed
     */
    public function getCustomAttributesOfAccessibleProperties()
    {
        $return_me_arr = [];
        /** @var Property $PropertyObj */
        foreach ($this->getAccessiblePropertyObjArr() as $PropertyObj)
        {
            $custom_attribute_arr = $PropertyObj->custom_attributes;
            foreach ($custom_attribute_arr as $custom_name => $custom_attribute)
            {
                if ( ! isset($return_me_arr[$custom_name][$custom_attribute]))
                {
                    $return_me_arr[$custom_name][$custom_attribute] = [];
                }
                $return_me_arr[$custom_name][$custom_attribute] = $PropertyObj->id;
            }
        }
        return $return_me_arr;
    }

    /**
     * @return array
     */
    public function getJsonProperties(): array
    {
        return $this->json_properties;
    }

    /**
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function client()
    {
        return $this->belongsTo(
            Client::class, 'client_id', 'id'
        )->with('properties');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function userInvitations()
    {
        return $this->hasMany(
            UserInvitation::class,
            'invitee_user_id',
            'id'
        );
    }

    /**
     * @return bool
     */
    public function defaultsAnalyticsReportTemplateExists()
    {
        $user_config_arr = $this->getConfigJSON(true);
        return (
            isset($user_config_arr[User::DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG])
            &&
            is_int($user_config_arr[User::DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG])
        );
    }

    /**
     * @return mixed
     */
    public function getDefaultAnalyticsReportTemplate()
    {
        if ( ! $report_template_id = $this->getConfigValue(User::DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG))
        {
            throw new GeneralException('cannot find default analytics report template for ' . $this->getDisplayName());
        }
        return App::make(App\Waypoint\Repositories\ReportTemplateRepository::class)->find($report_template_id);
    }

    /**
     * @return Collection
     */
    public function getNativeAcountTypeSummaries(): Collection
    {
        $return_me = new Collection();

        $NativeAccountTypeSummaryForClientArr = App::make(NativeAccountTypeSummaryRepository::class)->getForClient($this->id);

        /** @var NativeAccountTypeSummary $NativeAccountTypeSummaryObj */
        foreach ($NativeAccountTypeSummaryForClientArr as $NativeAccountTypeSummaryObj)
        {
            $NativeAccountTypeSummaryArr                                     = $NativeAccountTypeSummaryObj->toArray();
            $report_template_account_group_id                                = $this->getReportTemplateAccountGroupFromNativeAccountType($NativeAccountTypeSummaryArr['id'])->id;
            $NativeAccountTypeSummaryArr['report_template_account_group_id'] = $report_template_account_group_id;
            if ( ! is_null($NativeAccountTypeSummaryArr['report_template_account_group_id']))
            {
                $return_me[] = $NativeAccountTypeSummaryArr;
            }
        }
        return $return_me;
    }

    /**
     * @param $native_account_type_id
     * @return mixed
     */
    public function getNativeAccountTypeSummaryWithReportTemplateAccountGroupFromNativeAccountType($native_account_type_id)
    {
        $ReportTemplateObj = $this->getDefaultAnalyticsReportTemplate();

        $ReportTemplateAccountGroupRepositoryObj = App::make(App\Waypoint\Repositories\ReportTemplateAccountGroupRepository::class);
        $NativeAccountTypeSummaryRepositoryObj   = App::make(NativeAccountTypeSummaryRepository::class);

        $ReportTemplateAccountGroupObj =
            $ReportTemplateAccountGroupRepositoryObj
                ->findWhere(
                    [
                        'report_template_id'                      => $ReportTemplateObj->id,
                        'parent_report_template_account_group_id' => null,
                        'native_account_type_id'                  => $native_account_type_id,
                    ]
                )->first();

        if ( ! $NativeAccountTypeSummaryObj = $NativeAccountTypeSummaryRepositoryObj->find($native_account_type_id))
        {
            throw new GeneralException('could not find account type summary from id given');
        }

        $NativeAccountTypeSummaryObj->report_template_account_group_id = $ReportTemplateAccountGroupObj->id;

        return $NativeAccountTypeSummaryObj;
    }

    /**
     * @param ReportTemplateAccountGroup $ReportTemplateAccountGroupObj
     * @return NativeAccountTypeSummary
     */
    public function getNativeAccounTypeSummaryIncludingRTAG(ReportTemplateAccountGroup $ReportTemplateAccountGroupObj): NativeAccountTypeSummary
    {
        $NativeAccountTypeSummaryRepositoryObj = App::make(NativeAccountTypeSummaryRepository::class);

        if ( ! $NativeAccountTypeSummaryObj = $NativeAccountTypeSummaryRepositoryObj->find($ReportTemplateAccountGroupObj->native_account_type_id))
        {
            throw new GeneralException('could not find account type summary from id given');
        }

        $NativeAccountTypeSummaryObj->report_template_account_group_id = $ReportTemplateAccountGroupObj->id;

        return $NativeAccountTypeSummaryObj;
    }

    /**
     *
     * @return \App\Waypoint\Collection
     * @throws \BadMethodCallException
     */
    public function getAccessiblePropertyObjArr()
    {
        /*
         * This is not ideal, but several places of the code uses this method
         *  @todo Refactor all the places of the code which uses: User@getAccessiblePropertyObjArr to use the repository
         */

        return
            App::make(App\Waypoint\Repositories\PropertyRepository::class)
               ->getUserAccessiblePropertyObjArr($this->id);
    }

    /** @var null|[] */
    protected $AccessiblePropertyIdArr = null;

    /**
     * @return array
     * @throws \BadMethodCallException
     */
    public function getAccessiblePropertyIdArr()
    {
        if (is_array($this->AccessiblePropertyIdArr))
        {
            return $this->AccessiblePropertyIdArr;
        }

        $this->AccessiblePropertyIdArr =
            DB::table('access_lists')
              ->join('access_list_users', 'access_lists.id', '=', 'access_list_users.access_list_id')
              ->join('access_list_properties', 'access_lists.id', '=', 'access_list_properties.access_list_id')
              ->select('access_list_properties.property_id')
              ->where('access_list_users.user_id', '=', $this->id)
              ->pluck('property_id')
              ->toArray();

        return $this->AccessiblePropertyIdArr;
    }

    /**
     * @param integer $property_id
     * @return bool
     * @throws \BadMethodCallException
     *
     * @todo make this better
     */
    public function canAccessProperty($property_id)
    {
        /** @noinspection PhpUndefinedFieldInspection */
        if ($this->active_status != User::ACTIVE_STATUS_ACTIVE)
        {
            return false;
        }

        $user_id   = $this->id;
        $minutes   = config('cache.cache_on', false)
            ? config('cache.cache_tags.User.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
            :
            0;
        $key       = 'canAccessProperty_user_id_' . $user_id . '_' . md5(__FILE__ . __LINE__);
        $return_me =
            Cache::tags([
                            'Property_' . $this->client_id,
                            'AccessList_' . $this->client_id,
                            'User_' . $this->client_id,
                            'Non-Session',
                        ])
                 ->remember(
                     $key,
                     $minutes,
                     function () use ($user_id, $property_id)
                     {
                         $UserObj = User::find($user_id);
                         return in_array($property_id, $UserObj->getAccessiblePropertyObjArr()->pluck('id')->toArray());

                     }
                 );

        return $return_me;

        // return in_array($property_id, $this->getAccessiblePropertyObjArr()->pluck('id')->toArray());
    }

    /**
     * @return boolean
     */
    public function isUserInAllAccessGroup(): bool
    {
        if (
            $this->hasRole(Role::WAYPOINT_ROOT_ROLE) ||
            $this->hasRole(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE) ||
            $this->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE) ||
            $this->hasRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE)
        )
        {
            return true;
        }
        if (in_array($this->user_id, $this->client->allAccesslist->accessListUsers->pluck('user_id')->toArray()))
        {
            return true;
        }
        return false;
    }

    /**
     * @param integer $property_id
     * @return bool
     * @throws \BadMethodCallException
     */
    public function propertyIsAccessible($property_id)
    {
        return in_array($property_id, $this->getAccessiblePropertyObjArr()->pluck('id')->toArray());
    }

    /**
     * @return string
     */
    public function getAccessListsAsString()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        return $this->accessLists->implode('name', ',');
    }

    /** @var null|[] */
    protected $AccessiblePropertyGroupObjArr = null;

    /**
     * @return \App\Waypoint\Collection|array
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function getAccessiblePropertyGroupObjArr()
    {
        $user_id = $this->id;

        $minutes                        = config('cache.cache_on', false)
            ? config('cache.cache_tags.User.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
            :
            0;
        $key                            = 'getAccessiblePropertyGroupObjArr_AccessiblePropertyGroup_user_id_' . $user_id . '_' . md5(__FILE__ . __LINE__);
        $AccessiblePropertyGroupsObjArr =
            Cache::tags([
                            'PropertyGroup_' . $this->client_id,
                            'Property_' . $this->client_id,
                            'AccessList_' . $this->client_id,
                            'Non-Session',
                        ])
                 ->remember(
                     $key,
                     $minutes,
                     function () use ($user_id)
                     {
                         $UserObj                        = User::find($user_id);
                         $AccessiblePropertyGroupsObjArr = null;
                         if (
                             $UserObj->isAdmin() ||
                             AccessListUser::where('user_id', $UserObj->id)
                                           ->where('access_list_id', $UserObj->client->allAccessList->id)
                                           ->get()
                                           ->count()
                         )
                         {
                             $AccessiblePropertyGroupsObjArr = $UserObj->client->propertyGroups;
                         }
                         else
                         {
                             $accessible_property_id_arr =
                                 DB::table('access_lists')
                                   ->select('access_list_properties.property_id')
                                   ->join('access_list_users', 'access_lists.id', '=', 'access_list_users.access_list_id')
                                   ->join('access_list_properties', 'access_lists.id', '=', 'access_list_properties.access_list_id')
                                   ->where('access_list_users.user_id', '=', $UserObj->id)
                                   ->pluck('property_id')
                                   ->toArray();

                             $AccessiblePropertyGroupsObjArr =
                                 PropertyGroup::select(['property_groups.*', DB::raw('count(property_group_properties.property_id) property_count')])
                                              ->join('property_group_properties', 'property_group_properties.property_group_id', '=', 'property_groups.id')
                                              ->where('property_groups.client_id', '=', $UserObj->client_id)
                                              ->groupBy('property_groups.id')
                                              ->get()
                                              ->filter(
                                                  function (PropertyGroup $PropertyGroupObj) use ($accessible_property_id_arr, $UserObj)
                                                  {
                                                      if ($PropertyGroupObj->user_id == $UserObj->id &&
                                                          $PropertyGroupObj->is_all_property_group
                                                      )
                                                      {
                                                          return true;
                                                      }
                                                      if ($PropertyGroupObj->propertyGroupProperties->count() == 0)
                                                      {
                                                          return false;
                                                      }
                                                      return ! array_diff(
                                                          $PropertyGroupObj->propertyGroupProperties->pluck('property_id')->toArray(),
                                                          $accessible_property_id_arr

                                                      );
                                                  });
                         }

                         $property_group_id_arr = $AccessiblePropertyGroupsObjArr->pluck('id')->toArray();
                         $PropertiesArr         =
                             DB::table('property_group_properties')
                               ->select(['property_id', 'property_group_id'])
                               ->when(! $UserObj->isAdmin(), function ($query) use ($property_group_id_arr)
                               {
                                   return $query->whereIn('property_group_id', $property_group_id_arr);
                               })
                               ->get()
                               ->groupBy('property_group_id')
                               ->map(function ($GroupedRow)
                               {
                                   $properties_arr = [];
                                   foreach ($GroupedRow as $element)
                                   {
                                       $properties_arr[] = $element->property_id;
                                   }
                                   return $properties_arr;
                               });

                         $AccessiblePropertyGroupsObjArr->each(function ($AccessiblePropertyGroupsObj) use ($PropertiesArr)
                         {
                             $AccessiblePropertyGroupsObj->{'property_group_property_ids'} = $PropertiesArr[$AccessiblePropertyGroupsObj->id] ?? null;
                         });

                         $AccessiblePropertyGroupsObjArr[] = $UserObj->allPropertyGroup;
                         $AccessiblePropertyGroupsObjArr   = $AccessiblePropertyGroupsObjArr->unique('id');

                         return $AccessiblePropertyGroupsObjArr;
                     }
                 );
        return $AccessiblePropertyGroupsObjArr;
    }

    /** @var null|[] */
    protected
        $accessible_property_group_id_arr = null;

    /**
     * @return array
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public
    function getAccessiblePropertyGroupIdArr()
    {
        if (is_array($this->accessible_property_group_id_arr))
        {
            return $this->accessible_property_group_id_arr;
        }
        $this->accessible_property_group_id_arr = $this->getAccessiblePropertyGroupObjArr()->pluck('id')->toArray();

        return $this->accessible_property_group_id_arr;
    }

    /**
     * @param null $accessible_property_group_id_arr
     */
    public
    function setAccessiblePropertyGroupIdArr(
        $accessible_property_group_id_arr
    ) {
        $this->accessible_property_group_id_arr = $accessible_property_group_id_arr;
    }

    /**
     * @param integer $property_group_id
     * @return bool
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     * @todo this could be faster if we passed in $PropertyGroupObj
     *
     */
    public
    function propertyGroupIsAccessible(
        $property_group_id,
        $PropertyGroupObj = null
    ) {
        if ($this->isUserInAllAccessGroup())
        {
            return true;
        }

        if ( ! $PropertyGroupObj)
        {
            $PropertyGroupObj = PropertyGroup::with('propertyGroupProperties')->find($property_group_id);
        }

        return ! (bool) array_diff(
            $PropertyGroupObj
                ->propertyGroupProperties
                ->pluck('property_id')
                ->toArray(),
            $this->getAccessiblePropertyIdArr()
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public
    function allPropertyGroup()
    {
        return $this->hasOne(
            PropertyGroup::class,
            'user_id',
            'id'
        )->where('is_all_property_group', true);
    }

    /**
     * @return mixed
     */
    public
    function suppress_pre_calc_events()
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
    public
    function suppress_pre_calc_usage()
    {
        if (isset($this->client->getConfigJSON()->SUPPRESS_PRE_CALC_USAGE))
        {
            return $this->client->getConfigJSON()->SUPPRESS_PRE_CALC_USAGE;
        }
        return config('waypoint.suppress_pre_calc_usage', true);
    }

    /**
     * @return array
     */
    public
    function parentToArray()
    {
        return parent::attributesToArray();
    }
}
