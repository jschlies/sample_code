<?php

namespace App\Waypoint\Tests\Artisan;

use App;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeClientTrait;
use App\Waypoint\Tests\TestCase;
use Artisan;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GenerateJavaScriptConfigCommandTest extends TestCase
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

        $javascript_file_name = config('waypoint.javascript_config_path');

        if (file_exists($javascript_file_name))
        {
            unlink($javascript_file_name);
        }

        $resultAsText = Artisan::call(
            'waypoint:generate_javaScript_config',
            []
        );

        $this->assertEmpty($resultAsText);
        $this->assertFileExists($javascript_file_name);
    }
}