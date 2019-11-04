<?php

namespace App\Waypoint\Tests\Artisan;

use App;
use App\Waypoint\Tests\Generated\MakeClientTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Artisan;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;

class ListAccessListUsersCommandTest extends TestCase
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
    public function artisan_access_list_users()
    {
        /**
         * @todo see https://stackoverflow.com/questions/33611788/how-to-test-artisan-commands-in-laravel-5 or
         *       post Laravel 5.7 see https://laravel-news.com/testing-artisan-commands-in-laravel-5-7
         */

        /** @var  array $client_arr */
        $client_arr = $this->fakeClientData();
        $this->ClientRepositoryObj->create($client_arr);

        $i = 0;
        foreach ($this->ClientRepositoryObj->all() as $ClientObj)
        {
            if ($ClientObj->id == 1)
            {
                continue;
            }
            Artisan::call(
                'waypoint:list:access_list_users',
                [
                    '--client_ids' => $ClientObj->id,
                ]
            );
            $this->assertNotEmpty($this->getActualOutput(), 'waypoint:list:access_list_users failed');

            if ($i++ > config('waypoint.unittest_loop'))
            {
                break;
            }
        }

        Artisan::call(
            'waypoint:list:access_list_users',
            [
                '--client_ids' => 'All',
            ]
        );
        $this->assertNotEmpty($this->getActualOutput());
    }
}