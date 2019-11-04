<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Models\UserDetail;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;
use function explode;
use function implode;
use function in_array;
use function reset;

/**
 * Class UserDetailApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class UserDetailApiTest extends TestCase
{
    use MakeUserTrait, ApiTestTrait;
    use MakeAccessListTrait;
    use MakeUserTrait;

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
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function it_can_create_userDetails()
    {
        /** @var  array $user_arr */
        $user_detail_arr = $this->fakeUserData();
        $this->json(
            'POST',
            '/api/v1/clients/' .
            $user_detail_arr['client_id'] . '/users',
            $user_detail_arr
        );
        $this->assertApiListResponse(UserDetail::class);
        $user_id = reset($this->getJSONContent()['data'])['id'];
        /** @var  UserDetail $UserDetailObj */
        $UserDetailObj = UserDetail::find($user_id);
        $this->assertEquals($UserDetailObj->id, $user_id);

        /*
         * can read user details
         */
        $this->json('GET', '/api/v1/clients/' . $user_detail_arr['client_id'] . '/userDetails/' . $user_id);
        $this->assertApiSuccess();

        /**
         * can delete
         */
        $this->json('DELETE', '/api/v1/clients/' . $user_detail_arr['client_id'] . '/userDetails/' . $user_id);
        $this->assertApiSuccess();

        /**
         * remember we do not delete users, we mark them inactive
         */
        $this->json('GET', '/api/v1/clients/' . $user_detail_arr['client_id'] . '/userDetails/' . $user_id);
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
    public function it_can_create_users()
    {
        /** @var  array $user_arr */
        $user_detail_arr = $this->fakeUserData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/users',
            $user_detail_arr
        );

        $user_id = reset($this->getJSONContent()['data'])['id'];

        /**
         * give user non-sense role
         */
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $user_id . '/role/hamlet/addRole'
        );
        $this->assertApiFailure();
        $UserObj = $this->UserRepositoryObj->find($user_id);
        $this->assertFalse(in_array(Role::CLIENT_ADMINISTRATIVE_USER_ROLE, $UserObj->getRoleNamesArr()));

        /**
         * give role to invalid user
         */
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . 1234 . '/role/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/addRole'
        );
        $this->assertApiFailure();
        $UserObj = $this->UserRepositoryObj->find($user_id);
        $this->assertFalse(in_array(Role::CLIENT_ADMINISTRATIVE_USER_ROLE, $UserObj->getRoleNamesArr()));

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $user_id . '/role/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/addRole'
        );
        $this->assertApiSuccess();
        $UserObj = $this->UserRepositoryObj->find($user_id);
        $this->assertTrue(in_array(Role::CLIENT_ADMINISTRATIVE_USER_ROLE, $UserObj->getRoleNamesArr()));

        /**
         * delete user non-sense role
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $user_id . '/role/kfsdfkhgsdlkfjhsdlkjhslkjhfsldkjh/deleteRole'
        );
        $this->assertApiFailure();
        $UserObj = $this->UserRepositoryObj->find($user_id);
        $this->assertTrue(in_array(Role::CLIENT_ADMINISTRATIVE_USER_ROLE, $UserObj->getRoleNamesArr()));

        /**
         * delete role to invalid user
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . 1234 . '/role/dfvdffgdfg/deleteRole'
        );
        $this->assertApiFailure();
        $UserObj = $this->UserRepositoryObj->find($user_id);
        $this->assertTrue(in_array(Role::CLIENT_ADMINISTRATIVE_USER_ROLE, $UserObj->getRoleNamesArr()));

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $user_id . '/role/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/deleteRole'
        );
        $this->assertApiSuccess();
        $UserObj = $this->UserRepositoryObj->find($user_id);
        $this->assertFalse(in_array(Role::CLIENT_ADMINISTRATIVE_USER_ROLE, $UserObj->getRoleNamesArr()));
    }

    /**
     * @test
     */
    public function it_can_delete_users()
    {
        /** @var  UserDetail $UserDetailObj */
        $UserDetailObj = $this->FirstGenericUserObj;
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $UserDetailObj->client_id . '/users/' . $UserDetailObj->id
        );
        $this->assertApiSuccess();
        /**
         * remember we do not delete users, we mark them inactive
         */

        $this->json('GET', '/api/v1/clients/' . $UserDetailObj->client_id . '/userDetails/' . $UserDetailObj->id);
        $this->assertApiSuccess();
        $this->assertEquals($this->getFirstDataObject()['active_status'], User::ACTIVE_STATUS_INACTIVE);
        /**
         * remember we do not delete users, we mark them inactive
         */
        $this->json('GET', '/api/v1/clients/' . $UserDetailObj->client_id . '/users/' . $UserDetailObj->id);
        $this->assertApiSuccess();
        $this->assertEquals($this->getFirstDataObject()['active_status'], User::ACTIVE_STATUS_INACTIVE);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_create_userDetails_with_no_firstname()
    {
        /** @var  array $user_arr */
        $user_detail_arr = $this->fakeUserData();
        unset($user_detail_arr['firstname']);
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/users',
            $user_detail_arr
        );
        $this->assertApiFailure();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_create_userDetails_with_shorto_firstname()
    {
        /** @var  array $user_arr */
        $user_detail_arr              = $this->fakeUserData();
        $user_detail_arr['firstname'] = 'a';
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/users', $user_detail_arr
        );
        $this->assertApiFailure();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function it_can_create_users_via_bouitique_routes()
    {
        /** @var  array $user_arr */
        $user_detail_arr  = $this->fakeUserData();
        $FirstAccessList  = $this->FirstAccessListObj;
        $SecondAccessList = $this->SecondAccessListObj;

        $user_detail_arr['access_list_id'] = implode(',', [$FirstAccessList->id, $SecondAccessList->id]);
        $user_detail_arr['role']           = implode(',', [Role::CLIENT_GENERIC_USER_ROLE, Role::CLIENT_ADMINISTRATIVE_USER_ROLE]);

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/users',
            $user_detail_arr
        );

        $this->assertApiSuccess();
        /** @var  UserDetail $UserDetailObj */
        $user_id       = $this->getFirstDataObject()['id'];
        $UserDetailObj = UserDetail::find($user_id);
        $this->assertEquals($UserDetailObj->id, $user_id);

        $this->assertTrue(! is_null($UserDetailObj->getMorphClass()));
        $this->assertTrue(is_numeric($this->getFirstDataObject()['client_id']));
        $this->assertEquals($this->getFirstDataObject()['client_id'], $UserDetailObj->client_id);

        foreach (explode(',', $user_detail_arr['access_list_id']) as $access_list_id)
        {
            $this->assertTrue(in_array($access_list_id, $UserDetailObj->accessLists->pluck('id')->toArray()));
        }
        foreach (explode(',', $user_detail_arr['role']) as $role)
        {
            $this->assertTrue($UserDetailObj->hasRole($role));
        }

        /*
         * can read user details
         */
        $this->json('GET', '/api/v1/clients/' . $UserDetailObj->client_id . '/userDetails/' . $user_id);
        $this->assertApiSuccess();

        /*
         * can update user details
         */
        $user_detail_arr['access_list_id'] = implode(',', [-$FirstAccessList->id, -$SecondAccessList->id]);
        $user_detail_arr['role']           = implode(',', ['-' . Role::CLIENT_GENERIC_USER_ROLE, '-' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE]);
        $this->json(
            'PUT',
            '/api/v1/clients/' . $UserDetailObj->client_id . '/userDetails/' . $UserDetailObj->id,
            $user_detail_arr
        );

        $UserDetailObj = UserDetail::find($user_id);
        /**
         * no need to test, you just made user an CLIENT_ADMINISTRATIVE_USER_ROLE
         * foreach (explode(',',$user_detail_arr['access_list_id']) as $access_list_id)
         * {
         *    $this->assertFalse(in_array(-1*$access_list_id, $UserDetailObj->getAccessListIdArr()));
         * }
         */
        $this->assertTrue($UserDetailObj->hasRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE));
        $this->assertTrue($UserDetailObj->hasRole(Role::CLIENT_GENERIC_USER_ROLE));

        /**
         * now add them back
         */
        $user_detail_arr['access_list_id'] = implode(',', [$FirstAccessList->id, $SecondAccessList->id]);
        $user_detail_arr['role']           = implode(',', [Role::CLIENT_GENERIC_USER_ROLE, Role::CLIENT_ADMINISTRATIVE_USER_ROLE]);
        $this->json(
            'PUT',
            '/api/v1/clients/' . $UserDetailObj->client_id . '/userDetails/' . $UserDetailObj->id,
            $user_detail_arr
        );
        $UserDetailObj = UserDetail::find($user_id);
        foreach (explode(',', $user_detail_arr['access_list_id']) as $access_list_id)
        {
            $this->assertTrue(in_array(abs($access_list_id), $UserDetailObj->accessLists->pluck('id')->toArray()));
        }
        $this->assertTrue($UserDetailObj->hasRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE));
        $this->assertTrue($UserDetailObj->hasRole(Role::CLIENT_GENERIC_USER_ROLE));

        $this->assertEquals($UserDetailObj->id, $user_id);
        /**
         * can delete
         */
        $this->json('DELETE', '/api/v1/clients/' . $UserDetailObj->client_id . '/userDetails/' . $user_id);
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function it_can_access_bulk_users()
    {
        $LoggedInUserObj            = $this->getLoggedInUserObj();
        $is_hidden_saved            = $LoggedInUserObj->is_hidden;
        $LoggedInUserObj->is_hidden = true;
        $LoggedInUserObj->save();
        $this->LogOutUser();
        $this->logInUser();

        $LoggedInUserObj = $this->getLoggedInUserObj();

        /*
         * can read user details
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/usersBulk');
        $this->assertApiSuccess();

        $LoggedInUserObj->is_hidden = $is_hidden_saved;
        $LoggedInUserObj->save();
        $this->LogOutUser();
        $this->logInUser();

    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function it_cannot_access_bulk_users()
    {
        /**
         * not the negative case
         */
        $LoggedInUserObj = $this->getLoggedInUserObj();
        $is_hidden_saved = $LoggedInUserObj->is_hidden;

        $LoggedInUserObj->is_hidden = false;
        $LoggedInUserObj->save();
        $this->LogOutUser();
        $this->logInUser();
        $LoggedInUserObj = $this->getLoggedInUserObj();

        /*
         * can read user details
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/usersBulk');

        $LoggedInUserObj->is_hidden = $is_hidden_saved;
        $LoggedInUserObj->save();
        $this->LogOutUser();
        $this->logInUser();
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
