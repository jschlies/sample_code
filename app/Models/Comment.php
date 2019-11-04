<?php

namespace App\Waypoint\Models;

use App\Waypoint\Collection;
use App\Waypoint\ModelDateFormatterTrait;

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
class Comment extends \Actuallymab\LaravelComment\Models\Comment
{
    use ModelDateFormatterTrait;
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [

    ];

    /**
     * @return string
     *
     * @todo deal with this - getShortModelName is here since this does not inherit App/Waypoint/Repository for Entrust reasons
     */
    public function getShortModelName()
    {
        $model_name = explode('\\', get_class($this));
        $return_me  = array_pop($model_name);
        return $return_me;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }

    public function toArray(): array
    {
        $return_me                    = parent::toArray();
        $return_me['commentMentions'] = $this->commentMentions->toArray();
        $return_me['created_at']      = $this->perhaps_format_date($this->created_at);
        $return_me['updated_at']      = $this->perhaps_format_date($this->updated_at);
        $return_me['model_name']      = self::class;

        return $return_me;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function commentMentions()
    {
        return $this->hasMany(
            CommentMention::class,
            'comment_id',
            'id'
        );
    }
}
