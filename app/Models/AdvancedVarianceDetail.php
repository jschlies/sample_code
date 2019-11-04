<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class AdvancedVarianceDetail
 * @package App\Waypoint\Models
 */
class AdvancedVarianceDetail extends AdvancedVariance
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
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

            "as_of_month" => $this->as_of_month,
            "as_of_year"  => $this->as_of_year,
            'comments'    => $this->getComments()->toArray(),

            'relatedUserTypes' => $this->getRelatedUserTypes(AdvancedVariance::class, $this->id)->toArray(),
            "threshold_mode"   => $this->threshold_mode,

            's3_dump_md5'                       => $this->s3_dump_md5,
            'last_s3_dump_name'                 => $this->last_s3_dump_name,
            'last_s3_dump_date'                 => $this->last_s3_dump_date,
            'last_s3_dump_name_report_template' => $this->last_s3_dump_name_report_template,
            'last_s3_dump_date_report_template' => $this->perhaps_format_date($this->last_s3_dump_date_report_template),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            'model_name' => self::class,
        ];
    }
}
