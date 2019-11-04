<?php

namespace App\Waypoint\Repositories;

use \App\Waypoint\Repository as BaseRepository;
use App\Waypoint\Models\Attachment;

/**
 * Class PropertyRepository
 * @package App\Waypoint\Repositories
 */
class AttachmentRepository extends BaseRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return Attachment::class;
    }
}
