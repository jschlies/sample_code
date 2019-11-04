<?php

namespace App\Waypoint\Tests\Artisan\Artisan;

use App;
use App\Waypoint\Console\Commands\GenerateGenericUsersCommand;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Models\User;
use App\Waypoint\Tests\Generated\MakeAccessListPropertyTrait;
use App\Waypoint\Tests\Generated\MakeClientTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Artisan;

class GenerateGenericUsersCommandTest extends TestCase
{
    use DatabaseTransactions;
    use MakeClientTrait;
    use MakeAccessListPropertyTrait, ApiTestTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\Exception
     */
    public function test_artisan_command()
    {
        /**
         * @todo see https://stackoverflow.com/questions/33611788/how-to-test-artisan-commands-in-laravel-5 or
         *       post Laravel 5.7 see https://laravel-news.com/testing-artisan-commands-in-laravel-5-7
         */
        $resultAsText = Artisan::call(
            'waypoint:generate_generic_users',
            [
                '--client_ids' => $this->ClientObj->id,
            ]
        );

        $this->assertEmpty($resultAsText);

        /**
         * Refresh Client obj
         */
        $this->ClientObj->refresh();

        $testEmailsArr = GenerateGenericUsersCommand::testEmailsArr($this->ClientObj->id);
        foreach ($testEmailsArr as $testEmails)
        {
            if ( ! $UserObj = User::where(['email' => $testEmails['email'], 'client_id' => $this->ClientObj->id])->get()->first())
            {
                $this->assertTrue(false, 'did not create user');
            }
            $this->assertEquals(User::where(['email' => $testEmails['email'], 'client_id' => $this->ClientObj->id])->get()->count(), 1);
            $this->assertTrue($UserObj->hasRole($testEmails['role']), 'did not create user');
        }

        $testEmailsArr = GenerateGenericUsersCommand::testEmailsArr($this->ClientObj->id);
        foreach ($testEmailsArr as $testEmails)
        {
            if ( ! $UserObj = User::where(['email' => $testEmails['email'], 'client_id' => $this->ClientObj->id])->get()->first())
            {
                $this->assertTrue(false, 'did not create user');
            }
            $this->assertEquals(User::where(['email' => $testEmails['email'], 'client_id' => $this->ClientObj->id])->get()->count(), 1);
            $this->assertTrue($UserObj->hasRole($testEmails['role']), 'did not create user');

            $this->assertEquals(ApiKey::where(['user_id' => $UserObj->id])->count(), 1);
            $ApiKeyObj = ApiKey::where(['user_id' => $UserObj->id])->get()->first();

            $this->assertEquals(get_class($ApiKeyObj), ApiKey::class);
        }
    }
}