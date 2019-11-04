<?php

namespace App\Waypoint\Tests\Artisan;

use App;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Tests\Generated\MakePropertyGroupTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Artisan;

class ListPropertyGroupsCommandTest extends TestCase
{
    use DatabaseTransactions;
    use MakePropertyGroupTrait;
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

        /** @var PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj = $this->fakePropertyGroup()->save();

        Artisan::call(
            'waypoint:list:property_groups',
            [
                '--client_ids' => $PropertyGroupObj->user->client_id,
            ]
        );
        $this->assertNotEmpty($this->getActualOutput());
    }
}