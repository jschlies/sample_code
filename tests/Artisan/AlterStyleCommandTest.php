<?php

namespace App\Waypoint\Tests\Artisan;

use App;
use App\Waypoint\Console\Commands\AlterStyleCommand;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\Generated\MakeClientTrait;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AlterStyleCommandTest extends TestCase
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
     * @throws Exception
     */
    public function test_artisan_command()
    {
        /**
         * @todo see https://stackoverflow.com/questions/33611788/how-to-test-artisan-commands-in-laravel-5 or
         *       post Laravel 5.7 see https://laravel-news.com/testing-artisan-commands-in-laravel-5-7
         */

        $style_property       = 'STYLE_PROPERTY' . mt_rand();
        $style_value          = mt_rand();
        $AlterStyleCommandObj = new AlterStyleCommand();
        $AlterStyleCommandObj->processAlterStyleCommand(
            $this->ClientObj->id,
            $style_property,
            $style_value
        );
        $this->assertEquals($this->getUnitTestClient(1)->getConfigJSON()->$style_property, $style_value);
    }
}