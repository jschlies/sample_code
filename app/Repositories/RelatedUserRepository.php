<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\User;
use Illuminate\Container\Container as Application;
use DB;

class RelatedUserRepository extends RelatedUserRepositoryBase
{
    /** @var UserRepository */
    protected $UserRepositoryObj;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->UserRepositoryObj = App::make(UserRepository::class);
    }

    /**
     * @param array $attributes
     * @return App\Waypoint\Models\RelatedUser
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException'
     */
    public function create(array $attributes)
    {
        if ($this->findWhere(
            [
                'user_id'              => $attributes['user_id'],
                'related_object_id'    => $attributes['related_object_id'],
                'related_user_type_id' => $attributes['related_user_type_id'],
            ]
        )->first())
        {
            throw new GeneralException('This user already exists in this role for this object');
        }

        /** @var User $UserObj */
        $UserObj = $this->UserRepositoryObj->find($attributes['user_id']);
        if (
            $UserObj->active_status == User::ACTIVE_STATUS_ACTIVE ||
            (
                $UserObj->active_status == User::ACTIVE_STATUS_INACTIVE ||
                $UserObj->user_invitation_status == User::USER_INVITATION_STATUS_PENDING
            )
        )
        {
            return parent::create($attributes);
        }
        throw new GeneralException('This user :<' . $attributes['user_id'] . 'is not active and does not have a pending invitation');
    }

    /**
     * @param integer $client_id
     * @return Collection
     */
    public function getRelatedUsersByClient($client_id)
    {
        $RelatedUserTypeRepositoryObj = App::make(RelatedUserTypeRepository::class);
        $return_me                    = $this->findWhereIn(
            'related_user_type_id',
            $RelatedUserTypeRepositoryObj->findWhere(
                [
                    'client_id' => $client_id,
                ]
            )->getArrayOfIDs()
        );
        return $return_me;
    }

    /**
     * @param integer $property_id
     * @return Collection
     */
    public function getRelatedUsersByProperty($property_id)
    {
        $result              = DB::select(
            DB::raw(
                "
                    SELECT related_users.id AS id FROM related_users
                    
                    JOIN related_user_types ON related_users.related_user_type_id = related_user_types.id
                      
                    WHERE 
                        related_user_types.related_object_type = :PROPERTY AND
                        related_users.related_object_id = :PROPERTY_ID 
                "
            ),
            [
                'PROPERTY'    => App\Waypoint\Models\Property::class,
                'PROPERTY_ID' => $property_id,
            ]
        );
        $related_user_id_arr = array_unique(
            array_map(
                function ($value)
                {
                    return $value->id;
                },
                $result
            )
        );

        $return_me = $this->with('relatedUserType')->findWhereIn('id', $related_user_id_arr);
        return $return_me;
    }

    /**
     * @param integer $opportunity_id
     * @return Collection
     */
    public function getRelatedUsersByOpportunity($opportunity_id)
    {
        $result              = DB::select(
            DB::raw(
                "
                    SELECT related_users.id AS id FROM related_users
                    
                    JOIN related_user_types ON related_users.related_user_type_id = related_user_types.id
                      
                    WHERE 
                        related_user_types.related_object_type = :OPPORTUNITY AND
                        related_users.related_object_id = :OPPORTUNITY_ID 
                "
            ),
            [
                'OPPORTUNITY'    => App\Waypoint\Models\Property::class,
                'OPPORTUNITY_ID' => $opportunity_id,
            ]
        );
        $related_user_id_arr = array_unique(
            array_map(
                function ($value)
                {
                    return $value->id;
                },
                $result
            )
        );

        $return_me = $this->findWhereIn('id', $related_user_id_arr);
        return $return_me;
    }

    /**
     * @param integer $advanced_variance_id
     * @return Collection
     */
    public function getRelatedUsersByAdvancesVariance($advanced_variance_id)
    {
        $result              = DB::select(
            DB::raw(
                "
                    SELECT related_users.id AS id FROM related_users
                    
                    JOIN related_user_types ON related_users.related_user_type_id = related_user_types.id
                      
                    WHERE 
                        related_user_types.related_object_type = :ADVANCEDVARIANCE AND
                        related_users.related_object_id = :ADVANCEDVARIANCE_ID 
                "
            ),
            [
                'ADVANCEDVARIANCE'    => AdvancedVariance::class,
                'ADVANCEDVARIANCE_ID' => $advanced_variance_id,
            ]
        );
        $related_user_id_arr = array_unique(
            array_map(
                function ($value)
                {
                    return $value->id;
                },
                $result
            )
        );

        $return_me = $this->findWhereIn('id', $related_user_id_arr);
        return $return_me;
    }

    /**
     * @param integer $advanced_variance_id
     * @return Collection
     */
    public function getReviewers($advanced_variance_id)
    {
        $related_user_id_arr = $this->getReviewerIdArr($advanced_variance_id);
        return $this->findWhereIn('id', $related_user_id_arr);
    }

    /**
     * @param integer $advanced_variance_id
     * @return []]
     */
    public function getReviewerIdArr($advanced_variance_id)
    {
        $result              = DB::select(
            DB::raw(
                "
                    SELECT related_users.id AS id FROM related_users
                    
                    JOIN related_user_types ON related_users.related_user_type_id = related_user_types.id
                      
                    WHERE 
                        related_user_types.related_object_type = :ADVANCED_VARIANCE AND
                        related_user_types.related_object_subtype = :SUBTYPE AND
                        related_users.related_object_id = :ADVANCED_VARIANCE_ID 
                "
            ),
            [
                'ADVANCED_VARIANCE'    => AdvancedVariance::class,
                'ADVANCED_VARIANCE_ID' => $advanced_variance_id,
                'SUBTYPE'              => AdvancedVariance::REVIEWER,
            ]
        );
        $related_user_id_arr = array_unique(
            array_map(
                function ($value)
                {
                    return $value->id;
                },
                $result
            )
        );

        return $related_user_id_arr;
    }

    /**
     * @param integer $user_id
     * @param integer $related_object_id
     * @param $related_object_type
     * @param $related_object_subtype
     * @return bool
     */
    public function user_is_related($user_id, $related_object_id, $related_object_type, $related_object_subtype)
    {
        $result = DB::select(
            DB::raw(
                "
                    SELECT related_users.id AS id FROM related_users
                    
                    JOIN related_user_types ON related_users.related_user_type_id = related_user_types.id 
                      
                    WHERE 
                        related_user_types.related_object_type = :RELATED_OBJECT_TYPE AND
                        related_user_types.related_object_subtype = :RELATED_OBJECT_SUBTYPE AND
                        related_users.related_object_id = :RELATED_OBJECT_ID AND
                        related_users.user_id = :USER_ID 
                "
            ),
            [
                'USER_ID'                => $user_id,
                'RELATED_OBJECT_ID'      => $related_object_id,
                'RELATED_OBJECT_TYPE'    => $related_object_type,
                'RELATED_OBJECT_SUBTYPE' => $related_object_subtype,
            ]
        );
        return (boolean) $result;
    }
}
