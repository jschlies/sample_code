<?php

namespace App\Waypoint\Tests;

use App\Waypoint\Tests\Generated\MakeAccessListUserTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Artisan;

class PostmanCollectionCommandTest extends TestCase
{
    use DatabaseTransactions;
    use MakeAccessListUserTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function test_artisan_command()
    {
        /**
         * @todo see https://stackoverflow.com/questions/33611788/how-to-test-artisan-commands-in-laravel-5 or
         *       post Laravel 5.7 see https://laravel-news.com/testing-artisan-commands-in-laravel-5-7
         */

        $resultAsText = Artisan::call(
            'waypoint:postman_collection',
            []
        );
        $this->assertEmpty($resultAsText);
    }
}