<?php

namespace App\Waypoint\Tests\Api;

use App\Waypoint\Models\Role;

use App\Waypoint\Models\AuthenticatingEntity;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAuthenticatingEntityTrait;
use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Tests\TestCase;

/**
 * Class AuthenticatingEntityApiBaseTest
 *
 * @codeCoverageIgnore
 */
class AuthenticatingEntityApiTest extends TestCase
{
    use MakeAuthenticatingEntityTrait, ApiTestTrait;

    public function setUp()
    {
        $this->setLoggedInUserRole(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE);
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_authenticating_entities()
    {
        /** @var  array $authenticating_entities_arr */
        $authenticating_entities_arr = $this->fakeAuthenticatingEntityData();
        $this->json(
            'POST',
            '/api/v1/admin/authenticatingEntities',
            $authenticating_entities_arr
        );
        $this->assertApiSuccess();
        $authenticatingEntities_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/admin/authenticatingEntities/' . $authenticatingEntities_id
        );
        $this->assertApiSuccess();

        /**
         * now re-add it
         */
        $this->json(
            'POST',
            '/api/v1/admin/authenticatingEntities',
            $authenticating_entities_arr
        );
        $this->assertApiSuccess();

        $authenticatingEntities_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/admin/authenticatingEntities/' . $authenticatingEntities_id
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     */
    public function it_can_read_authenticating_entities_list()
    {
        $this->json(
            'GET',
            '/api/v1/admin/authenticatingEntities?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertAPIListResponse(AuthenticatingEntity::class);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_delete_non_existing_authenticating_entities()
    {
        /** @var  AuthenticatingEntity $authenticatingEntityObj */
        $this->json(
            'DELETE',
            '/api/v1/admin/authenticatingEntities/1000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        unset($this->AuthenticatingEntityRepositoryObj);
        parent::tearDown();
    }
}
