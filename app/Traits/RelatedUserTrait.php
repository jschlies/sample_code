<?php

namespace App\Waypoint;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\RelatedUser;
use App\Waypoint\Models\RelatedUserTypeSlim;
use App\Waypoint\Repositories\RelatedUserRepository;
use App\Waypoint\Repositories\RelatedUserTypeRepository;
use App\Waypoint\Repositories\UserRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class RelatedUserTrait
 */
trait RelatedUserTrait
{
    /**
     * @return mixed
     */
    public function relatedUsers()
    {
        return $this->hasMany(RelatedUser::class, 'related_object_id', 'id')->relatedToType($this->getMorphClass());
    }

    /**
     * @return mixed
     */
    public function relatedUserTypes()
    {
        return $this->belongsToMany(RelatedUserTypeSlim::class, 'related_users', 'related_object_id', 'related_user_type_id')
                    ->where('related_user_types.related_object_type', $this->getMorphClass())
                    ->select('related_user_types.*')
                    ->groupBy('related_user_types.id', 'related_users.related_object_id');
    }

    /**
     * @param null $related_object_type
     * @return Collection|mixed
     * @throws GeneralException
     *
     * NOTE NOTE NOTE
     * This, when called in ClientObj, will not return relatedUsers
     */
    public function getRelatedUserTypes($related_object_type = null, $object_id = null, $related_object_subtype = null)
    {
        $RelatedUserTypeRepositoryObj = App::make(RelatedUserTypeRepository::class);
        if ($this->getMorphClass() == Client::class)
        {
            if ($related_object_type)
            {
                $criteria = [
                    'related_object_type' => $related_object_type,
                    'client_id'           => $this->id,
                ];
                if ($related_object_subtype)
                {
                    $criteria['related_object_subtype'] = $related_object_subtype;
                }
                $RelatedUserTypeObjArr =
                    $RelatedUserTypeRepositoryObj
                        ->with('relatedUsers')
                        ->findWhere($criteria);

                if ($object_id)
                {
                    /** @var App\Waypoint\Models\RelatedUserType $RelatedUserTypeObj */
                    foreach ($RelatedUserTypeObjArr as $RelatedUserTypeObj)
                    {
                        $RelatedUserTypeObj->related_object_id = $object_id;
                    }
                }
                return $RelatedUserTypeObjArr;
            }
            else
            {
                return $this->relatedUserTypes;
            }
        }
        elseif ($this->getMorphClass() == Property::class)
        {
            $client_id = $this->client_id;
        }
        elseif (
            $this->getMorphClass() == Opportunity::class ||
            $this->getMorphClass() == AdvancedVariance::class
        )
        {
            $client_id = $this->property->client_id;
        }
        else
        {
            throw new GeneralException('unknown model type');
        }

        $criteria = [
            'related_object_type' => $this->getMorphClass(),
            'client_id'           => $client_id,
        ];
        if ($related_object_subtype)
        {
            $criteria['related_object_subtype'] = $related_object_subtype;
        }

        $RelatedUserTypeObjArr = $RelatedUserTypeRepositoryObj->findWhere($criteria);

        /**
         * this causes toArray() to include relatedUsers
         */
        /** @var App\Waypoint\Models\RelatedUserType $RelatedUserTypeObj */
        foreach ($RelatedUserTypeObjArr as $RelatedUserTypeObj)
        {
            $RelatedUserTypeObj->related_object_id = $this->id;
        }

        return $RelatedUserTypeObjArr;
    }

    /**
     * @param integer $user_id
     * @param integer $related_object_id
     * @param $related_object_type
     * @param null $related_object_subtype
     * @return bool
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws ValidatorException
     */
    public function add_user($user_id, $related_object_id, $related_object_type, $related_object_subtype = null)
    {
        $UserRepositoryObj = App::make(UserRepository::class);
        if ( ! $CandidateUserObj = $UserRepositoryObj->find($user_id))
        {
            return false;
        }
        if ($this->getMorphClass() == App\Waypoint\Models\Property::class)
        {
            if ( ! $CandidateUserObj->canAccessProperty($related_object_id))
            {
                return false;
            }
        }

        /** @var RelatedUserTypeRepository $RelatedUserTypeRepositoryObj */
        $RelatedUserTypeRepositoryObj = App::make(RelatedUserTypeRepository::class);
        if ( ! $RelatedUserTypeObj = $RelatedUserTypeRepositoryObj->findWhere(
            [
                'client_id'              => $CandidateUserObj->client_id,
                'related_object_type'    => $related_object_type,
                'related_object_subtype' => $related_object_subtype,
            ]
        )->first())
        {
            throw new App\Waypoint\Exceptions\GeneralException('No RelatedUserTypeObj');
        }

        /** @var RelatedUserRepository $RelatedUserRepositoryObj */
        $RelatedUserRepositoryObj = App::make(RelatedUserRepository::class);
        $RelatedUserRepositoryObj->create(
            [
                'user_id'              => $CandidateUserObj->id,
                'related_object_id'    => $related_object_id,
                'related_user_type_id' => $RelatedUserTypeObj->id,
            ]
        );
        return true;
    }
}
