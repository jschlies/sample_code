<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Events\AdvancedVarianceLineItemCommentEvent;
use App\Waypoint\Model;
use App\Waypoint\Models\CommentDetail;

/**
 * Class CommentDetailRepository
 * @package App\Waypoint\Repositories
 */
class CommentDetailRepository extends CommentRepository
{
    /**
     * @param array $attributes
     * @return CommentRepository|mixed
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
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
        $CommentedUserObj = parent::create($attributes);

        /**
         * we need a fully qualified $commentable_type
         */
        if (preg_match('/App.Waypoint.Models/', $attributes['commentable_type'], $gleaned))
        {
            $fq_commentable_type = $attributes['commentable_type'];
        }
        else
        {
            $fq_commentable_type = 'App\Waypoint\Models\\' . $attributes['commentable_type'];
        }
        $CommentableObj = $fq_commentable_type::find($attributes['commentable_id']);

        /**
         * special code for various models - notifications and such
         */
        if ($attributes['commentable_type'] == Model::getShortModelNameFromModelName(App\Waypoint\Models\AdvancedVarianceLineItem::class))
        {
            $CommentedUserObj->advancedVariance->add_reviewer($this->getCurrentLoggedInUserObj()->id);
        }
        /**
         * return newest comment of $CommentableObj
         */
        return
            $CommentableObj
                ->commentsDetail
                ->sortBy(
                    function ($CommentsDetailObj)
                    {
                        return $CommentsDetailObj->updated_at;
                    }
                )
                ->last();
    }

    /**
     * @param $commentable_type
     * @param $commentable_id
     * @return mixed
     */
    public function find_with_commentable_type_commentable_id($commentable_type, $commentable_id)
    {
        $fq_commentable_type = 'App\Waypoint\Models\\' . $commentable_type;
        $CommentableObj      = $fq_commentable_type::find($commentable_id);
        return $CommentableObj->commentsDetail;
    }

    /**
     * @return string
     */
    public function model()
    {
        return CommentDetail::class;
    }
}
