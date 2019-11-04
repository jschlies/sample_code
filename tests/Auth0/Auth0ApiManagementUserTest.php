<?php

namespace App\Waypoint\Tests;

use App\Waypoint\Auth0\Auth0ApiManagementUser;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\User;
use App;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementUserMock;

/**
 * Class UserRepositoryBaseTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class Auth0ApiManagementUserTest extends TestCase
{
    use MakeUserTrait, ApiTestTrait;
    use MakeFavoriteTrait;

    /** @var  Auth0ApiManagementUser */
    protected $Auth0ApiManagementUserObj;
    /** @var  User */
    protected $UserObj;

    public function setUp()
    {
        parent::setUp();
        $this->Auth0ApiManagementUserObj = App::make(Auth0ApiManagementUserMock::class);

        /** @var  array $user_arr */
        $user_arr      = $this->fakeUserData();
        $this->UserObj = $this->UserRepositoryObj->create($user_arr);
        /**
         * removing this sleep() causes Auth0 to fail. Must be a caching/latency issue someplace
         */
        sleep(3);
    }

    /**
     * @test
     *
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws \Exception
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     */
    public function it_processes_users()
    {
        /** @var  array $user_arr */
        $user_arr = $this->fakeUserData();

        $this->UserObj = $this->UserRepositoryObj->create($user_arr);
        /**
         * removing this sleep() causes Auth0 to fail. Must be a caching/latency issue someplace
         */
        sleep(3);

        $this->assertJson($this->UserObj->creation_auth0_response);
        /** @var User $FoundUser */
        $FoundUser = $this->Auth0ApiManagementUserObj->get_user_with_email($this->UserObj->email, $this->UserObj->authenticatingEntity->identity_connection);

        $this->assertEquals($FoundUser->email, $user_arr['email']);

        $AllUsers = $this->Auth0ApiManagementUserObj->get_all_users();
        $this->assertTrue(is_array($AllUsers));
        $found_id = false;
        foreach ($AllUsers as $LocalUserObj)
        {
            if ($LocalUserObj->email == $user_arr['email'])
            {
                $found_id = true;
                break;
            }
        }
        $this->assertTrue($found_id, 'Did not find created user in auth0');

        $FoundUserArr = $this->Auth0ApiManagementUserObj->search_users(
            ['email.raw' => $FoundUser->email]
        );

        $this->assertEquals(1, count($FoundUserArr));
        $this->assertEquals($FoundUser->email, $FoundUserArr[0]->email);
        $this->assertEquals($FoundUser->username, $FoundUserArr[0]->username);

        $FoundUserArr = $this->Auth0ApiManagementUserObj->search_users(
            ['username' => $FoundUser->username]
        );
        $this->assertEquals($FoundUser->email, $FoundUserArr[0]->email);
        $this->assertEquals($FoundUser->username, $FoundUserArr[0]->username);
    }

    /**
     * @test create
     *
     * @throws GeneralException
     * @throws \Exception
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_gets_user_logs()
    {
        $this->UserObj = $this->SecondGenericUserObj;

        /**
         * @todo add some updates and other interesting activity
         */
        $log_arr = $this->Auth0ApiManagementUserObj->get_user_logs($this->UserObj->email);
        $this->assertTrue(is_array($log_arr));
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}