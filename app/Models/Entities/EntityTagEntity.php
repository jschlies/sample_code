<?php

namespace App\Waypoint\Models;

use App\Waypoint\Exceptions\EntityTagException;

/**
 * Class EntityTagEntity
 * @package App\Waypoint\Models
 */
class EntityTagEntity extends EntityTagEntityModelBase
{
    /**
     * Validation rules
     *
     * @var array
     * @todo tighten up validation
     */
    public static $rules = [
        'client_id'     => 'sometimes|nullable|integer',
        'user_id'       => 'sometimes|nullable|integer',
        'data'          => 'array_or_json_string',
        'entity_tag_id' => 'required|integer',
        'entity_id'     => 'required|integer',
    ];

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        $pointed_at_thing = $this->get_thing_pointed_at();
        return [
            "id"             => $this->id,
            "entity_id"      => $this->entity_id,
            "entity_tag_id"  => $this->entity_tag_id,
            "client_id"      => $this->client_id,
            "user_id"        => $this->user_id,
            "favorite_thing" => $pointed_at_thing ? $pointed_at_thing->toArray() : [],
            "data"           => $this->data ? json_decode($this->data) : [],
            "model_name"     => self::class,
        ];
    }

    /**
     * @return ReportTemplateAccountGroup|Client|User|Property|PropertyGroup
     * @throws \Exception
     */
    public function get_thing_pointed_at()
    {
        switch ($this->entityTag->entity_model)
        {
            case EntityTag::PROPERTY_FAVORITE_MODEL:
            case EntityTag::PROPERTY_IMAGE_MODEL:
                return Property::find($this->entity_id);
            case EntityTag::PROPERTY_GROUP_FAVORITE_MODEL:
                return PropertyGroup::find($this->entity_id);
            case EntityTag::CLIENT_STYLE_MODEL:
            case EntityTag::CLIENT_CONFIG_MODEL:
            case EntityTag::CLIENT_LOGO_IMAGE_MODEL:
            case EntityTag::CLIENT_IMAGE_MODEL:
            case EntityTag::CLIENT_DEFAULT_PROPERTY_IMAGE_MODEL:
                return Client::find($this->entity_id);
            case EntityTag::USER_CONFIG_MODEL:
            case EntityTag::USER_IMAGE_MODEL:
                return User::find($this->entity_id);
            default:
                throw new EntityTagException('Add ' . $this->entityTag->entity_model . ' to get_thing_pointed_at() in EntityTagEntity!!!!!');
        }
    }

    /**
     * @return mixed
     */
    public function getEntityJSONData()
    {
        return json_decode($this->data, true);
    }
}