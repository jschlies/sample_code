<?php

namespace App\Waypoint\Tests\Artisan;

use App;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use \App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use Artisan;

class ListPropertiesCommandTest extends TestCase
{
    use DatabaseTransactions;
    use MakePropertyTrait;
    use ApiTestTrait;

    /**
     * @var ClientRepository $ClientRepositoryObj
     */
    protected $ClientRepositoryObj;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws \App\Waypoint\Exceptions\ValidationException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function test_artisan_command()
    {
        /**
         * @todo see https://stackoverflow.com/questions/33611788/how-to-test-artisan-commands-in-laravel-5 or
         *       post Laravel 5.7 see https://laravel-news.com/testing-artisan-commands-in-laravel-5-7
         */

        Artisan::call(
            'waypoint:list:properties',
            [
                '--client_ids' => $this->ClientObj->id,
            ]
        );

        $this->assertNotEmpty($this->getActualOutput());
    }
}