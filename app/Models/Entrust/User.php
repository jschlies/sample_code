<?php

namespace App\Waypoint\Models\Entrust;

use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AccessListDetail;
use App\Waypoint\Models\AccessListSummary;
use App\Waypoint\Models\AccessListSlim;
use App\Waypoint\Models\AccessListTrimmedSummary;
use App\Waypoint\Models\AccessListUser;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceApproval;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Models\Attachment;
use App\Waypoint\Models\CommentMention;
use App\Waypoint\Models\DownloadHistory;
use App\Waypoint\Models\NotificationLog;
use App\Waypoint\Models\EntityTagEntity;
use App\Waypoint\Models\Favorite;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\RoleUser;
use App\Waypoint\EntrustUserTrait;
use App\Waypoint\Models\UserInvitation;
use App\Waypoint\Models\Role;
use Cache;
use Config;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 * @package App\Waypoint\Models\Entrust
 */
class User extends Authenticatable
{
    /**
     * NOTE that this overrides \Zizaco\Entrust\Traits\EntrustUserTrait since
     * \Zizaco\Entrust\Traits\EntrustUserTrait:cachedRoles() does not seem to
     * 'put'to cache correctly - version issue??? Don't know.
     *
     * @return mixed
     */
    public function cachedRoles()
    {
        $user_id        = $this->id;
        $cachedRolesArr =
            Cache::tags([Config::get('entrust.role_user_table')])
                 ->remember(
                     'entrust_roles_for_user_' . $user_id,
                     3600,
                     function () use ($user_id)
                     {
                         $UserObj = User::find($user_id);
                         return $UserObj->roles()->get();
                     }
                 );
        return $cachedRolesArr;
    }

    use EntrustUserTrait;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token', 'creation_auth0_response'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function accessLists()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            AccessList::class,
            'access_list_users',
            'user_id',
            'access_list_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function accessListSummaries()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            AccessListSummary::class,
            'access_list_users',
            'user_id',
            'access_list_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function accessListTrimmedSummaries()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            AccessListTrimmedSummary::class,
            'access_list_users',
            'user_id',
            'access_list_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function accessListDetails()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            AccessListDetail::class,
            'access_list_users',
            'user_id',
            'access_list_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function accessListSlims()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            AccessListSlim::class,
            'access_list_users',
            'user_id',
            'access_list_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function accessListUsers()
    {
        return $this->hasMany(
            AccessListUser::class,
            'user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     * see https://github.com/chrisbjr/api-guard
     */
    public function apiKey()
    {
        return $this->hasOne(
            ApiKey::class,
            'user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceLineItemsAsExplanationTypeUser()
    {
        return $this->hasMany(
            AdvancedVarianceLineItem::class,
            'explanation_type_user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceApprovals()
    {
        return $this->hasMany(
            AdvancedVarianceApproval::class,
            'approving_user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function apiKeys()
    {
        return $this->hasMany(
            ApiKey::class,
            'user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceLineItemsAsExplainer()
    {
        return $this->hasMany(
            AdvancedVarianceLineItem::class,
            'explainer_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceLineItemsAsFlagger()
    {
        return $this->hasMany(
            AdvancedVarianceLineItem::class,
            'flagger_user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceLineItemsAsResolver()
    {
        return $this->hasMany(
            AdvancedVarianceLineItem::class,
            'resolver_user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVariancesAsLocker()
    {
        return $this->hasMany(
            AdvancedVariance::class,
            'locker_user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function attachmentsAsCreatedBy()
    {
        return $this->hasMany(
            Attachment::class,
            'created_by_user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function commentMentions()
    {
        return $this->hasMany(
            CommentMention::class,
            'user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function downloadHistories()
    {
        return $this->hasMany(
            DownloadHistory::class, 'user_id', 'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function emailHistories()
    {
        return $this->hasMany(
            NotificationLog::class, 'user_id', 'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function entityTagEntities()
    {
        return $this->hasMany(
            EntityTagEntity::class,
            'user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function favorites()
    {
        return $this->hasMany(
            Favorite::class,
            'user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function UserInvitations()
    {
        return $this->hasMany(
            UserInvitation::class,
            'user_id',
            'id'
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
    public function opportunitiesAsAssignedTo()
    {
        return $this->hasMany(
            Opportunity::class,
            'assigned_to_user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function opportunitiesAsCreatedBy()
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
    public function propertyGroups()
    {
        return $this->hasMany(
            PropertyGroup::class,
            'user_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     **/
    public function roles()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->belongsToMany(
            Role::class, 'role_users', 'user_id', 'role_id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function roleUsers()
    {
        return $this->hasMany(
            RoleUser::class, 'user_id', 'id'
        );
    }
}
