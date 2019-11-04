<?php

namespace App\Waypoint\Models;

use App\Waypoint\GetEntityTagsTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 *
 * @method static User find($id, $columns = ['*']) desc
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and') desc
 *
 */
class UserDetail extends User
{
    use GetEntityTagsTrait;

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        self::setSuspendValidation(true);
        $key           = 'related_users_user_' . $this->id;
        $related_users = $this->getPreCalcValue($key);
        if ($related_users === null)
        {
            $related_users = $this->relatedUsers->toArray();
            $this->updatePreCalcValue(
                $key,
                $related_users
            );
        }

        $key                    = 'assetTypesOfProperties_user_' . $this->id;
        $asset_types_of_property_arr = $this->getPreCalcValue($key);
        if ($asset_types_of_property_arr === null)
        {
            $asset_types_of_property_arr = $this->getAssetTypesOfAccessibleProperties();
            $this->updatePreCalcValue(
                $key,
                $asset_types_of_property_arr
            );
        }

        $key                            = 'standardAttributesOfProperties_user_' . $this->id;
        $standard_attributes_of_property_arr = $this->getPreCalcValue($key);
        if ($standard_attributes_of_property_arr === null)
        {
            $standard_attributes_of_property_arr = $this->getStandardAttributesOfAccessibleProperties();
            $this->updatePreCalcValue(
                $key,
                $standard_attributes_of_property_arr
            );
        }

        $key                          = 'customAttributesOfProperties_user_' . $this->id;
        $custom_attributes_of_property_arr = $this->getPreCalcValue($key);
        if ($custom_attributes_of_property_arr === null)
        {
            $custom_attributes_of_property_arr = $this->getCustomAttributesOfAccessibleProperties();
            $this->updatePreCalcValue(
                $key,
                $custom_attributes_of_property_arr
            );
        }
        self::setSuspendValidation(false);

        /**
         * @todo See HER-1768
         */
        return [
            "id"                             => $this->id,
            "firstname"                      => $this->firstname,
            "lastname"                       => $this->lastname,
            "roles"                          => $this->getRoleNamesArr(),
            "role_highest"                   => $this->getHighestRole(),
            "client_id"                      => $this->client_id,
            "email"                          => $this->email,
            "user_name"                      => $this->user_name,
            "active_status"                  => $this->active_status,
            "related_users"                  => $related_users,
            "assetTypesOfProperties"         => $asset_types_of_property_arr,
            "standardAttributesOfProperties" => $standard_attributes_of_property_arr,
            "customAttributesOfProperties"   => $custom_attributes_of_property_arr,
            "active_status_date"             => $this->perhaps_format_date($this->active_status_date),
            "first_login_date"               => $this->perhaps_format_date($this->first_login_date),
            "last_login_date"                => $this->perhaps_format_date($this->last_login_date),
            "favorite_things"                => $this->getFavoriteThings(),
            "is_hidden"                      => $this->is_hidden ? true : false,
            'salutation'                     => $this->salutation,
            'suffix'                         => $this->suffix,
            'work_number'                    => $this->work_number,
            'mobile_number'                  => $this->mobile_number,
            'company'                        => $this->company,
            'location'                       => $this->location,
            'job_title'                      => $this->job_title,
            "user_invitation_status"         => $this->user_invitation_status,
            "user_invitation_status_date"    => $this->perhaps_format_date($this->user_invitation_status_date),
            "userInvitations"                => $this->userInvitations->toArray(),
            "creation_auth0_response"        => $this->creation_auth0_response ? json_decode($this->creation_auth0_response, true) : [],
            "config_json"                    => $this->config_json ? json_decode($this->config_json, true) : [],
            "image_json"                     => $this->image_json ? json_decode($this->image_json, true) : [],

            "authenticating_entity_id" => $this->authenticating_entity_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
