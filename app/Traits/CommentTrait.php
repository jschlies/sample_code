<?php

namespace App\Waypoint;

use Actuallymab\LaravelComment\Commentable;
use App\Waypoint\Models\Comment;
use App\Waypoint\Models\CommentDetail;

/**
 * Class CommentableTrait
 * @package App\Waypoint\Models
 *
 */
trait CommentableTrait
{
    use Commentable;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function commentsDetail()
    {
        return $this->morphMany(CommentDetail::class, 'commentable');
    }
}