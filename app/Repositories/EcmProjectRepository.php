<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\EcmProject;
use App\Waypoint\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class EcmProjectRepository
 * @package App\Waypoint\Repositories
 */
class EcmProjectRepository extends EcmProjectRepositoryBase
{
    /**
     * @param array $attributes
     * @return EcmProject
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        $EcmProjectObj = parent::create($attributes);

        return $EcmProjectObj;
    }

    /**
     * @param array $attributes
     * @param int $id
     * @return EcmProject
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $EcmProjectObj = parent::update($attributes, $id);

        return $EcmProjectObj;
    }

    /**
     * @return null
     * @throws \Exception
     */
    public function validator()
    {
        return parent::validator();
    }

    /**
     * @param integer $client_id
     * @return \App\Waypoint\Collection
     * @throws \App\Waypoint\Exceptions\DeploymentException
     * @throws GeneralException
     */
    public function findWithClientIdUserId($client_id, $user_id)
    {
        /** @var ClientRepository $ClientRepository */
        $ClientRepository = $this->makeRepository(ClientRepository::class);
        /** @var Client $ClientObj */
        if ( ! $ClientObj = $ClientRepository->find($client_id))
        {
            throw new ModelNotFoundException('No such client');
        }
        /** @var UserRepository $UserRepository */
        $UserRepository = $this->makeRepository(UserRepository::class);
        /** @var User $UserObj */
        if ( ! $UserObj = $UserRepository->find($user_id))
        {
            throw new ModelNotFoundException('No such client');
        }
        $return_me = new Collection();
        foreach ($ClientObj->properties as $PropertyObj)
        {
            if ($UserObj->canAccessProperty($PropertyObj->id) && $PropertyObj->ecmProjects->count())
            {
                $return_me = $return_me->merge($PropertyObj->ecmProjects);
            }
        }
        return $return_me;
    }
}
