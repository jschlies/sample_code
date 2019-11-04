<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App\Waypoint\Models\AccessListUser;
use App;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\Generated\MakeAccessListUserTrait;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class AccessListUserPublicApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class AccessListUserPublicApiTest extends TestCase
{
    use MakeAccessListUserTrait, ApiTestTrait;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     */
    public function it_can_read_access_list_users()
    {
        /** @var  AccessListUser $AccessListUserObj */
        $AccessListUserObj = $this->makeAccessListUser(
            [
                'access_list_id'      => $this->FirstAccessListObj->id,
                'access_list_user_id' => $this->FifthGenericUserObj->id,
            ]
        );
        $this->json(
            'GET',
            '/api/v1/clients/' . $AccessListUserObj->accessList->client_id . '/accessList/' . $AccessListUserObj->access_list_id . '/accessListUser'
        );

        $this->assertApiSuccess();

        $this->assertTrue(is_array($this->getJSONContent()), 'Response element is not an array');
        $this->assertTrue(count($this->getDataObjectArr()) > 0);
        $this->assertTrue(count($this->getDataObjectArr()) <= config('waypoint.unittest_loop'), 'Response element issue');
        foreach ($this->getDataObjectArr() as $responseDataElement)
        {
            $AccessListUserObj = AccessListUser::find($responseDataElement['id']);

            $this->assertInstanceOf(App\Waypoint\Models\Client::class, $AccessListUserObj->accessList->client);

            $this->assertInstanceOf(App\Waypoint\Models\AccessListUser::class, $AccessListUserObj);
            $this->assertInstanceOf(App\Waypoint\Models\User::class, $AccessListUserObj->user);
        }
        /**
         * @todo check manyTo relationships
         */
    }

    /**
     * @test
     *
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_read_access_list_user()
    {
        /** @var  AccessListUser $AccessListUserObj */
        $AccessListUserObj = $this->makeAccessListUser();
        $this->json(
            'GET',
            '/api/v1/clients/' . $AccessListUserObj->accessList->client_id . '/accessList/' . $AccessListUserObj->accessList->id . '/accessListUser/' . $AccessListUserObj->id
        );
        $this->assertApiSuccess();
    }

    /**
     * @param array $accessListUserFields
     * @return AccessListUser
     */
    public function makeAccessListUser($accessListUserFields = [])
    {
        $theme = $this->fakeAccessListUserData($accessListUserFields);
        return $this->AccessListUserRepositoryObj->create($theme);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
