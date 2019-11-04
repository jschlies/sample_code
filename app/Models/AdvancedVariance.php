<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\AuditableTrait;
use App\Waypoint\Collection;
use App\Waypoint\CommentableTrait;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\HasAttachment;
use App\Waypoint\RelatedUserTrait;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\RelatedUserRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class AdvancedVariance extends AdvancedVarianceModelBase implements AuditableContract
{
    use AuditableTrait;
    use RelatedUserTrait;
    use CommentableTrait;
    use HasAttachment;

    const REVIEWER = 'Advanced Variance Reviewer';
    public static $related_user_type_arr = [
        self::REVIEWER,
    ];

    const PERIOD_TYPE_MONTHLY   = 'monthly';
    const PERIOD_TYPE_QUARTERLY = 'quarterly';
    public static $period_type_arr = [
        self::PERIOD_TYPE_MONTHLY,
        self::PERIOD_TYPE_QUARTERLY,
    ];

    const OVERAGE_THRESHOLD_OPERATOR_AND = 'and';
    const OVERAGE_THRESHOLD_OPERATOR_OR  = 'or';
    public static $overage_threshold_operator_arr = [
        self::OVERAGE_THRESHOLD_OPERATOR_AND,
        self::OVERAGE_THRESHOLD_OPERATOR_OR,
    ];
    const THRESHOLD_MODE_NATIVE_ACCOUNT                = 'native_account';
    const THRESHOLD_MODE_REPORT_TEMPLATE_ACCOUNT_GROUP = 'report_template_account_group';
    const THRESHOLD_MODE_BOTH                          = 'both';
    public static $threshold_mode_arr = [
        self::THRESHOLD_MODE_NATIVE_ACCOUNT,
        self::THRESHOLD_MODE_REPORT_TEMPLATE_ACCOUNT_GROUP,
        self::THRESHOLD_MODE_BOTH,
    ];

    const TRIGGER_MODE_MONTHLY  = 'trigger_mode_monthly';
    const TRIGGER_MODE_YTD      = 'trigger_mode_ytd';
    const TRIGGER_MODE_QTD      = 'trigger_mode_qtd';
    const TRIGGER_MODE_FORECAST = 'trigger_mode_forecast';
    public static $trigger_mode_value_arr = [
        self::TRIGGER_MODE_MONTHLY,
        self::TRIGGER_MODE_YTD,
        self::TRIGGER_MODE_QTD,
        self::TRIGGER_MODE_FORECAST,
    ];

    const ADVANCED_VARIANCE_CONFIG_KEY      = 'ADVANCED_VARIANCE';
    const ADVANCED_VARIANCE_TABS_CONFIG_KEY = 'ADVANCED_VARIANCE_TABS';

    const ACTIVE_STATUS_UNLOCKED = 'unlocked';
    const ACTIVE_STATUS_LOCKED   = 'locked';
    public static $active_status_value_arr = [
        self::ACTIVE_STATUS_UNLOCKED,
        self::ACTIVE_STATUS_LOCKED,
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'locker_user_id' => 'sometimes|nullable|integer',
    ];

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'client_id',
        'advanced_variance_status',
        'advanced_variance_start_date',
        'period_type',
        'property_id',
        'report_template_id',
        'locker_user_id',
        'threshold_mode',
        'locked_date',
        'target_locked_date',
        'as_of_month',
        'as_of_year',
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
     * @param null|array $rules
     * @return null|array
     * @throws \App\Waypoint\Exceptions\GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(self::$baseRules, self::$rules);
        }
        $rules                             = parent::get_model_rules($rules, $object_id);
        $rules['advanced_variance_status'] = 'required|string|max:255|in:' . implode(',', AdvancedVariance::$active_status_value_arr);
        $rules['threshold_mode']           = 'required|string|max:255|in:' . implode(',', AdvancedVariance::$threshold_mode_arr);
        $rules['period_type']              = 'required|string|max:255|in:' . implode(',', AdvancedVariance::$period_type_arr);
        $rules['trigger_mode']             = 'required|string|max:255|in:' . implode(',', AdvancedVariance::$trigger_mode_value_arr);
        return $rules;
    }

    /**
     * @param Builder $query
     * @param $property_group_id
     */
    public function scopeByPropertyGroup(Builder $query, $property_group_id)
    {
        $query
            ->join('properties', 'advanced_variances.property_id', 'properties.id')
            ->join('property_group_properties', 'properties.id', 'property_group_properties.property_id')
            ->where('property_group_properties.property_group_id', $property_group_id)
            ->select(['advanced_variances.*']);
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
            "id"                           => $this->id,
            "client_id"                    => $this->property->client_id,
            "advanced_variance_start_date" => $this->perhaps_format_date($this->advanced_variance_start_date),
            "period_type"                  => $this->period_type,
            "trigger_mode"                 => $this->trigger_mode,
            "property_id"                  => $this->property_id,
            "report_template_id"           => $this->report_template_id,
            "threshold_mode"               => $this->threshold_mode,

            "advancedVarianceLineItems" => $this->advancedVarianceLineItemDetails->toArray(),

            'relatedUserTypes' => $this->getRelatedUserTypes(AdvancedVariance::class, $this->id)->toArray(),

            "as_of_month" => $this->as_of_month,
            "as_of_year"  => $this->as_of_year,
            "comments"    => $this->getComments()->toArray(),

            's3_dump_md5'                       => $this->s3_dump_md5,
            'last_s3_dump_name'                 => $this->last_s3_dump_name,
            'last_s3_dump_date'                 => $this->perhaps_format_date($this->last_s3_dump_date),
            'last_s3_dump_name_report_template' => $this->last_s3_dump_name_report_template,
            'last_s3_dump_date_report_template' => $this->perhaps_format_date($this->last_s3_dump_date_report_template),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function lockerUser()
    {
        return $this->belongsTo(
            User::class,
            'locker_user_id',
            'id'
        );
    }

    /**
     * @param integer $user_id
     * @return bool
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function add_reviewer($user_id)
    {
        self::getRepository()->add_reviewer($this->id, $user_id, $this->property_id);
        $this->refresh();
    }

    /**
     * @param integer $user_id
     * @throws GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function remove_reviewer($user_id)
    {
        self::getRepository()->remove_reviewer($this->id, $user_id);
        $this->refresh();
    }

    /**
     * @param integer $user_id
     */
    public function approve($user_id)
    {
        $AdvancedVarianceApprovalRepositoryObj = App::make(App\Waypoint\Repositories\AdvancedVarianceApprovalRepository::class);
        $AdvancedVarianceApprovalRepositoryObj->create(
            [
                'advanced_variance_id' => $this->id,
                'approving_user_id'    => $user_id,
                'approval_date'        => Carbon::now(),
            ]
        );
        $this->add_reviewer($user_id);
        $this->refresh();
    }

    /**
     * @return bool
     */
    public function approved()
    {
        return (boolean) $this->advancedVarianceApprovals->count();
    }

    /**
     * @return bool
     */
    public function locked()
    {
        return (boolean) $this->locker_user_id && (bool) $this->locked_date;
    }

    /**
     * @param integer $user_id
     * @throws GeneralException
     */
    public function unapprove($user_id)
    {
        if ( ! $this->user_is_reviewer($user_id))
        {
            throw new GeneralException('invalid reviwer');
        }
        $AdvancedVarianceApprovalRepositoryObj = App::make(App\Waypoint\Repositories\AdvancedVarianceApprovalRepository::class);
        $AdvancedVarianceApprovalObj           = $AdvancedVarianceApprovalRepositoryObj->findWhere(
            [
                'advanced_variance_id' => $this->id,
                'approving_user_id'    => $user_id,
            ]
        )->first();
        $AdvancedVarianceApprovalRepositoryObj->delete($AdvancedVarianceApprovalObj->id);
    }

    /**
     * @param integer $user_id
     * @throws GeneralException
     */
    public function mark_locked($user_id)
    {
        if ( ! $this->user_is_reviewer($user_id))
        {
            throw new GeneralException('invalid reviewer');
        }
        if ( ! $this->advancedVarianceApprovals->count())
        {
            throw new GeneralException('Advanced Variance is not approved');
        }
        self::getRepository()->update(
            [
                'locker_user_id'           => $user_id,
                'locked_date'              => Carbon::now(),
                'advanced_variance_status' => AdvancedVariance::ACTIVE_STATUS_LOCKED,
            ],
            $this->id
        );
    }

    /**
     * @param integer $user_id
     * @throws GeneralException
     */
    public function mark_unlocked($user_id)
    {
        if ( ! $this->user_is_reviewer($user_id))
        {
            throw new GeneralException('invalid reviewer');
        }
        self::getRepository()->update(
            [
                'locker_user_id'           => null,
                'locked_date'              => null,
                'advanced_variance_status' => AdvancedVariance::ACTIVE_STATUS_UNLOCKED,
            ],
            $this->id
        );
    }

    /**
     * @param $value_name
     * @return mixed
     * @throws GeneralException
     */
    public function get_conf_values($value_name)
    {
        return self::getRepository()->get_conf_values($value_name, $this->property_id);
    }

    /**
     * @return AdvancedVarianceRepository|App\Waypoint\Repository
     */
    public static function getRepository()
    {
        return App::make(AdvancedVarianceRepository::class);
    }

    /**
     * @param integer $user_id
     * @return bool
     */
    public function user_is_reviewer($user_id): bool
    {
        $RelatedUserRepositoryObj = App::make(RelatedUserRepository::class);
        if ($RelatedUserRepositoryObj->user_is_related($user_id, $this->id, AdvancedVariance::class, AdvancedVariance::REVIEWER))
        {
            return true;
        }
        return false;
    }

    /**
     * @return Collection
     */
    public function getReviewers(): Collection
    {
        /** @var Collection $RelatedUserObjArr */
        $RelatedUserObjArr = App::make(RelatedUserRepository::class)->getReviewers($this->id);
        /** @var Collection $return_me */
        $return_me = new Collection();
        /** @var RelatedUser $RelatedUserObj */
        foreach ($RelatedUserObjArr as $RelatedUserObj)
        {
            $return_me[] = $RelatedUserObj->user;
        }
        return $return_me;
    }

    /**
     * @param integer $user_id
     * @return bool
     */
    public function user_is_locker($user_id): bool
    {
        return $user_id == $this->locker_user_id;
    }

    /**
     * @return Collection
     */
    public function getFlagged(): Collection
    {
        return $this->advancedVarianceLineItemWorkflows->filter(
            function ($AdvancedVarianceLineItemObj)
            {
                return $AdvancedVarianceLineItemObj->flagged_manually ||
                       $AdvancedVarianceLineItemObj->flagged_via_policy;
            }
        );
    }

    /**
     * @return Collection
     */
    public function getFlaggedManually(): Collection
    {
        return $this->advancedVarianceLineItemWorkflows->filter(
            function ($AdvancedVarianceLineItemObj)
            {
                return $AdvancedVarianceLineItemObj->flagged_manually;
            }
        );
    }

    /**
     * @return Collection
     */
    public function getFlaggedByPolicy(): Collection
    {
        return $this->advancedVarianceLineItemWorkflows->filter(
            function ($AdvancedVarianceLineItemObj)
            {
                return $AdvancedVarianceLineItemObj->flagged_via_policy;
            }
        );
    }

    /**
     * @return Collection
     */
    public function getExpectedRecipiants(): Collection
    {
        /** @var Collection $return_me */
        $return_me = $this->getReviewers();

        if ($this->locker_id)
        {
            $return_me[] = $this->lockerUser;
        }

        /**
         * 1 dedup,
         * 2 make sure that user is ACTIVE_STATUS_ACTIVE and USER_INVITATION_STATUS_ACCEPTED
         * 3 return
         */
        return $return_me
            ->unique(
                function (User $UserObj)
                {
                    return $UserObj->id;
                }
            )
            ->filter(
                function (User $UserObj)
                {
                    return
                        $UserObj->active_status == User::ACTIVE_STATUS_ACTIVE ;
                }
            );
    }

    /**
     * @return App\Waypoint\Collection
     */
    public function getRelatedUsers()
    {
        $RelatedUserRepository = App::make(RelatedUserRepository::class);
        return $RelatedUserRepository->getRelatedUsersByAdvancesVariance($this->id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceLineItemDetails()
    {
        return $this->hasMany(
            AdvancedVarianceLineItemDetail::class,
            'advanced_variance_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceLineItemsReport()
    {
        return $this->hasMany(
            AdvancedVarianceLineItemReport::class,
            'advanced_variance_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceLineItemsSlim()
    {
        $relation = $this->hasMany(
            AdvancedVarianceLineItemSlim::class,
            'advanced_variance_id',
            'id'
        );
        /**
         * See https://stackoverflow.com/questions/22322222/laravel-eloquent-orm-group-where
         *
         * select *
         *   from `advanced_variance_line_items`
         *   inner join `report_template_account_groups` on `advanced_variance_line_items`.`report_template_account_group_id` = `report_template_account_groups`.`id`
         *   where
         *       (
         *           `advanced_variance_line_items`.`report_template_account_group_id` is not null and
         *           `report_template_account_groups`.`parent_report_template_account_group_id` is null
         *       )
         *      ----- this last part is tacked on by elequent
         *       and `advanced_variance_line_items`.`advanced_variance_id` in (?, ?, ?, ?, ?, ?, ?)
         */
        $relation
            ->getQuery()
            ->join('report_template_account_groups', 'advanced_variance_line_items.report_template_account_group_id', '=', 'report_template_account_groups.id')
            ->where(
                function ($query)
                {
                    $query->whereNotNull('advanced_variance_line_items.report_template_account_group_id');
                    $query->whereNull('report_template_account_groups.parent_report_template_account_group_id');
                }
            );
        return $relation;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceLineItemsSummary()
    {
        $relation = $this->hasMany(
            AdvancedVarianceLineItem::class,
            'advanced_variance_id',
            'id'
        );
        /**
         * See https://stackoverflow.com/questions/22322222/laravel-eloquent-orm-group-where
         */
        $relation
            ->getQuery()
            ->where('advanced_variance_line_items.is_summary', '>', 0);
        return $relation;
    }

    /**
     * @return Collection
     */
    public function advancedVarianceLineItemsSorted()
    {
        /** @var  AdvancedVarianceLineItem $advancedVarianceLineItemsSortedHeir */
        $AdvancedVarianceLineItemsSortedHeir = new Collection();

        /** @var ReportTemplateAccountGroup $ParentReportTemplateAccountGroupObj */
        $UltimateParentReportTemplateAccountGroupObjArr
            = $this->reportTemplate->reportTemplateAccountGroups
            ->filter(
                function (ReportTemplateAccountGroup $ReportTemplateAccountGroupObj)
                {
                    /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
                    return ! $ReportTemplateAccountGroupObj->parent_report_template_account_group_id;
                }
            );

        /** @var Collection $UltimateParentReportTemplateAccountGroupObjArrWithDefinedSortOrder */
        $UltimateParentReportTemplateAccountGroupObjArrWithDefinedSortOrder
            = $UltimateParentReportTemplateAccountGroupObjArr
            ->filter(
                function ($item)
                {
                    return ! is_null($item->sort_order);
                }
            )->sortBy(
                function ($item)
                {
                    return $item->sort_order;
                }
            );

        /** @var Collection $UltimateParentReportTemplateAccountGroupObjArrWithUndefinedSortOrder */
        $UltimateParentReportTemplateAccountGroupObjArrWithUndefinedSortOrder
            = $UltimateParentReportTemplateAccountGroupObjArr
            ->filter(
                function ($item)
                {
                    return is_null($item->sort_order);
                }
            )->sortBy(
                function ($item)
                {
                    return $item->report_template_account_group_code;
                }
            );

        $UltimateParentReportTemplateAccountGroupObjArr
            = $UltimateParentReportTemplateAccountGroupObjArrWithDefinedSortOrder
            ->concat($UltimateParentReportTemplateAccountGroupObjArrWithUndefinedSortOrder);

        foreach ($UltimateParentReportTemplateAccountGroupObjArr as $ParentReportTemplateAccountGroupObj)
        {
            $AdvancedVarianceLineItemsSortedHeir
                = $AdvancedVarianceLineItemsSortedHeir->merge(
                $this->advancedVarianceLineItemsSection($ParentReportTemplateAccountGroupObj, 0)
            );
        }

        // add calculated field flavor line item and respect sort
        $CalculatedFieldsLineItemCollection = $this->getCalculatedFieldLineItemsForThisReport();

        if ($CalculatedFieldsLineItemCollection->count() > 0)
        {
            foreach ($CalculatedFieldsLineItemCollection as $AdvancedVarianceLineItemReportObj)
            {
                $index = $AdvancedVarianceLineItemsSortedHeir->search(
                    function ($item) use ($AdvancedVarianceLineItemReportObj)
                    {
                        return $item->sort_order > $AdvancedVarianceLineItemReportObj->sort_order;
                    }
                );

                // add at index or at the end
                if (is_integer($index))
                {
                    $AdvancedVarianceLineItemsSortedHeir->splice($index, 0, [$AdvancedVarianceLineItemReportObj]);
                }
                else
                {
                    $AdvancedVarianceLineItemsSortedHeir->push($AdvancedVarianceLineItemReportObj);
                }
            }
        }

        return $AdvancedVarianceLineItemsSortedHeir;
    }

    /**
     * @return Collection|array
     */
    private function getCalculatedFieldLineItemsForThisReport()
    {
        $CalculatedFieldAdvancedVarianceLineItemReportCollection = new Collection();

        /** @var App\Waypoint\Repositories\AdvancedVarianceLineItemReportRepository $AdvancedVarianceLineItemReportRepositoryObj */
        $AdvancedVarianceLineItemReportRepositoryObj
            = App::make(App\Waypoint\Repositories\AdvancedVarianceLineItemReportRepository::class);

        /** @var Collection $CalculatedFields */
        $CalculatedFields = $this->reportTemplate->calculatedFields;

        if ($CalculatedFields->count() > 0)
        {
            /** @var CalculatedField $calculated_field */
            foreach ($CalculatedFields as $CalculatedFieldObj)
            {
                if (
                $CalculatedFieldAdvancedVarianceLineItemReport
                    = $AdvancedVarianceLineItemReportRepositoryObj
                    ->findWhere(
                        [
                            'advanced_variance_id' => $this->id,
                            'calculated_field_id'  => $CalculatedFieldObj->id,
                            ['is_summary', '!=', null],
                            ['is_summary', '!=', 0],
                        ]
                    )
                    ->first()
                )
                {
                    $CalculatedFieldAdvancedVarianceLineItemReportCollection[] = $CalculatedFieldAdvancedVarianceLineItemReport;
                }
            }

            if ($CalculatedFieldAdvancedVarianceLineItemReportCollection->count() > 0)
            {
                $CalculatedFieldAdvancedVarianceLineItemReportCollection->sortBy('sort_order');
            }
        }
        return $CalculatedFieldAdvancedVarianceLineItemReportCollection;
    }

    /**
     * @param $ParentReportTemplateAccountGroupObj
     * @param int $depth
     * @return Collection|array
     */
    private function advancedVarianceLineItemsSection($ParentReportTemplateAccountGroupObj, $depth = 0)
    {
        /** @var  AdvancedVarianceLineItem $advancedVarianceLineItemsSortedHeir */
        $AdvancedVarianceLineItemsSortedHeir = new Collection();

        $ParentAdvancedVarianceLineItemObj =
            $this->advancedVarianceLineItemsReport->where(
                'report_template_account_group_id',
                $ParentReportTemplateAccountGroupObj->id
            )->first();

        $TestAdvancedVarianceLineItemObjArr =
            $this->advancedVarianceLineItemsReport
                ->whereIn(
                    'native_account_id',
                    $ParentReportTemplateAccountGroupObj->get_native_account_id_arr()
                );

        if ( ! $TestAdvancedVarianceLineItemObjArr->count())
        {
            return $AdvancedVarianceLineItemsSortedHeir;
        }

        $ChildAdvancedVarianceLineItemObjArr =
            $this->advancedVarianceLineItemsReport
                ->whereIn(
                    'native_account_id',
                    $ParentReportTemplateAccountGroupObj->nativeAccounts->pluck('id')
                )
                ->sortBy(
                    function ($ChildAdvancedVarianceLineItemObj)
                    {
                        return $ChildAdvancedVarianceLineItemObj->nativeAccount->native_account_code;
                    }
                );

        $ParentAdvancedVarianceLineItemObj->depth = $depth;
        $AdvancedVarianceLineItemsSortedHeir[]    = $ParentAdvancedVarianceLineItemObj;

        foreach ($ChildAdvancedVarianceLineItemObjArr as $ChildAdvancedVarianceLineItemObj)
        {
            $AdvancedVarianceLineItemsSortedHeir[] = $ChildAdvancedVarianceLineItemObj;
        }

        foreach ($ParentReportTemplateAccountGroupObj
                     ->reportTemplateAccountGroupChildren
                     ->sortBy(
                         function ($ReportTemplateAccountGroupObj)
                         {
                             return $ReportTemplateAccountGroupObj->report_template_account_group_code;
                         }
                     ) as $ChildReportTemplateAccountGroupObj)
        {
            $AdvancedVarianceLineItemsSortedHeir
                = $AdvancedVarianceLineItemsSortedHeir->merge(
                $this->advancedVarianceLineItemsSection(
                    $ChildReportTemplateAccountGroupObj,
                    $depth + 1
                )
            );
        }
        return $AdvancedVarianceLineItemsSortedHeir;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceLineItemSummaryWorkflows()
    {
        $relation = $this->hasMany(
            AdvancedVarianceLineItemWorkflow::class,
            'advanced_variance_id',
            'id'
        );
        /**
         * See https://stackoverflow.com/questions/22322222/laravel-eloquent-orm-group-where
         */
        $relation
            ->getQuery()
            ->where('advanced_variance_line_items.is_summary', '>', 0);
        return $relation;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceLineItemWorkflows()
    {
        return $this->hasMany(
            AdvancedVarianceLineItemWorkflow::class,
            'advanced_variance_id',
            'id'
        );
    }
}

