<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class AdvancedVarianceThreshold
 * @package App\Waypoint\Models
 */
class AdvancedVarianceThreshold extends AdvancedVarianceThresholdModelBase
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
            "id"                               => $this->id,
            "client_id"                        => $this->client_id,
            "property_id"                      => $this->property_id,
            "native_account_id"                => $this->native_account_id,
            "native_account_type_id"           => $this->native_account_type_id,
            "report_template_account_group_id" => $this->report_template_account_group_id,

            "native_account_overage_threshold_amount"   => $this->native_account_overage_threshold_amount,
            "native_account_overage_threshold_amount_too_good"   => $this->native_account_overage_threshold_amount_too_good,
            "native_account_overage_threshold_percent"  => $this->native_account_overage_threshold_percent,
            "native_account_overage_threshold_percent_too_good"  => $this->native_account_overage_threshold_percent_too_good,
            "native_account_overage_threshold_operator" => $this->native_account_overage_threshold_operator,

            "report_template_account_group_overage_threshold_amount"   => $this->report_template_account_group_overage_threshold_amount,
            "report_template_account_group_overage_threshold_amount_too_good"   => $this->report_template_account_group_overage_threshold_amount_too_good,
            "report_template_account_group_overage_threshold_percent"  => $this->report_template_account_group_overage_threshold_percent,
            "report_template_account_group_overage_threshold_percent_too_good"  => $this->report_template_account_group_overage_threshold_percent_too_good,
            "report_template_account_group_overage_threshold_operator" => $this->report_template_account_group_overage_threshold_operator,

            "calculated_field_overage_threshold_amount"   => $this->calculated_field_overage_threshold_amount,
            "calculated_field_overage_threshold_amount_too_good"   => $this->calculated_field_overage_threshold_amount_too_good,
            "calculated_field_overage_threshold_percent"  => $this->calculated_field_overage_threshold_percent,
            "calculated_field_overage_threshold_percent_too_good"  => $this->calculated_field_overage_threshold_percent_too_good,
            "calculated_field_overage_threshold_operator" => $this->calculated_field_overage_threshold_operator,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            'model_name' => self::class,
        ];
    }
}
