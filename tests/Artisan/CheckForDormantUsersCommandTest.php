<?php

namespace App\Waypoint\Tests\Artisan;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\User;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;
use Artisan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CheckForDormantUsersCommandTest extends TestCase
{
    use DatabaseTransactions;
    use MakeUserTrait;
    use ApiTestTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test create
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

        $UserObj = $this->FirstGenericUserObj;
        $this->ClientRepositoryObj->update(
            [
                'dormant_user_switch' => true,
                'dormant_user_ttl'    => 60,
            ],
            $UserObj->client_id
        );
        $this->UserRepositoryObj->update(
            [
                'last_login_date' => Carbon::now()->addSeconds(-10000000)->format('Y-m-d H:i:s'),
            ],
            $UserObj->id
        );

        Artisan::call(
            'waypoint:check_for_dormant:users',
            [
                '--client_ids' => $UserObj->client_id,
            ]
        );

        $UserObj = $this->UserRepositoryObj->find($UserObj->id);

        $this->assertEquals($UserObj->active_status, User::ACTIVE_STATUS_INACTIVE);
        $this->assertNotNull($UserObj->dormant_user_date);
    }
}