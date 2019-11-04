<?php

namespace App\Waypoint\Tests\Artisan;

use App;
use App\Waypoint\Models\Client;
use App\Waypoint\Tests\Generated\MakeAccessListUserTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Artisan;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;

class ListAccessListsCommandTest extends TestCase
{
    use DatabaseTransactions;
    use MakeAccessListUserTrait;
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
        $AccessListUserObj = $this->fakeAccessListUser()->save();

        Artisan::call(
            'waypoint:list:access_lists',
            [
                '--client_ids' => $AccessListUserObj->accessList->client_id,
            ]
        );
        $this->assertNotEmpty($this->getActualOutput());
    }
}