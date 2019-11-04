<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\RoleDetail;
use App\Waypoint\Models\User;
use App\Waypoint\Models\UserDetail;
use App\Waypoint\Repositories\UserDetailRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;

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
     * @var UserDetailRepository
     * this is needed in MakeUserTrait
     */
    protected $UserDetailRepositoryObj;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_userDetails()
    {
        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/userDetails/' . $this->getLoggedInUserObj()->id);
        $this->assertApiSuccess();

        /** @var User $User1Obj */
        $User1Obj = $this->FirstGenericUserObj;
        /** @var User $User2Obj */
        $User2Obj = $this->SecondGenericUserObj;

        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/usersDetail');
        $this->assertApiSuccess();
        $found_id = false;
        foreach ($this->getDataObjectArr() as $user_arr)
        {
            if ($user_arr['id'] == $User1Obj->id)
            {
                $found_id = true;
                break;
            }
        }
        $this->assertTrue($found_id);
        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/usersDetail/' . $User1Obj->id . ',' . $User2Obj->id);
        $this->assertApiSuccess();
        $this->assertEquals(2, count($this->getDataObjectArr()));
        $found_id = false;
        foreach ($this->getDataObjectArr() as $user_arr)
        {
            if ($user_arr['id'] == $User2Obj->id)
            {
                $found_id = true;
                break;
            }
        }
        $this->assertTrue($found_id);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_update_userDetails()
    {
        $this->setLoggedInUserRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
        $this->logInUser();
        /** @var  UserDetail $UserDetailObj */
        $UserObj = $this->ThirdGenericUserObj;

        /** @var  UserDetail $UserDetailObj */
        $UserDetailObj = $this->UserDetailRepositoryObj->find($UserObj->id);
        $UserDetailObj->setConfigJSON(Seeder::getFakeUserConfigJson());

        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $editedUser_arr */
        $editedUser_arr = $this->fakeUserData([], Seeder::DEFAULT_FACTORY_NAME);
        unset($editedUser_arr['email']);
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/userDetails/' . $UserDetailObj->id,
            $editedUser_arr
        );
        $this->assertApiSuccess();

        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        $this->logInUser();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_users()
    {
        /** @var  UserDetail $UserDetailObj */
        $UserDetailObj = $this->UserDetailRepositoryObj->find($this->getLoggedInUserObj()->id);
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $UserDetailObj->id
        );
        $this->assertApiSuccess();

        /** @var User $User1Obj */
        $User1Obj = $this->FifthGenericUserObj;
        /** @var User $User2Obj */
        $User2Obj = $this->SixthGenericUserObj;

        $this->setLoggedInUserRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
        $this->logInUser();
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/users'
        );
        $this->assertApiSuccess();

        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        $this->logInUser();

        $found_id = false;
        foreach ($this->getDataObjectArr() as $user_arr)
        {
            if ($user_arr['id'] == $User1Obj->id)
            {
                $found_id = true;
                break;
            }
        }
        $this->assertTrue($found_id);
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $User1Obj->id . ',' . $User2Obj->id
        );
        $this->assertApiSuccess();
        $this->assertEquals(2, count($this->getDataObjectArr()));
        $found_id = false;
        foreach ($this->getDataObjectArr() as $user_arr)
        {
            if ($user_arr['id'] == $User2Obj->id)
            {
                $found_id = true;
                break;
            }
        }
        $this->assertTrue($found_id);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_cannot_update_users()
    {
        /** @var  User $UserObj */
        $UserObj = $this->FirstGenericUserObj;

        $UserObj->setConfigJSON(Seeder::getFakeUserConfigJson());

        $UserObj->updateConfig('FOO', 'foo');
        $conf = $UserObj->getConfigJSON(true);
        $this->assertEquals('foo', $conf['FOO']);
        $conf = $UserObj->getConfigJSON();
        $this->assertEquals('foo', $conf->FOO);

        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $editedUser_arr */
        $editedUser_arr = $this->fakeUserData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $UserObj->id,
            $editedUser_arr
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_update_users()
    {
        $this->setLoggedInUserRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
        $this->logInUser();
        /** @var  UserDetail $UserDetailObj */
        $UserDetailObj = $this->SecondGenericUserObj;

        $UserDetailObj->setConfigJSON(Seeder::getFakeUserConfigJson());

        $UserDetailObj->updateConfig('FOO', 'foo');
        $conf = $UserDetailObj->getConfigJSON(true);
        $this->assertEquals('foo', $conf['FOO']);
        $conf = $UserDetailObj->getConfigJSON();
        $this->assertEquals('foo', $conf->FOO);

        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $editedUser_arr */
        $editedUser_arr = $this->fakeUserData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $UserDetailObj->id,
            $editedUser_arr
        );
        $this->assertApiSuccess();

        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        $this->logInUser();
    }

    /**
     * @test
     */
    public function it_can_access_accessibleGroups()
    {
        /** @var  User $UserObj */
        $UserObj = $this->SecondGenericUserObj;

        $this->json(
            'GET', '/api/v1/clients/' . $this->ClientObj->id . '/userDetails/' . $UserObj->id . '/accessibleGroups'
        );
        $this->assertApiSuccess();

        /**
         * v2
         */
        $this->json(
            'GET', '/api/v2/clients/' . $this->ClientObj->id . '/userDetails/' . $UserObj->id . '/accessibleGroups'
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
    public function it_can_read_roles_list()
    {
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/roles'
        );
        $this->assertAPIListResponse(RoleDetail::class);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
