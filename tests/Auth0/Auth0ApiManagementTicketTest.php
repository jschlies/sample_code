<?php

namespace App\Waypoint\Tests;

use App;
use App\Waypoint\Auth0\Auth0ApiManagementTicket;
use App\Waypoint\Auth0\Auth0ApiManagementUser;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AuthenticatingEntity;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementTicketMock;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementUserMock;

/**
 * Class UserRepositoryBaseTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class Auth0ApiManagementTicketTest extends TestCase
{
    use MakeUserTrait, ApiTestTrait;
    use MakeFavoriteTrait;

    /** @var  Auth0ApiManagementUser */
    protected $Auth0ApiManagementUserObj;
    /** @var  Auth0ApiManagementTicket */
    protected $Auth0ApiManagementTicketObj;

    /**
     * @why are we testing Auth0ApiManagementTicket???????
     *
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function setUp()
    {
        parent::setUp();
        $this->Auth0ApiManagementUserObj   = App::make(Auth0ApiManagementUserMock::class);
        $this->Auth0ApiManagementTicketObj = App::make(Auth0ApiManagementTicketMock::class);

        /** @var  array $user_arr */
        $user_arr      = $this->fakeUserData();
        $this->UserObj = $this->UserRepositoryObj->create($user_arr);
        /**
         * removing this sleep() causes Auth0 to fail. Must be a caching/latency issue someplace
         */
        sleep(3);
    }

    /**
     * @test create
     *
     * @throws GeneralException
     * @throws \Exception
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_processes_user_email_verification_ticket()
    {
        $this->UserObj = $this->FirstGenericUserObj;

        $response = $this->Auth0ApiManagementTicketObj->create_email_verification_ticket(
            $this->FirstGenericUserObj->email,
            'http://google.com',
            AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION
        );
        $this->assertTrue(TestCase::is_syntactially_valid_url($response->ticket));
    }

    /**
     * @test create
     *
     * @throws GeneralException
     * @throws \Exception
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_processes_password_change_ticket()
    {
        $this->UserObj = $this->SeventhGenericUserObj;

        $new_password = Seeder::getRandomString(16);
        $response     = $this->Auth0ApiManagementTicketObj->create_password_change_ticket($this->UserObj->email, 'http://google.com', 600, $new_password);
        $this->assertTrue(TestCase::is_syntactially_valid_url($response->ticket));
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}