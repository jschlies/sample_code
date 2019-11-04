<?php

namespace App\Waypoint\Models;

use App\Waypoint\Collection;

/**
 * See https://github.com/actuallymab/laravel-comment
 *
 * REMEMBER
 * commentable_id = id of object in question (Opportunity_id, AdvancedVarianceLineItem_id......
 * commentable_type = type of object in question (Opportunity, AdvancedVarianceLineItem......
 * commented_id - user_id of commenter
 * commented_type - type of commenter - always User
 *
 * @method static Client find($id, $columns = ['*']) desc
 * @method integer count($columns = '*') desc
 * @method static Client[]|Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static Client|Collection findOrFail($id, $columns = ['*']) desc
 * @method static Client findOrNew($id, $columns = ['*']) desc
 * @method static Client firstOrNew(array $attributes, array $values = []) desc
 * @method static Client firstOrCreate(array $attributes, array $values = []) desc
 * @method static Client updateOrCreate(array $attributes, array $values = []) desc
 */
class CommentDetail extends Comment
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    public function toArray(): array
    {
        $return_me                    = parent::toArray();
        $return_me['commentMentions'] = $this->commentMentions->toArray();
        return $return_me;
    }

    public function getTable()
    {
        return 'comments';
    }
}
