<?php

namespace App\Waypoint\Models;

/**
 * Class ClientDetail
 * @package App\Waypoint\Models
 */
class ClientDetail extends Client
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        self::setSuspendValidation(true);
        $key              = 'relatedUserTypes_client_' . $this->id;
        $relatedUserTypes = $this->getPreCalcValue($key);
        if ($relatedUserTypes === null)
        {
            $relatedUserTypes = $this->getRelatedUserTypes()->toArray();
            $this->updatePreCalcValue(
                $key,
                $relatedUserTypes
            );
        }

        $key                              = 'standard_attribute_unique_values_client_' . $this->id;
        $standard_attribute_unique_values = $this->getPreCalcValue($key);
        if ($standard_attribute_unique_values === null)
        {
            $standard_attribute_unique_values = $this->getStandardAttributeUniqueValues();
            $this->updatePreCalcValue(
                $key,
                $standard_attribute_unique_values
            );
        }
        $key                            = 'custom_attribute_unique_values_client_' . $this->id;
        $custom_attribute_unique_values = $this->getPreCalcValue($key);
        if ($custom_attribute_unique_values === null)
        {
            $custom_attribute_unique_values = $this->getCustomAttributeUniqueValues();
            $this->updatePreCalcValue(
                $key,
                $custom_attribute_unique_values
            );
        }

        $key                               = 'defaultAdvancedVarianceThresholds_' . $this->id;
        $defaultAdvancedVarianceThresholds = $this->getPreCalcValue($key);
        if ($defaultAdvancedVarianceThresholds === null)
        {
            $defaultAdvancedVarianceThresholds = $this->getDefaultAdvancedVarianceThresholds()->toArray();
            $this->updatePreCalcValue(
                $key,
                $defaultAdvancedVarianceThresholds
            );
        }
        self::setSuspendValidation(false);

        $favorite_groups = $this->getFavoriteGroups()->toArray();
        return [
            "id"                                           => $this->id,
            'client_id_old'                                => $this->client_id_old,
            "name"                                         => $this->name,
            "description"                                  => $this->description,
            "display_name_old"                             => $this->display_name_old ? $this->display_name_old : null,
            "active_status"                                => $this->active_status,
            "client_code"                                  => $this->client_code,
            "display_name"                                 => $this->display_name,
            "property_group_calc_status"                   => $this->property_group_calc_status,
            "property_group_calc_last_requested"           => $this->perhaps_format_date($this->property_group_calc_last_requested),
            "active_status_date"                           => $this->perhaps_format_date($this->active_status_date),
            "property_group_force_recalc"                  => $this->property_group_force_recalc,
            "property_group_force_first_time_calc"         => $this->property_group_force_first_time_calc,
            "property_group_force_calc_property_group_ids" => $this->property_group_force_calc_property_group_ids,

            "defaultAdvancedVarianceThresholds" => $defaultAdvancedVarianceThresholds,

            "favorite_groups"                  => $favorite_groups,
            "standard_attribute_unique_values" => $standard_attribute_unique_values,
            "custom_attribute_unique_values"   => $custom_attribute_unique_values,

            /**
             * look in $this->getRelatedUserTypes() to see how this is different
             */
            "relatedUserTypes"                 => $relatedUserTypes,
            "native_account_types"             => $this->id !== 1 ? $this->getNativeAccountTypesHash() : [],
            "config_json"                      => json_decode($this->config_json, true),
            "style_json"                       => json_decode($this->style_json, true),
            "image_json"                       => json_decode($this->image_json, true),

            "reportTemplates" => $this->reportTemplates->toArray(),

            "dormant_user_switch" => $this->dormant_user_switch,
            "dormant_user_ttl"    => $this->dormant_user_ttl,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
