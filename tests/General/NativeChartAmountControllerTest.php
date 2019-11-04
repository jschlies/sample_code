<?php

namespace App\Waypoint\Tests\General;

use App;
use App\Waypoint\Http\Controllers\Api\NativeChartAmountController;
use App\Waypoint\Tests\TestCase;
use Carbon\Carbon;

class NativeChartAmountControllerTest extends TestCase
{
    private $NativeChartAmountController;

    public function setUp()
    {
        parent::setUp();

        $this->NativeChartAmountController = App::make(NativeChartAmountController::class);
    }

    /**
     * @test
     */
    public function process_native_amount_controller_input_parser_for_custom_date_range()
    {
        $Input = [
            'fromMonth' => '01',
            'fromYear'  => '2018',
            'toMonth'   => '02',
            'toYear'    => '2019',
        ];

        list($input, $RequestedFromDateObj, $RequestedToDateObj, $BenchmarkGenerationDateObj)
            = $this->NativeChartAmountController->processInputForNativeChartAmountController($this->ClientObj->id, $Input);

        $ExpectedFromDate = Carbon::create($Input['fromYear'], $Input['fromMonth'])->startOfMonth()->setTime(0, 0, 0);
        $ExpectedToDate   = Carbon::create($Input['toYear'], $Input['toMonth'])->endOfMonth();

        $this->assertEquals($ExpectedFromDate, $RequestedFromDateObj);
        $this->assertEquals($ExpectedToDate, $RequestedToDateObj);
    }

    /**
     * @test
     */
    public function process_native_amount_controller_input_parser_for_latest_month()
    {
        $Input = [
            'fromMonth' => '01',
            'fromYear'  => '2018',
        ];

        list($input, $RequestedFromDateObj, $RequestedToDateObj, $BenchmarkGenerationDateObj)
            = $this->NativeChartAmountController->processInputForNativeChartAmountController($this->ClientObj->id, $Input);

        $ExpectedFromDate = Carbon::create($Input['fromYear'], $Input['fromMonth'])->startOfMonth()->setTime(0, 0, 0);
        $ExpectedToDate   = Carbon::create($Input['fromYear'], $Input['fromMonth'])->endOfMonth();

        $this->assertEquals($ExpectedFromDate, $RequestedFromDateObj);
        $this->assertEquals($ExpectedToDate, $RequestedToDateObj);
    }

    /**
     * @test
     */
    public function process_native_amount_controller_input_parser_for_year_to_date()
    {
        $Input = [
            'fromMonth' => '01',
            'fromYear'  => '2018',
            'toMonth'   => '08',
            'toYear'    => '2018',
        ];

        list($input, $RequestedFromDateObj, $RequestedToDateObj, $BenchmarkGenerationDateObj)
            = $this->NativeChartAmountController->processInputForNativeChartAmountController($this->ClientObj->id, $Input);

        $ExpectedFromDate = Carbon::create($Input['fromYear'], $Input['fromMonth'])->startOfMonth()->setTime(0, 0, 0);
        $ExpectedToDate   = Carbon::create($Input['toYear'], $Input['toMonth'])->endOfMonth();

        $this->assertEquals($ExpectedFromDate, $RequestedFromDateObj);
        $this->assertEquals($ExpectedToDate, $RequestedToDateObj);
    }

    /**
     * @test
     */
    public function process_native_amount_controller_input_parser_for_calendar_year()
    {
        $Input = [
            'year' => '2018',
        ];

        list($input, $RequestedFromDateObj, $RequestedToDateObj, $BenchmarkGenerationDateObj)
            = $this->NativeChartAmountController->processInputForNativeChartAmountController($this->ClientObj->id, $Input);

        $ExpectedFromDate = Carbon::create($Input['year'], 1, 1)->startOfMonth()->setTime(0, 0, 0);
        $ExpectedToDate   = Carbon::create($Input['year'], 12, 31)->endOfMonth();

        $this->assertEquals($ExpectedFromDate, $RequestedFromDateObj);
        $this->assertEquals($ExpectedToDate, $RequestedToDateObj);
    }
}