<?php

namespace App\Waypoint\Models;

/**
 * Class ClientFull
 * @package App\Waypoint\Models
 */
class ClientFull extends Client
{

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                                           => $this->id,
            'client_id_old'                                => $this->client_id_old,
            "name"                                         => $this->name,
            "description"                                  => $this->description,
            "active_status"                                => $this->active_status,
            "client_code"                                  => $this->client_code,
            "display_name"                                 => $this->display_name,
            "display_name_old"                             => $this->display_name_old ? $this->display_name_old : null,
            "property_group_calc_status"                   => $this->property_group_calc_status,
            "property_group_calc_last_requested"           => $this->perhaps_format_date($this->property_group_calc_last_requested),
            "active_status_date"                           => $this->perhaps_format_date($this->active_status_date),
            "property_group_force_recalc"                  => $this->property_group_force_recalc,
            "property_group_force_first_time_calc"         => $this->property_group_force_first_time_calc,
            "property_group_force_calc_property_group_ids" => $this->property_group_force_calc_property_group_ids,
            "favorite_groups"                              => $this->getFavoriteGroups()->toArray(),
            "accessListsFull"                              => $this->accessListsFull->toArray(),
            "nativeCoasFull"                               => $this->nativeCoasFull ? $this->nativeCoasFull->toArray() : [],
            "propertiesFull"                               => $this->propertiesFull->toArray(),
            "propertyGroupsFull"                           => $this->propertyGroups->toArray(),
            "userDetails"                                  => $this->userDetails->toArray(),

            "defaultAdvancedVarianceThresholds" => $this->getDefaultAdvancedVarianceThresholds()->toArray(),

            "config_json" => json_decode($this->config_json, true),
            "style_json"  => json_decode($this->style_json, true),
            "image_json"  => json_decode($this->image_json, true),

            "dormant_user_switch" => $this->dormant_user_switch,
            "dormant_user_ttl"    => $this->dormant_user_ttl,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function accessListsFull()
    {
        return $this->hasMany(
            AccessListFull::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function propertyGroupsFull()
    {
        return $this->hasMany(
            PropertyGroupFull::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function reportTemplatesFull()
    {
        return $this->hasMany(
            ReportTemplateFull::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function propertiesFull()
    {
        return $this->hasMany(
            PropertyFull::class,
            'client_id',
            'id'
        );
    }
}