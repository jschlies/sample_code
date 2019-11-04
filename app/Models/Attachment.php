<?php

namespace App\Waypoint\Models;

use App;

class Attachment extends \Bnb\Laravel\Attachments\Attachment
{
    /**
     * @return array
     */
    public function toArray(): array
    {
        $attributes                    = parent::toArray();
        $attributes['file_size_units'] = 'bytes';
        $attributes['createdByUser']   = $this->createdByUser->toArray();
        $attributes['url']             = '/api/v1/ClientUser/attachments/' . $this->id . '/download';

        return $attributes;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function createdByUser()
    {
        return $this->belongsTo(
            User::class, 'created_by_user_id', 'id'
        );
    }

    /**
     * @param string $disposition
     * @return bool|void
     *
     * for some reason Bnb\Laravel\Attachments, I think to avoid collisions,
     * the 'filename' is kept in 'key' and $this->file is the name of the /tmp/ file.
     * the line "$this->file = $this->key;" will switch it back.
     *
     */
    public function output($disposition = 'inline')
    {
        $this->filename = $this->key;
        parent::output($disposition);
    }
}