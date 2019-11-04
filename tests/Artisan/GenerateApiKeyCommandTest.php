<?php

namespace App\Waypoint\Tests\Artisan;

use App;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Models\User;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;
use Artisan;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GenerateApiKeyCommandTest extends TestCase
{
    use DatabaseTransactions;
    use MakeUserTrait;
    use ApiTestTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     * @outputBuffering enabled
     * @throws \PHPUnit\Framework\Exception
     */
    public function test_artisan_command()
    {
        /**
         * @todo see https://stackoverflow.com/questions/33611788/how-to-test-artisan-commands-in-laravel-5 or
         *       post Laravel 5.7 see https://laravel-news.com/testing-artisan-commands-in-laravel-5-7
         */
        /** @var User $UserObj */
        $UserObj = $this->fakeUser()->save();

        $this->expectOutputRegex('/Creating ApiKey\ ([\S]*)/');
        Artisan::call(
            'waypoint:generate_api_key',
            [
                '--client_id' => $UserObj->client_id,
                '--email'     => $UserObj->email,
            ]
        );

        if ( ! preg_match('/Creating ApiKey\ ([\S]*)/', $this->getActualOutput(), $gleaned))
        {
            $this->assertTrue(false, 'api key');
        }

        /** @var ApiKey $ApiKeyObj */
        $ApiKeyObj = ApiKey::where(['key' => $gleaned[1]])->get()->first();
        $ApiKeyObj->validate();

        $this->assertEquals(get_class($ApiKeyObj), ApiKey::class);

        $this->assertEquals($UserObj->id, $ApiKeyObj->user_id);
    }
}