<?php

namespace App\Waypoint\Models;

use App\Waypoint\GetEntityTagsTrait;

/**
 * Class PropertyFull
 * @package App\Waypoint\Models
 */
class PropertyFull extends Property
{
    use GetEntityTagsTrait;

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                 => $this->id,
            "wp_property_id_old" => $this->wp_property_id_old,
            "load_factor_old"    => $this->load_factor_old,
            'client_id'          => $this->client_id,
            "name"               => $this->name,
            "display_name"       => $this->display_name,
            'year_built'         => $this->year_built,
            "description"        => $this->description,
            "property_code"      => $this->property_code,
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

            'property_id_old'        => $this->property_id_old,
            "square_footage"         => $this->square_footage,
            'management_type'        => $this->management_type,
            "favorite_groups"        => $this->getFavoriteGroups()->toArray(),
            "nativeCoas"             => $this->nativeCoas ? $this->nativeCoas->toArray() : [],
            'lease_type'             => $this->lease_type,
            'original_property_code' => $this->original_property_code,
            'property_owned'         => $this->property_owned,
            'property_class'         => $this->property_class,
            'year_renovated'         => $this->year_renovated,
            'number_of_buildings'    => $this->number_of_buildings,
            'number_of_floors'       => $this->number_of_floors,
            'custom_attributes'      => json_decode($this->custom_attributes, true),
            'region'                 => $this->region,
            'sub_region'             => $this->sub_region,
            "acquisition_date"       => $this->perhaps_format_date($this->acquisition_date),
            'investment_type'        => $this->investment_type,
            'fund'                   => $this->fund,
            'property_sub_type'      => $this->property_sub_type,
            'ownership_entity'       => $this->ownership_entity,
            "config_json"            => json_decode($this->config_json, true),
            "image_json"             => json_decode($this->image_json, true),
            "asset_type_id"          => $this->asset_type_id,
            "assetType"              => $this->assetType ? $this->assetType->toArray() : null,
            "raw_upload"             => $this->raw_upload,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
