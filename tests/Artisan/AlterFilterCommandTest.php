<?php

namespace App\Waypoint\Tests\Artisan;

use App;
use App\Waypoint\Models\Client;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeClientTrait;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Artisan;
use function in_array;

class AlterFilterCommandTest extends TestCase
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
     * @throws \Exception
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function test_artisan_command()
    {
        /**
         * @todo see https://stackoverflow.com/questions/33611788/how-to-test-artisan-commands-in-laravel-5 or
         *       post Laravel 5.7 see https://laravel-news.com/testing-artisan-commands-in-laravel-5-7
         */
        $filter_name  = Seeder::getFakeName();
        $filter_value = mt_rand();
        $resultAsText = Artisan::call(
            'waypoint:filter:alter',
            [
                '--client_id'      => $this->ClientObj->id,
                '--filter_name'    => $filter_name,
                '--filter_options' => $filter_value,
            ]
        );

        $this->assertEquals($resultAsText, 0);
        $this->ClientObj = Client::find($this->ClientObj->id);
        $this->assertTrue(
            in_array(
                $filter_value,
                $this->ClientObj->getConfigJSON(true)[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::FILTERS_CONFIG_KEY][$filter_name]
            )
        );
    }
}