<?php

namespace App\Waypoint\Tests\Artisan;

use App;
use App\Waypoint\Tests\TestCase;
use Artisan;

class GenerateSystemInfoCommandTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     * @outputBuffering enabled
     * @throws \PHPUnit\Framework\Exception
     */
    public function test_artisan_command()
    {
        /**
         * @todo see https://stackoverflow.com/questions/33611788/how-to-test-artisan-commands-in-laravel-5 or
         *       post Laravel 5.7 see https://laravel-news.com/testing-artisan-commands-in-laravel-5-7
         */
        Artisan::call(
            'waypoint:list:system_information',
            []
        );

        $this->assertNotEmpty($this->getActualOutput());

        if ( ! preg_match('/See\ ([\S]*)/', $this->getActualOutput(), $gleaned))
        {
            $this->assertTrue(false, 'no sysout file');
        }

        $sys_info_json_filename = $gleaned[1];
        $sys_info_json          = file_get_contents($sys_info_json_filename);
        $ClientFromJsonObj      = json_decode($sys_info_json);

        $this->assertNotNull($ClientFromJsonObj->git);
        $this->assertNotNull($ClientFromJsonObj->git->full);
        $this->assertNotNull($ClientFromJsonObj->git->short);
        $this->assertNotNull($ClientFromJsonObj->laravel_config);
        $this->assertNotNull($ClientFromJsonObj->laravel_config->waypoint);
        $this->assertNotNull($ClientFromJsonObj->php);
        $this->assertNotNull($ClientFromJsonObj->php->phpinfo);
    }
}