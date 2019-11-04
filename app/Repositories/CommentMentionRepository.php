<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\CommentMention;

class CommentMentionRepository extends CommentMentionRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return CommentMention::class;
    }
}
