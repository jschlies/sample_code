<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessListUser;
use App\Waypoint\Models\User;
use Cache;
use Illuminate\Container\Container as Application;

/**
 * Class AccessListUserRepository
 * @package App\Waypoint\Repositories
 */
class AccessListUserRepository extends AccessListUserRepositoryBase
{
    /** @var UserRepository */
    protected $UserRepositoryObj;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->UserRepositoryObj = App::make(UserRepository::class);
    }

    /**
     * @return string
     */
    public function model()
    {
        return AccessListUser::class;
    }

    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return AccessListUser
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        $UserObj = $this->UserRepositoryObj->find($attributes['user_id']);
        if (
            $UserObj->active_status == User::ACTIVE_STATUS_ACTIVE ||
            (
                $UserObj->active_status == User::ACTIVE_STATUS_INACTIVE &&
                $UserObj->user_invitation_status == User::USER_INVITATION_STATUS_PENDING
            )
        )
        {
            $AccessListUserObj = parent::create($attributes);
            Cache::tags('AccessList_' . $AccessListUserObj->user->client_id)->flush();
            return $AccessListUserObj;
        }
        throw new GeneralException('This user :<' . $attributes['user_id'] . 'is not active and does not have a pending invitation' . __FILE__ . ':' . __LINE__);
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param int $access_list_user_id
     * @return AccessListUser
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(array $attributes, $access_list_user_id)
    {
        $AccessListUserObj = parent::update($attributes, $access_list_user_id);
        Cache::tags('AccessList_' . $AccessListUserObj->user->client_id)->flush();
        return $AccessListUserObj;
    }

    /**
     * Delete a entity in repository by id
     *
     * @param integer $access_list_user_id
     * @return bool
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function delete($access_list_user_id)
    {
        $result = parent::delete($access_list_user_id);
        return $result;
    }
}
