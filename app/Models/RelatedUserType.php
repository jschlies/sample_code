<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class RelatedUserType
 *
 * @method static RelatedUserType find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static RelatedUserType|Collection findOrFail($id, $columns = ['*']) desc
 */
class RelatedUserType extends RelatedUserTypeModelBase
{
    /** @var null|int */
    public $related_object_id = null;

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        $return_me = [
            "id"                     => $this->id,
            "client_id"              => $this->client_id,
            "name"                   => $this->name,
            "description"            => $this->description,
            "related_object_type"    => $this->related_object_type,
            "related_object_subtype" => $this->related_object_subtype,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
        if ($this->related_object_id)
        {
            /**
             * more than likely, this is in context of AdvancedCariance or Property or .......
             */
            $ReturnMeMaybeObjArr =
                $this->getRelatedUsers()
                     ->unique('user_id')
                     ->map(
                         function (RelatedUser $RelatedUserObj)
                         {
                             return [
                                 'user_id'         => $RelatedUserObj->user_id,
                                 'related_user_id' => $RelatedUserObj->id,
                             ];
                         }
                     );
        }
        else
        {
            /**
             * more than likely, this is in context of Client
             *
             * Per HER-3529 it ok to pass the id's of hidden users
             */
            $ReturnMeMaybeObjArr =
                $this->relatedUsers
                    ->unique('user_id')
                    ->map(
                        function ($RelatedUserObj)
                        {
                            return [
                                'user_id'         => $RelatedUserObj->user_id,
                                'related_user_id' => null,
                            ];
                        }
                    );
        }

        /**
         * this (self::$requesting_user_role) is set in ApiController::CallAction()
         * Ihis is a bit of a have to filter out things. In this
         * case, filter out hidden users based on role of the loggedInUser
         *
         * self::$requesting_user_role === null covers the artisan command case
         */
        if (
            ! self::$requesting_user_role === null &&
            ! self::$requesting_user_role == App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE &&
            ! self::$requesting_user_role == App\Waypoint\Models\Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE
        )
        {
            $ReturnMeMaybeObjArr = $ReturnMeMaybeObjArr
                ->filter(
                    function($ReturnMeMaybeObj)
                    {
                        return ! User::find($ReturnMeMaybeObj['user_id'])->is_hidden;
                    }
                );
        }

        $return_me['users'] = $ReturnMeMaybeObjArr->toArray();
        return $return_me;
    }

    /**
     * @return mixed
     * @throws GeneralException
     */
    public function getRelatedUsers()
    {
        if ($this->related_object_id)
        {
            /**
             * @todo why just these two
             */
            if (
                self::$requesting_user_role == App\Waypoint\Models\Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE ||
                self::$requesting_user_role == App\Waypoint\Models\Role::WAYPOINT_ASSOCIATE_ROLE
            )
            {
                return $this->relatedUsers()
                            ->where('related_object_id', '=', $this->related_object_id)
                            ->get()
                            ->unique('user_id');
            }
            else
            {
                return $this->relatedUsers()
                            ->where('related_object_id', '=', $this->related_object_id)
                            ->with('user')
                            ->get()
                            ->filter(
                                function (RelatedUser $RelatedUserObj)
                                {
                                    return ! $RelatedUserObj->user->is_hidden;
                                }
                            )
                            ->unique('user_id');
            }
        }
        else
        {
            throw new GeneralException('please provide a related_object_id');
        }
    }

    /**
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(self::$baseRules, self::$rules);
        }
        $rules                        = parent::get_model_rules($rules, $object_id);
        $rules['related_object_type'] = 'required|string|max:255|in:' . implode(',', RelatedUser::$related_object_types);

        return $rules;
    }
}
