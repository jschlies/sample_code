<?php

namespace App\Waypoint\Tests\Artisan;

use App;
use App\Waypoint\Models\Client;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\Generated\MakeClientTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Artisan;

class DeleteClientCommandTest extends TestCase
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
     * @throws \PHPUnit\Framework\Exception
     */
    public function test_artisan_command()
    {
        /**
         * @todo see https://stackoverflow.com/questions/33611788/how-to-test-artisan-commands-in-laravel-5 or
         *       post Laravel 5.7 see https://laravel-news.com/testing-artisan-commands-in-laravel-5-7
         */
        /** @var Client $ClientObj */
        $ClientObj    = $this->makeClient();
        $resultAsText = Artisan::call(
            'waypoint:delete:client',
            [
                '--client_id' => $ClientObj->id,
            ]
        );

        $this->assertEmpty($resultAsText);

        if (Client::find($ClientObj->id))
        {
            $this->assertFalse(true, 'did not delete file');
        }
    }
}