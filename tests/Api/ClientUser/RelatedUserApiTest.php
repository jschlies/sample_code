<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\RelatedUser;
use App\Waypoint\Models\Role;
use App\Waypoint\Repositories\RelatedUserRepository;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeRelatedUserTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class RelatedUserApiBaseTest
 *
 * @codeCoverageIgnore
 */
class RelatedUserApiTest extends TestCase
{
    use MakeRelatedUserTrait, ApiTestTrait;

    /**
     * @var RelatedUserRepository
     * this is needed in MakeRelatedUserTrait
     */
    protected $RelatedUserRepositoryObj;

    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        parent::setUp();
        $this->ClientObj->addUserToAllAccessList($this->getLoggedInUserObj()->id);
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_related_users()
    {
        /** @var  array $related_user_arr */
        $related_user_arr = $this->fakeRelatedUserData();
        $this->getLoggedInUserObj()->client->addUserToAllAccessList($related_user_arr['user_id']);

        $this->json(
            'POST', '/api/v1/clients/' . $this->ClientObj->id . '/' . substr('relatedUsers', 0, 32),
            $related_user_arr
        );
        $this->assertApiSuccess();
        $related_users_id = $this->getFirstDataObject()['id'];
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' .
            $related_user_arr['user_id'] . '/' . substr('relatedUsers', 0, 32)
        );
        $this->assertAPIListResponse(RelatedUser::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_users_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertTrue($found_it);

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/' . substr('relatedUsers', 0, 32) . '/' . $related_users_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $related_user_arr['user_id'] . '/' . substr('relatedUsers', 0, 32)
        );
        $this->assertAPIListResponse(RelatedUser::class, 0);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_users_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertFalse($found_it);

        /**
         * now re-add it
         */
        $this->json(
            'POST', '/api/v1/clients/' . $this->ClientObj->id . '/' . substr('relatedUsers', 0, 32),
            $related_user_arr
        );
        $related_users_id = $this->getFirstDataObject()['id'];
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $related_user_arr['user_id'] . '/' . substr('relatedUsers', 0, 32)
        );
        $this->assertAPIListResponse(RelatedUser::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_users_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertTrue($found_it);

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/' . substr('relatedUsers', 0, 32) . '/' . $related_users_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $related_user_arr['user_id'] . '/' . substr('relatedUsers', 0, 32));
        $this->assertApiListResponse(RelatedUser::class, 0);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_users_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertFalse($found_it);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_delete_non_existing_related_user_types()
    {
        $this->json(
            'DELETE', '/api/v1/clients/' . $this->ClientObj->id . '/' . substr('relatedUsers', 0, 32) . '/1000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_related_user_twice()
    {
        /** @var  array $related_user_arr */
        $related_user_arr = $this->fakeRelatedUserData();
        $this->getLoggedInUserObj()->client->addUserToAllAccessList($related_user_arr['user_id']);

        $this->json(
            'POST', '/api/v1/clients/' . $this->ClientObj->id . '/' . substr('relatedUsers', 0, 32),
            $related_user_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'POST', '/api/v1/clients/' . $this->ClientObj->id . '/' . substr('relatedUsers', 0, 32),
            $related_user_arr
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
