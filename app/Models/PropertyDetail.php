<?php

namespace App\Waypoint\Models;

/**
 * Class PropertyDetail
 * @package App\Waypoint\Models
 */
class PropertyDetail extends Property
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        self::setSuspendValidation(true);
        $key              = 'relatedUserTypes_property_' . $this->id;
        $relatedUserTypes = $this->getPreCalcValue($key);
        if ($relatedUserTypes === null)
        {
            $relatedUserTypes = $this->getRelatedUserTypes(Property::class, $this->id)->toArray();
            $this->updatePreCalcValue(
                $key,
                $relatedUserTypes
            );
        }
        $key                       = 'advancedVarianceSummaries_property_' . $this->id;
        $advancedVarianceSummaries = $this->getPreCalcValue($key);
        if ($advancedVarianceSummaries === null)
        {
            $advancedVarianceSummaries = $this->advancedVarianceSummaries->toArray();
            $this->updatePreCalcValue(
                $key,
                $advancedVarianceSummaries
            );
        }
        self::setSuspendValidation(false);

        return [
            "id"                 => $this->id,
            "name"               => $this->name,
            "display_name"       => $this->display_name,
            'client_id'          => $this->client_id,
            "description"        => $this->description,
            "property_code"      => $this->property_code,
            "wp_property_id_old" => $this->wp_property_id_old,
            "load_factor_old"    => $this->load_factor_old,
            "property_id_old"    => $this->property_id_old,
            "active_status"      => $this->active_status,
            "active_status_date" => $this->perhaps_format_date($this->active_status_date),
            "accounting_system"  => $this->accounting_system,

            'street_address'              => $this->street_address,
            'display_address'             => $this->display_address,
            'smartystreets_metadata'      => $this->smartystreets_metadata,
            'city'                        => $this->city,
            'state'                       => $this->state,
            'state_abbr'                  => $this->state_abbr,
            'postal_code'                 => $this->postal_code,
            'country'                     => $this->country,
            'country_abbr'                => $this->country_abbr,
            'longitude'                   => (float) $this->longitude,
            'latitude'                    => (float) $this->latitude,
            'suppress_address_validation' => $this->suppress_address_validation,
            'address_validation_failed'   => $this->address_validation_failed,
            'census_tract'                => $this->census_tract,
            'time_zone'                   => $this->time_zone,

            "square_footage"            => $this->square_footage,
            "management_type"           => $this->management_type,
            "year_built"                => $this->year_built,
            "lease_type"                => $this->lease_type,
            "original_property_code"    => $this->original_property_code,
            "property_owned"            => $this->property_owned,
            'property_class'            => $this->property_class,
            'year_renovated'            => $this->year_renovated,
            'number_of_buildings'       => $this->number_of_buildings,
            'number_of_floors'          => $this->number_of_floors,
            'custom_attributes'         => json_decode($this->custom_attributes, true),
            'region'                    => $this->region,
            'sub_region'                => $this->sub_region,
            "acquisition_date"          => $this->perhaps_format_date($this->acquisition_date),
            'investment_type'           => $this->investment_type,
            'fund'                      => $this->fund,
            'property_sub_type'         => $this->property_sub_type,
            'ownership_entity'          => $this->ownership_entity,
            'advancedVarianceSummaries' => $advancedVarianceSummaries,
            'relatedUserTypes'          => $relatedUserTypes,
            'config_json'               => json_decode($this->config_json, true),
            "image_json"                => json_decode($this->image_json, true),
            "asset_type_id"             => $this->asset_type_id,
            "assetType"                 => $this->assetType->toArray(),
            "raw_upload"                => $this->raw_upload,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            'model_name' => self::class,
        ];
    }
}
