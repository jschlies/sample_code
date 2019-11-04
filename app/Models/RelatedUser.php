<?php

namespace App\Waypoint\Models;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class RelatedUser
 *
 * @method static RelatedUser find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static RelatedUser|Collection findOrFail($id, $columns = ['*']) desc
 */
class RelatedUser extends RelatedUserModelBase
{
    public function scopeRelatedToType(Builder $builder, $object_type)
    {
        $builder->join('related_user_types', 'related_user_type_id', '=', 'related_user_types.id')
                ->where('related_user_types.related_object_type', $object_type)
                ->select('related_users.*');
    }

    /**
     * @var array
     *
     * READ ME - Yea You!!! - Read This - You'll be sorry
     *
     * If you add to this list - look at AdvancedVariance::delete(). Gotta delete
     * related users first or you get a naaaaasssssty race condition
     */
    public static $related_object_types = [
        Property::class,
        Opportunity::class,
        AdvancedVariance::class,
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'related_object_id' => 'required|related_user_types_check',
    ];

    /**
     * @param null $rules
     * @param null $object_id
     * @return array|null
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(self::$baseRules, self::$rules);
        }
        $rules = parent::get_model_rules($rules, $object_id);
        return $rules;
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id"                   => $this->id,
            "user_id"              => $this->user_id,
            "related_object_id"    => $this->related_object_id,
            "related_user_type_id" => $this->related_user_type_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
