<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\AuditableTrait;
use App\Waypoint\CommentableTrait;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\RelatedUserTrait;
use App\Waypoint\Repositories\RelatedUserRepository;
use Illuminate\Notifications\Notifiable;
use App\Waypoint\HasAttachment;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;

/**
 * Class Opportunity
 * @package App\Waypoint\Models
 */
class Opportunity extends OpportunityModelBase implements AuditableContract, UserResolver
{
    use AuditableTrait;
    use CommentableTrait;
    use Notifiable;
    use HasAttachment;
    use RelatedUserTrait;

    /**
     * Route notifications for the mail channel.
     *
     * @return string
     */
    public function routeNotificationForMail()
    {
        return $this->user->email;
    }

    /**
     * @var array
     * hold the fields to be updated straight from the request object as getDirty() seems not
     * to give the desired result for update() as opposed to save()
     */
    public $dirtyDataAlternative = null;

    /** @var array */
    public static $rules = [
        'name'                 => 'required|max:255',
        'property_id'          => 'required|integer',
        'assigned_to_user_id'  => 'required|integer',
        'created_by_user_id'   => 'required|integer',
        'description'          => 'sometimes|max:1024',
        'opportunity_status'   => 'required|max:255',
        'opportunity_priority' => 'required|max:255',
    ];

    const OPPORTUNITY_STATUS_OPEN    = 'open';
    const OPPORTUNITY_STATUS_CLOSED  = 'closed';
    const OPPORTUNITY_STATUS_DEFAULT = self::OPPORTUNITY_STATUS_OPEN;
    public static $opportunity_status_arr = [
        self::OPPORTUNITY_STATUS_OPEN,
        self::OPPORTUNITY_STATUS_CLOSED,
    ];

    const OPPORTUNITY_PRIORITY_HIGH    = 'high';
    const OPPORTUNITY_PRIORITY_MEDIUM  = 'medium';
    const OPPORTUNITY_PRIORITY_LOW     = 'low';
    const OPPORTUNITY_PRIORITY_DEFAULT = self::OPPORTUNITY_PRIORITY_MEDIUM;
    public static $opportunity_priority_arr = [
        self::OPPORTUNITY_PRIORITY_HIGH,
        self::OPPORTUNITY_PRIORITY_MEDIUM,
        self::OPPORTUNITY_PRIORITY_LOW,
    ];

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'name',
        'property_id',
        'assigned_to_user_id',
        'created_by_user_id',
        'description',
        'opportunity_status',
        'opportunity_priority',
        'estimated_incentive',
        'expense_amount',
    ];

    /**
     * Opportunity constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @return bool
     */
    public function validate()
    {
        return parent::validate();
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
        $rules                         = parent::get_model_rules($rules, $object_id);
        $rules['opportunity_status']   = 'required|string|in:' . implode(',', Opportunity::$opportunity_status_arr);
        $rules['opportunity_priority'] = 'required|string|in:' . implode(',', Opportunity::$opportunity_priority_arr);
        return $rules;
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
            "id"                   => $this->id,
            "name"                 => $this->name,
            "client_id"            => $this->property->client_id,
            "property_id"          => $this->property_id,
            "client_category_id"   => $this->client_category_id,
            "assigned_to_user_id"  => $this->assigned_to_user_id,
            "description"          => $this->description,
            "opportunity_status"   => $this->opportunity_status,
            "opportunity_priority" => $this->opportunity_priority,
            "expense_amount"       => $this->expense_amount,
            "estimated_incentive"  => $this->estimated_incentive,
            "created_by_user_id"   => $this->created_by_user_id,
            'relatedUserTypes'     => $this->getRelatedUserTypes(Opportunity::class, $this->id)->toArray(),
            "comments"             => $this->getComments()->toArray(),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function assignedToUser()
    {
        return $this->belongsTo(
            User::class, 'assigned_to_user_id', 'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function createdByUser()
    {
        return $this->belongsTo(
            User::class, 'created_by_user_id', 'id'
        );
    }

    /**
     * @return App\Waypoint\Collection
     */
    public function getRelatedUsers()
    {
        $RelatedUserRepository = App::make(RelatedUserRepository::class);
        return $RelatedUserRepository->getRelatedUsersByOpportunity($this->id);
    }

    /**
     * @return App\Waypoint\Collection|array
     */
    public function getExpectedRecipiants()
    {
        $ExpectedReciepiantsUserObjArr = new App\Waypoint\Collection();
        if ($this->createdByUser)
        {
            $ExpectedReciepiantsUserObjArr[] = $this->createdByUser;
        }
        if ($this->assignedToUser)
        {
            $ExpectedReciepiantsUserObjArr[] = $this->assignedToUser;
        }
        return $ExpectedReciepiantsUserObjArr;
    }
}
