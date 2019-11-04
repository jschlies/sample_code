<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class AssetType
 * @package App\Waypoint\Models
 */
class AssetType extends AssetTypeModelBase
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'client_id'              => 'required|integer|unique_with:asset_types,asset_type_name,object_id',
        'asset_type_name'        => 'required|min:2|max:255',
        'asset_type_description' => 'sometimes|nullable|min:3|max:255',
        'display_name'           => 'sometimes|nullable|min:3|max:255',
    ];

    /**
     * AssetType constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id"                     => $this->id,
            "client_id"              => $this->client_id,
            "asset_type_name"        => $this->asset_type_name,
            "asset_type_description" => $this->asset_type_description,
            "display_name"           => $this->display_name,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
