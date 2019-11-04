<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\Collection;

use App\Waypoint\Repositories\ReportTemplateAccountGroupRepository;

/**
 * Class ReportTemplateAccountGroup
 * @package App\Waypoint\Models
 */
class ReportTemplateAccountGroup extends ReportTemplateAccountGroupModelBase
{

    /** @var null|int */
    protected $generations = null;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'report_template_id'                      => 'required|integer',
        'parent_report_template_account_group_id' => 'sometimes|nullable|integer',
        'native_account_type_id'                  => 'sometimes|nullable|integer',
        'is_category'                             => 'required',
        'is_major_category'                       => 'sometimes',
        'is_waypoint_specific'                    => 'sometimes',
        'report_template_account_group_name'      => 'required|min:2|string|max:255|unique_with:report_template_account_groups,report_template_id,object_id',
        'report_template_account_group_code'      => 'required|min:2|string|max:255|unique_with:report_template_account_groups,report_template_id,object_id',
        'display_name'                            => 'sometimes|max:255',
        'usage_type'                              => 'sometimes|max:255',
        'sorting'                                 => 'sometimes|max:255',
        'version_num'                             => 'sometimes|max:255',
        'deprecated_waypoint_code'                => 'sometimes|max:255',
        'boma_account_header_1_code_old'          => 'sometimes|max:255',
        'boma_account_header_1_name_old'          => 'sometimes|max:255',
        'boma_account_header_2_code_old'          => 'sometimes|max:255',
        'boma_account_header_2_name_old'          => 'sometimes|max:255',
        'boma_account_header_3_code_old'          => 'sometimes|max:255',
        'boma_account_header_3_name_old'          => 'sometimes|max:255',
        'boma_account_header_4_code_old'          => 'sometimes|max:255',
        'boma_account_header_4_name_old'          => 'sometimes|max:255',
        'boma_account_header_5_code_old'          => 'sometimes|max:255',
        'boma_account_header_5_name_old'          => 'sometimes|max:255',
        'boma_account_header_6_code_old'          => 'sometimes|max:255',
        'boma_account_header_6_name_old'          => 'sometimes|max:255',
    ];

    /** @var array|null */
    public $native_account_id_arr = null;

    /** @var array|null */
    public $report_template_account_group_arr = null;

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                                      => $this->id,
            "report_template_account_group_name"      => $this->report_template_account_group_name,
            "report_template_account_group_code"      => $this->report_template_account_group_code,
            'native_account_type_id'                  => $this->native_account_type_id,
            'nativeAccountType'                       => $this->nativeAccountType->toArray(),
            "display_name"                            => $this->display_name,
            "report_template_id"                      => $this->report_template_id,
            "is_category"                             => $this->is_category,
            "is_major_category"                       => $this->is_major_category,
            "is_waypoint_specific"                    => $this->is_waypoint_specific,
            'parent_report_template_account_group_id' => $this->parent_report_template_account_group_id,
            'usage_type'                              => $this->usage_type,
            'sorting'                                 => $this->sorting,
            'version_num'                             => $this->version_num,
            'deprecated_waypoint_code'                => $this->deprecated_waypoint_code,
            'boma_account_header_1_code_old'          => $this->boma_account_header_1_code_old,
            'boma_account_header_1_name_old'          => $this->boma_account_header_1_name_old,
            'boma_account_header_2_code_old'          => $this->boma_account_header_2_code_old,
            'boma_account_header_2_name_old'          => $this->boma_account_header_2_name_old,
            'boma_account_header_3_code_old'          => $this->boma_account_header_3_code_old,
            'boma_account_header_3_name_old'          => $this->boma_account_header_3_name_old,
            'boma_account_header_4_code_old'          => $this->boma_account_header_4_code_old,
            'boma_account_header_4_name_old'          => $this->boma_account_header_4_name_old,
            'boma_account_header_5_code_old'          => $this->boma_account_header_5_code_old,
            'boma_account_header_5_name_old'          => $this->boma_account_header_5_name_old,
            'boma_account_header_6_code_old'          => $this->boma_account_header_6_code_old,
            'boma_account_header_6_name_old'          => $this->boma_account_header_6_name_old,

            "sort_order"                       => $this->sort_order,
            "is_summary"                       => $this->is_summary,
            "is_summary_tab_default_line_item" => $this->is_summary_tab_default_line_item,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
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
        $rules = parent::get_model_rules($rules, $object_id);
        return $rules;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reportTemplateAccountGroupParent()
    {
        return $this->belongsTo(ReportTemplateAccountGroupSummary::class, 'parent_report_template_account_group_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function reportTemplateAccountGroupChildren()
    {
        return $this->hasMany(ReportTemplateAccountGroupSummary::class, 'parent_report_template_account_group_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function reportTemplateAccountGroupChildrenFull()
    {
        return $this->hasMany(ReportTemplateAccountGroupFull::class, 'parent_report_template_account_group_id', 'id');
    }

    /**
     * @param bool $comparison
     * @return array
     */
    public function getGrandChildrenDeprecatedCoaCodes($comparison = false)
    {
        $ReportTemplateAccountGroupRepositoryObj = App::make(ReportTemplateAccountGroupRepository::class);
        $grandChildrenCoaCodesList               = [];
        foreach ($this->getChildren() as $child)
        {
            $grandChildren = $ReportTemplateAccountGroupRepositoryObj->findWhere(
                [
                    ['parent_report_template_account_group_id', '=', $child->id],
                ],
                [
                    'deprecated_waypoint_code',
                ]
            );
            if ($comparison)
            {
                $grandChildrenCoaCodesList[$child->deprecated_waypoint_code] = collect($grandChildren)->pluck('deprecated_waypoint_code')->all();
            }
            else
            {
                $grandChildrenCoaCodesList = array_merge($grandChildrenCoaCodesList, collect($grandChildren)->pluck('deprecated_waypoint_code')->all());
            }
        }
        return $grandChildrenCoaCodesList;
    }

    /**
     * @return int|null
     *
     * number of generations to ultimate parent
     */
    public function get_generations()
    {
        if ($this->generations === null)
        {
            $this->generations = 0;
            $NextGenerationObj = $this;
            while ($NextGenerationObj = $NextGenerationObj->reportTemplateAccountGroupParent)
            {
                $this->generations++;
            }
        }
        return $this->generations;
    }

    /**
     * @return Collection
     */
    public function getLineage()
    {
        // get family line of boma codes for the target code
        $lineage = new Collection();
        /** @var ReportTemplateAccountGroup $CurrentReportTemplateAccountGroupObj */
        $CurrentReportTemplateAccountGroupObj = $this;

        while ($CurrentReportTemplateAccountGroupObj->parent_report_template_account_group_id)
        {
            $lineage[]                            = $CurrentReportTemplateAccountGroupObj->reportTemplateAccountGroupParent;
            $CurrentReportTemplateAccountGroupObj = $CurrentReportTemplateAccountGroupObj->reportTemplateAccountGroupParent;
        }
        return $lineage;
    }

    /**aux_coa_line_item
     * @return Collection
     */
    public function getChildren()
    {
        $ReportTemplateAccountGroupRepositoryObj = App::make(ReportTemplateAccountGroupRepository::class);
        return $ReportTemplateAccountGroupRepositoryObj->findWhere(
            [
                ['parent_report_template_account_group_id', '=', $this->id],
            ]
        );
    }

    /**
     * @return array
     */
    public function getChildrenDeprecatedReportTemplateAccountGroupsCodes()
    {
        $ReportTemplateAccountGroupRepositoryObj  = App::make(ReportTemplateAccountGroupRepository::class);
        $ReportTemplateAccountGroupChildrenObjArr = $ReportTemplateAccountGroupRepositoryObj->findWhere(
            [
                ['parent_report_template_account_group_id', '=', $this->id],
            ],
            [
                'deprecated_waypoint_code',
            ]
        );
        $childrenDeprecatedCoaCodes               = [];
        foreach ($ReportTemplateAccountGroupChildrenObjArr as $item)
        {
            $childrenDeprecatedCoaCodes[] = $item->deprecated_waypoint_code;
        }
        return $childrenDeprecatedCoaCodes;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reportTemplateAccountGroupSummaryParent()
    {
        return $this->belongsTo(ReportTemplateAccountGroupSummary::class, 'parent_report_template_account_group_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function reportTemplateAccountGroupSummaryChildren()
    {
        return $this->hasMany(ReportTemplateAccountGroupSummary::class, 'parent_report_template_account_group_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function reportTemplateAccountGroupDetailChildren()
    {
        return $this->hasMany(ReportTemplateAccountGroupDetail::class, 'parent_report_template_account_group_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function reportTemplateMappingsFull()
    {
        return $this->hasMany(
            ReportTemplateMappingFull::class,
            'report_template_account_group_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function nativeAccountTypeDetail()
    {
        return $this->belongsTo(
            NativeAccountTypeDetail::class,
            'native_account_type_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function nativeAccountTypeSummary()
    {
        return $this->belongsTo(
            NativeAccountTypeSummary::class,
            'native_account_type_id',
            'id'
        );
    }

    /**
     * @return array|null
     */
    public function get_native_account_id_arr()
    {
        if ($this->native_account_id_arr === null)
        {
            $this->native_account_id_arr = $this->nativeAccounts->pluck('id')->toArray();
            foreach ($this->reportTemplateAccountGroupChildren as $ChildReportTemplateAccountGroupObj)
            {
                $this->native_account_id_arr = array_merge(
                    $this->native_account_id_arr,
                    $ChildReportTemplateAccountGroupObj->get_native_account_id_arr()
                );
            }
        }
        return $this->native_account_id_arr;
    }

    /**
     * @return array|null
     */
    public function get_report_template_account_group_id_arr()
    {
        if ($this->report_template_account_group_arr === null)
        {
            $this->report_template_account_group_arr[] = $this->id;
            foreach ($this->reportTemplateAccountGroupChildren as $ChildReportTemplateAccountGroupObj)
            {
                $this->report_template_account_group_arr = array_merge(
                    $this->report_template_account_group_arr,
                    $ChildReportTemplateAccountGroupObj->get_report_template_account_group_id_arr()
                );
            }
        }
        return $this->report_template_account_group_arr;
    }
}
