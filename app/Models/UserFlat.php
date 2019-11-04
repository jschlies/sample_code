<?php

namespace App\Waypoint\Models;

use App;

class UserFlat extends User
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "Email"             => $this->email,
            "First Name"        => $this->firstname,
            "Last Name"         => $this->lastname,
            "Access List(s)"    => implode(',', $this->accessLists->pluck('name')->toArray()),
            "Role"              => $this->getHighestRole(),
            "User Status"       => $this->active_status,
            "Invitation Status" => $this->user_invitation_status,
            "Date Created"      => $this->perhaps_format_date($this->created_at),
            "Last Login Date"   => $this->perhaps_format_date($this->last_login_date),
            "First Login Date"  => $this->perhaps_format_date($this->first_login_date),
            "Client"            => $this->client->name,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
