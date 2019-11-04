<?php

namespace App\Waypoint\Models;

/**
 * Class EntityTag
 * @package App\Waypoint\Models
 */
class EntityTag extends EntityTagModelBase
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name'         => 'required|max:255|unique_with:entity_tags,entity_model,object_id',
        'description'  => 'sometimes',
        'entity_model' => 'required|max:255',
    ];

    const FAVORITES = Favorite::class;
    public static $entity_types = [
        self::FAVORITES,
    ];

    /**
     * this is a list of what models can be 'Favorite-ed'
     */
    const PROPERTY_FAVORITE_MODEL       = Property::class;
    const PROPERTY_GROUP_FAVORITE_MODEL = PropertyGroup::class;
    public static $favorite_values = [
        self::PROPERTY_FAVORITE_MODEL,
        self::PROPERTY_GROUP_FAVORITE_MODEL,
    ];

    /**
     * this is a list of what models can be 'Styled-ed'
     */
    const CLIENT_STYLE_MODEL = Client::class;
    public static $style_values = [
        self::CLIENT_STYLE_MODEL,
    ];

    /**
     * this is a list of what models can be 'Config-ed'
     */
    const CLIENT_CONFIG_MODEL = Client::class;
    const USER_CONFIG_MODEL   = User::class;
    public static $config_values = [
        self::CLIENT_CONFIG_MODEL,
        self::USER_CONFIG_MODEL,
    ];

    /**
     * this is a list of what models can have and image and what kind
     * Note that a particular model can have > 1 image associated
     */
    const CLIENT_IMAGE_MODEL                  = Client::class;
    const CLIENT_LOGO_IMAGE_MODEL             = ClientLogo::class;
    const CLIENT_DEFAULT_PROPERTY_IMAGE_MODEL = ClientDefaultPropertyImage::class;
    const PROPERTY_IMAGE_MODEL                = Property::class;
    const USER_IMAGE_MODEL                    = User::class;
    const USER_LOGO_IMAGE_MODEL               = UserLogo::class;
    public static $image_values = [
        self::CLIENT_IMAGE_MODEL,
        self::CLIENT_LOGO_IMAGE_MODEL,
        self::PROPERTY_IMAGE_MODEL,
        self::USER_IMAGE_MODEL,
    ];

    /**
     * @return string
     */
    public function getShortEntityModel()
    {
        $entity_model_arr = explode('\\', $this->entity_model);
        return array_pop($entity_model_arr);
    }

    public $client_id;
    public $user_id;
    public $entity_id;

    /**
     * @return null
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * @param null $client_id
     */
    public function setClientId($client_id)
    {
        $this->client_id = $client_id;
    }

    /**
     * @return null
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->entity_id;
    }

    /**
     * @param mixed $entity_id
     */
    public function setEntityId($entity_id)
    {
        $this->entity_id = $entity_id;
    }
}
