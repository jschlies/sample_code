<?php

namespace App\Waypoint\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Class AdvancedVarianceSummary
 * @package App\Waypoint\Models
 */
class AdvancedVarianceSummary extends AdvancedVariance
{
    /**
     * This is the minimal set of with's needed to serialize this object
     * with the minimal number of DB queries. Use it on anything that
     * might get toArray'd.
     *
     * @param Builder $builder
     */
    public function scopeWithChildren(Builder $builder)
    {
        $builder->with(
            [
                'advancedVarianceLineItemsSlim.reportTemplateAccountGroup.nativeAccountType',
                'advancedVarianceLineItemsSummary.reportTemplateAccountGroup.nativeAccountType',
                'advancedVarianceLineItemsSummary.comments',
                'advancedVarianceApprovals',
                'relatedUsers',
                'relatedUserTypes',
            ]
        );
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     */
    public function toArray(): array
    {
        $related_user_types = $this->relatedUserTypes->toArray();
        foreach ($related_user_types as &$related_user_type)
        {
            $related_user_type["users"] = $this
                ->relatedUsers
                ->where('related_user_type_id', $related_user_type['id'])
                ->map(
                    function ($RelatedUserObj)
                    {
                        return [
                            "related_user_id" => $RelatedUserObj->id,
                            "user_id"         => $RelatedUserObj->user_id,
                        ];
                    }
                );
        }

        return [
            "id" => $this->id,

            "advancedVarianceLineItemsSlim"    => $this->advancedVarianceLineItemsSlim->toArray(),
            "advancedVarianceLineItemsSummary" => $this->advancedVarianceLineItemsSummary->toArray(),

            "advanced_variance_start_date" => $this->perhaps_format_date($this->advanced_variance_start_date),
            "period_type"                  => $this->period_type,
            "trigger_mode"                 => $this->trigger_mode,
            "property_id"                  => $this->property_id,
            "report_template_id"           => $this->report_template_id,
            "as_of_month"                  => $this->as_of_month,
            "as_of_year"                   => $this->as_of_year,

            'relatedUserTypes' => $related_user_types,

            "report_template_account_group_id" => $this->report_template_account_group_id,
            "total_budgeted"                   => $this->total_budgeted,
            "total_actual"                     => $this->total_actual,
            "threshold_mode"                   => $this->threshold_mode,

            's3_dump_md5'                       => $this->s3_dump_md5,
            'last_s3_dump_name'                 => $this->last_s3_dump_name,
            'last_s3_dump_date'                 => $this->perhaps_format_date($this->last_s3_dump_date),
            'last_s3_dump_name_report_template' => $this->last_s3_dump_name_report_template,
            'last_s3_dump_date_report_template' => $this->perhaps_format_date($this->last_s3_dump_date_report_template),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            'model_name' => self::class,
        ];
    }
}
