<?php

namespace App\Waypoint\Models;

use App\Waypoint\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @method static EntityTagEntity find($id, $columns = ['*']) desc
 * @method int count($columns = '*') desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static EntityTagEntity|Collection findOrFail($id, $columns = ['*']) desc
 * @method static Favorite findOrNew($id, $columns = ['*']) desc
 * @method static Favorite firstOrNew(array $attributes, array $values = []) desc
 * @method static Favorite firstOrCreate(array $attributes, array $values = []) desc
 * @method static Favorite updateOrCreate(array $attributes, array $values = []) desc
 */
class Favorite extends EntityTagEntity
{
    /**
     * @return string
     */
    public function getFavoriteModelName()
    {
        return 'Favorite';
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function toArray(): array
    {
        if ($this->entityTag->entity_model == EntityTag::PROPERTY_FAVORITE_MODEL)
        {
            $favorite_thing = ["property_id" => $this->entity_id];
        }
        elseif ($this->entityTag->entity_model == EntityTag::PROPERTY_GROUP_FAVORITE_MODEL)
        {
            $favorite_thing = ["property_group_id" => $this->entity_id];
        }
        else
        {
            throw new ModelNotFoundException('entityTag->entity_model ' . $this->entityTag->entity_model . ' is not configured in ' . __FILE__);
        }
        return [
            "id"             => $this->id,
            "name"           => $this->name,
            "description"    => $this->description,
            "type"           => $this->entityTag->entity_model,
            "user_id"        => $this->user_id,
            "client_id"      => $this->client_id,
            "entity_tag_id"  => $this->entity_tag_id,
            "entity_id"      => $favorite_thing[key($favorite_thing)],
            "favorite_thing" => $favorite_thing,
            "entity_model"   => $this->entity_model,
            "data"           => $this->data ? json_decode($this->data) : [],
            "model_name"     => self::class,
        ];
    }
}