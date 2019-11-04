<?php

namespace App\Waypoint\Tests\Api\Root;

use App\Waypoint\Models\User;
use App\Waypoint\Models\UserDetail;
use App;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class UserDetailApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class UserDetailApiTest extends TestCase
{
    use MakeUserTrait, ApiTestTrait;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function it_can_create_read_delete_userDetails()
    {
        /** @var  array $user_arr */
        $user_detail_arr = $this->fakeUserData();
        $this->json('POST', '/api/v1/clients/' . $user_detail_arr['client_id'] . '/users', $user_detail_arr);

        /** @var  UserDetail $UserDetailObj */
        $UserDetailObj = $this->UserDetailRepositoryObj->find($this->getFirstDataObject()['id']);
        $this->assertEquals($UserDetailObj->id, $this->getFirstDataObject()['id']);

        $this->assertTrue(! is_null($UserDetailObj->getMorphClass()));
        $this->assertTrue(is_numeric($this->getFirstDataObject()['client_id']));
        $this->assertEquals($this->getFirstDataObject()['client_id'], $UserDetailObj->client_id);

        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/userDetails/' . $UserDetailObj->id);
        $this->assertApiSuccess();

        $this->json('DELETE', '/api/v1/clients/' . $this->ClientObj->id . '/userDetails/' . $UserDetailObj->id);
        $this->assertApiSuccess();
        /**
         * remember we do not delete users, we mark them inactive
         */

        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/userDetails/' . $UserDetailObj->id);
        $this->assertApiSuccess();
        $this->assertEquals($this->getFirstDataObject()['active_status'], User::ACTIVE_STATUS_INACTIVE);
        /**
         * remember we do not delete users, we mark them inactive
         */

        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $UserDetailObj->id);
        $this->assertApiSuccess();
        $this->assertEquals($this->getFirstDataObject()['active_status'], User::ACTIVE_STATUS_INACTIVE);
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_delete_users()
    {
        /** @var  array $user_arr */
        $user_detail_arr = $this->fakeUserData();
        $this->json('POST', '/api/v1/clients/' . $user_detail_arr['client_id'] . '/users/', $user_detail_arr);

        $user_id = $this->getFirstDataObject()['id'];
        /** @var  UserDetail $UserDetailObj */
        $UserDetailObj = $this->UserDetailRepositoryObj->find($user_id);
        $this->assertApiSuccess();

        $this->assertTrue(! is_null($UserDetailObj->getMorphClass()));

        $this->json('DELETE', '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $UserDetailObj->id);
        $this->assertApiSuccess();
        /**
         * remember we do not delete users, we mark them inactive
         */

        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/userDetails/' . $UserDetailObj->id);
        $this->assertApiSuccess();
        $this->assertEquals($this->getFirstDataObject()['active_status'], User::ACTIVE_STATUS_INACTIVE);
        /**
         * remember we do not delete users, we mark them inactive
         */

        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $UserDetailObj->id);
        $this->assertApiSuccess();
        $this->assertEquals($this->getFirstDataObject()['active_status'], User::ACTIVE_STATUS_INACTIVE);
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \Exception
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_update_is_hidden_unless_root()
    {
        /** @var  UserDetail $UserDetailObj */
        $UserDetailObj = $this->FifthGenericUserObj;

        /** @var  UserDetail $UserDetailObj */
        $UserDetailObj = $this->UserDetailRepositoryObj->find($UserDetailObj->id);
        $UserDetailObj->setConfigJSON(Seeder::getFakeUserConfigJson());

        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $editedUser_arr */
        $editedUser_arr              = [];
        $editedUser_arr['is_hidden'] = true;

        $this->json('PUT', '/api/v1/clients/' . $this->ClientObj->id . '/userDetails/' . $UserDetailObj->id, $editedUser_arr);

        $this->assertApiSuccess();
        $this->assertTrue($this->getFirstDataObject()['is_hidden']);

        $this->json('GET', '/api/v1/users/' . $UserDetailObj->id);
        $this->assertApiSuccess();

        $this->assertTrue((boolean) $this->getFirstDataObject()['is_hidden']);
        /** @var  array $editedUser_arr */

        $editedUser_arr['is_hidden'] = false;

        $this->json('PUT', '/api/v1/clients/' . $this->ClientObj->id . '/userDetails/' . $UserDetailObj->id, $editedUser_arr);

        $this->assertApiSuccess();
        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $UserDetailObj->id);
        $this->assertApiSuccess();

        $this->assertFalse((boolean) $this->getFirstDataObject()['is_hidden']);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
