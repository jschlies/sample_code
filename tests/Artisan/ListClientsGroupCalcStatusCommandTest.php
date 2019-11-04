<?php

namespace App\Waypoint\Tests\Artisan;

use App;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeClientTrait;
use App\Waypoint\Tests\TestCase;
use Artisan;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ListClientsGroupCalcStatusCommandTest extends TestCase
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
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function test_artisan_command()
    {
        /**
         * @todo see https://stackoverflow.com/questions/33611788/how-to-test-artisan-commands-in-laravel-5 or
         *       post Laravel 5.7 see https://laravel-news.com/testing-artisan-commands-in-laravel-5-7
         */

        Artisan::call(
            'waypoint:list:clients_group_calc_status',
            [
                '--client_ids' => 'All',
            ]
        );
        $this->assertNotEmpty($this->getActualOutput());
    }
}