<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\CommentMention;
use \App\Waypoint\Repository as BaseRepository;
use App\Waypoint\Models\Comment;
use DB;

/**
 * Class PropertyRepository
 * @package App\Waypoint\Repositories
 */
class CommentRepository extends BaseRepository
{
    /**
     * @param array $attributes
     * @return App\Waypoint\Models\User|mixed
     * @throws GeneralException
     *
     * $CommentedUserObj - the user making the comment
     * commented_id - the id of $CommentedUserObj
     * commented_type - the type of the thing doing the commeting - should always be "App\\Waypoint\\Models\\User"
     * commentable_type - the kink of thing we are commenting on, ie "App\\Waypoint\\Models\\Lease" or "App\\Waypoint\\Models\\Suite"
     * commentable_id - id of the thing (sease/suite) that the commetn was made on
     * $CommentableObj - the thing (sease/suite) that the commetn was made on
     */
    public function create(array $attributes)
    {
        if ( ! isset($attributes['comment']) || empty($attributes['comment']) || ! $attributes['comment'])
        {
            throw new GeneralException('invalid comment');
        }
        if ( ! isset($attributes['commentable_type']) || empty($attributes['commentable_type']) || ! $attributes['commentable_type'])
        {
            throw new GeneralException('invalid commentable_type');
        }
        if ( ! isset($attributes['commented_id']) || empty($attributes['commented_id']) || ! $attributes['commented_id'])
        {
            throw new GeneralException('invalid commented_id');
        }
        $commentable_type = $attributes['commentable_type'];

        /**
         * we need a fully qualified $commentable_type
         */
        if (preg_match('/App.Waypoint.Models/', $commentable_type, $gleaned))
        {
            $fq_commentable_type = $commentable_type;
        }
        else
        {
            $fq_commentable_type = 'App\Waypoint\Models\\' . $commentable_type;
        }

        $CommentableObj = $fq_commentable_type::find($attributes['commentable_id']);
        return App::make(UserRepository::class)->find($attributes['commented_id'])->comment($CommentableObj, $attributes['comment']);
    }

    public function find_with_commentable_type_commentable_id($commentable_type, $commentable_id)
    {
        $fq_commentable_type = 'App\Waypoint\Models\\' . $commentable_type;
        $CommentableObj      = $fq_commentable_type::find($commentable_id);
        return $CommentableObj->commentsDetail;
    }

    /**
     * @param int $comment_id
     * @return null
     */
    public function delete($comment_id)
    {
        Comment::where('id','=',$comment_id)->forceDelete();
    }

    /**
     * @return string
     */
    public function model()
    {
        return Comment::class;
    }
}
