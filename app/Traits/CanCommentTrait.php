<?php

namespace App\Waypoint;

use Actuallymab\LaravelComment\CanComment;
use App;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Comment;
use App\Waypoint\Models\CommentDetail;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Repositories\CommentMentionRepository;
use function preg_match_all;

/**
 * Class CommentTrait
 * @package App\Waypoint\Models
 *
 */
trait CanCommentTrait
{
    use CanComment;

    /**
     * @param AdvancedVarianceLineItem|Opportunity|Client|Property|PropertyGroup|AccessList $commentable
     * @param string $commentText
     * @param int $rate
     * @return $this
     */
    public function comment($commentable, $commentText = '', $rate = 0)
    {
        $comment = new Comment(
            [
                'comment'        => $commentText,
                'rate'           => ($commentable->getCanBeRated()) ? $rate : null,
                'approved'       => ($commentable->mustBeApproved() && ! $this->isAdmin()) ? false : true,
                'commented_id'   => $this->id,
                'commented_type' => get_class(),
                'user_id'        => $this->id,
            ]
        );

        $CommentObj = $commentable->comments()->save($comment);

        /**
         * find referred users
         */
        $CommentMentionRepositoryObj = App::make(CommentMentionRepository::class);
        preg_match_all("/\[~(\d*)]/", $commentText, $gleaned_arr);
        foreach ($gleaned_arr[1] as $user_id)
        {
            /**
             * check just in case someone is mentioned twice
             */
            if ( ! $CommentMentionRepositoryObj->findWhere(
                [
                    'user_id'    => $user_id,
                    'comment_id' => $CommentObj->id,
                ]
            )->first())
            {
                $CommentMentionRepositoryObj->create(
                    [
                        'user_id'    => $user_id,
                        'comment_id' => $CommentObj->id,
                    ]
                );
            }
        }
        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function commentsDetail()
    {
        return $this->morphMany(CommentDetail::class, 'commented');
    }
}