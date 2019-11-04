<?php

namespace App\Waypoint\Tests\Artisan;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\UserAdmin;
use App\Waypoint\Tests\Generated\MakeClientTrait;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use Artisan;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AddUsersCommandTest extends TestCase
{
    use DatabaseTransactions;
    use MakeClientTrait;
    use ApiTestTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \App\Waypoint\Exceptions\ValidationException
     * @throws \Exception
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function test_artisan_command()
    {
        /**
         * @todo see https://stackoverflow.com/questions/33611788/how-to-test-artisan-commands-in-laravel-5 or
         *       post Laravel 5.7 see https://laravel-news.com/testing-artisan-commands-in-laravel-5-7
         */

        $random_email = mt_rand() . 'foo@xxx.com';
        Artisan::call(
            'waypoint:add_user',
            [
                '--client_id'                     => $this->ClientObj->id,
                '--firstname'                     => mt_rand() . 'foo',
                '--lastname'                      => mt_rand() . 'foo',
                '--email'                         => $random_email,
                '--password'                      => mt_rand() . 'foo',
                '--add_to_client_all_access_list' => true,
                '--role'                          => Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
                '--email_verified'                => false,
                '--is_hidden'                     => true,
            ]
        );

        /** @var UserAdmin $UserAdminObj */
        $UserAdminObj = $this->UserAdminRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'email'     => $random_email,
            ]
        )->first();

        $this->assertEquals(get_class($UserAdminObj), App\Waypoint\Models\UserAdmin::class);
        $this->assertTrue($UserAdminObj->hasRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE));
        $this->assertEquals(1, $UserAdminObj->is_hidden);
        $this->assertEquals($random_email, $UserAdminObj->email);
        $this->assertEquals($this->ClientObj->id, $UserAdminObj->client_id);

        $random_email = mt_rand() . 'foo@xxx.com';
        Artisan::call(
            'waypoint:add_user',
            [
                '--client_id'                     => $this->ClientObj->id,
                '--firstname'                     => mt_rand() . 'foo',
                '--lastname'                      => mt_rand() . 'foo',
                '--email'                         => $random_email,
                '--password'                      => mt_rand() . 'foo',
                '--add_to_client_all_access_list' => true,
                '--role'                          => Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
                '--email_verified'                => false,
                '--is_hidden'                     => true,
            ]
        );

        $UserAdminObj = $this->UserAdminRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'email'     => $random_email,
            ]
        )->first();

        $this->assertEquals(get_class($UserAdminObj), UserAdmin::class);
        $this->assertTrue($UserAdminObj->hasRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE));
        $this->assertEquals(1, $UserAdminObj->is_hidden);
        $this->assertEquals($random_email, $UserAdminObj->email);
        $this->assertEquals($this->ClientObj->id, $UserAdminObj->client_id);

        $new_firstname = mt_rand() . 'foo';
        Artisan::call(
            'waypoint:add_user',
            [
                '--client_id'                     => $this->ClientObj->id,
                '--firstname'                     => $new_firstname,
                '--lastname'                      => mt_rand() . 'foo',
                '--email'                         => $random_email,
                '--password'                      => mt_rand() . 'foo',
                '--add_to_client_all_access_list' => true,
                '--role'                          => Role::CLIENT_ADMINISTRATIVE_USER_ROLE,
                '--email_verified'                => false,
                '--is_hidden'                     => true,
            ]
        );

        $UserAdminObj = $this->UserAdminRepositoryObj->findWhere(
            [
                'client_id' => $this->ClientObj->id,
                'email'     => $random_email,
            ]
        )->first();
        $this->assertEquals($random_email, $UserAdminObj->email);
        $this->assertEquals($new_firstname, $UserAdminObj->firstname);
    }
}