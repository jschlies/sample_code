<?php

namespace App\Waypoint\Models;

use App\Waypoint\Collection;

class FavoriteGroup extends EntityTag
{
    /** @var Collection */
    protected $favorites_for_user = null;
    /** @var Collection */
    protected $favorites_for_client = null;

    /**
     * @return string
     */
    public function getFavoriteModel()
    {
        return strtoupper($this->entity_model);
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
        /** @var Favorite|null $FavoriteObj */
        $FavoriteObj = null;

        if ($this->client_id)
        {
            $FavoriteObj = Favorite::where('entity_tag_id', '=', $this->id)
                                   ->where('client_id', '=', $this->client_id)
                                   ->get();
        }
        elseif ($this->user_id)
        {
            $FavoriteObj = Favorite::where('entity_tag_id', '=', $this->id)
                                   ->where('user_id', '=', $this->user_id)
                                   ->get();
        }
        elseif ($this->entity_id)
        {
            $FavoriteObj = Favorite::where('entity_tag_id', '=', $this->id)
                                   ->where('entity_id', '=', $this->entity_id)
                                   ->get();
        }

        return [
            "id"           => $this->id,
            "name"         => $this->name,
            "display_name" => $this->name . '-' . $this->getShortEntityModel(),
            "description"  => $this->description,
            "type"         => $this->entity_model,
            "favorites"    => $FavoriteObj ? $FavoriteObj->toArray() : [],
            "entity_model" => $this->entity_model,
            "model_name"   => self::class,
        ];
    }

    /**
     * @param integer $client_id
     * @return \App\Waypoint\Collection|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getFavoritesForClient($client_id)
    {
        if ($this->favorites_for_client)
        {
            return $this->favorites_for_client;
        }
        $this->favorites_for_client = new Collection();
        if ($client_id == null)
        {
            return $this->favorites_for_client;
        }
        $this->favorites_for_client = Favorite::where('entity_tag_id', '=', $this->id)
                                              ->where('client_id', '=', $client_id)->get();
        return $this->favorites_for_client;
    }

    /**
     * @param integer $user_id
     * @return \App\Waypoint\Collection|\Illuminate\Database\Eloquent\Collection|static[]
     *
     * @todo fix this - should be in parent
     */
    public function getFavoritesForUser($user_id)
    {
        if ($this->favorites_for_user)
        {
            return $this->favorites_for_user;
        }
        $this->favorites_for_user = new Collection();
        if ($user_id == null)
        {
            return $this->favorites_for_user;
        }

        $this->favorites_for_user = Favorite::where('entity_tag_id', '=', $this->id)
                                            ->where('user_id', '=', $user_id)->get();
        return $this->favorites_for_user;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function favorites()
    {
        return $this->entityTagEntities();
    }
}
