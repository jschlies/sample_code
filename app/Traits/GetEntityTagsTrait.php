<?php

namespace App\Waypoint;

use App\Waypoint\Models\FavoriteGroup;
use App\Waypoint\Models\EntityTag;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\User;

/**
 * Trait GetEntityTagsTrait
 * @package App\Waypoint\Models
 *
 * NOTE NOTE NOTE
 * This trait exists because the User, permission and role models
 * extend App\Waypoint\Models\Entrust\User which extends App\Waypoint\Models\Entrust which extends blah blah.
 *          the point is that if we want to add base functionality to all models, we need to use this trait
 *
 */
trait GetEntityTagsTrait
{
    /**
     * @return Collection
     * @todo lazy load these
     */
    public function getFavoriteGroups()
    {
        /** @var  FavoriteGroup $FavoriteGroupObj */
        $FavoriteGroupsArr = new Collection();
        foreach (FavoriteGroup::where('name', '=', EntityTag::FAVORITES)->get() as $FavoriteGroupObj)
        {
            if ($this instanceof Client)
            {
                $FavoriteGroupObj->setClientId($this->id);
            }
            elseif ($this instanceof User)
            {
                $FavoriteGroupObj->setUserId($this->id);
            }
            else
            {
                $FavoriteGroupObj->setEntityId($this->id);
            }
            $FavoriteGroupsArr[] = $FavoriteGroupObj;
        }

        return $FavoriteGroupsArr;
    }
}