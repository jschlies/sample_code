<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Models\Spreadsheet;
use App\Waypoint\Repositories\Ledger\OccupancyRepository;
use App\Waypoint\Repositories\Ledger\SpreadsheetRepository;
use App\Waypoint\Repositories\Ledger\OperatingExpensesRankingRepository;
use App\Waypoint\Repositories\Ledger\OperatingExpensesRepository;
use App\Waypoint\Repositories\Ledger\YearOverYearRankingRepository;
use App\Waypoint\Repositories\Ledger\YearOverYearRepository;
use App\Waypoint\Repositories\Ledger\PeerAverageRankingRepository;
use App\Waypoint\Repositories\Ledger\PeerAverageRepository;
use App\Waypoint\Repositories\Ledger\VarianceRankingRepository;
use App\Waypoint\Repositories\Ledger\VarianceRepository;
use Illuminate\Container\Container;
use League\Flysystem\Exception;

/**
 * Class OccupancyPropertyController
 * @package App\Waypoint\Http\Controllers\ApiRequest\Ledger
 */
class SpreadsheetController extends LedgerController
{

    /** @var  OccupancyRepository */
    private $SpreadsheetRepository;

    /**
     * @var bool
     */
    private $suppressResponse = true;

    /**
     * OperatingExpensesPropertyController constructor.
     * @param SpreadsheetRepository $OccupancyRepo
     */
    public function __construct(SpreadsheetRepository $SpreadsheetRepo)
    {
        $this->SpreadsheetRepository = $SpreadsheetRepo;
        parent::__construct($SpreadsheetRepo);
    }

    /**
     * @param $client_id
     * @param $ledgerDataType
     * @param $id
     * @param $report
     * @param $year
     * @param $period
     * @param $area
     * @param $report_template_account_group_id
     * @throws Exception
     * @throws \App\Waypoint\Exceptions\GeneralException
     */
    public function createCombinedOperatingExpensesSpreadsheet($client_id, $ledgerDataType, $id, $report, $year, $period, $area, $report_template_account_group_id)
    {
        list($expenseControllerName, $rankingControllerName) = $this->getExpensesAndRankingControllerName(self::OPERATING_EXPENSES, $ledgerDataType);
        $expensesData = (new $expenseControllerName(new OperatingExpensesRepository(new Container())))
            ->index($id, $report, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        $rankingData  = (new $rankingControllerName(new OperatingExpensesRankingRepository(new Container())))
            ->index($id, $report, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        (new Spreadsheet())->createCombinedLedgerSpreadsheet($expensesData, $rankingData); // create spreadsheet
    }

    /**
     * @param $ledgerDataType
     * @param int $id
     * @param int $client_id
     * @param $report
     * @param $year
     * @param $period
     * @param $area
     * @param $account_code
     */
    public function createCombinedVarianceSpreadsheet($client_id, $ledgerDataType, $id, $report, $year, $period, $area, $report_template_account_group_id)
    {
        list($expenseControllerName, $rankingControllerName) = $this->getExpensesAndRankingControllerName(self::VARIANCE, $ledgerDataType);
        $expensesData = (new $expenseControllerName(new VarianceRepository(new Container())))
            ->index($id, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        $rankingData  = (new $rankingControllerName(new VarianceRankingRepository(new Container())))
            ->index($id, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        (new Spreadsheet())->createCombinedLedgerSpreadsheet($expensesData, $rankingData); // create spreadsheet
    }

    /**
     * @param $client_id
     * @param $ledgerDataType
     * @param $id
     * @param $report
     * @param $year
     * @param $period
     * @param $area
     * @param $report_template_account_group_id
     * @throws Exception
     * @throws \App\Waypoint\Exceptions\GeneralException
     */
    public function createCombinedPeerAverageSpreadsheet($client_id, $ledgerDataType, $id, $report, $year, $period, $area, $report_template_account_group_id)
    {
        list($expenseControllerName, $rankingControllerName) = $this->getExpensesAndRankingControllerName(self::PEER_AVERAGE, $ledgerDataType);
        $expensesData = (new $expenseControllerName(new PeerAverageRepository(new Container())))
            ->index($id, $report, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        $rankingData  = (new $rankingControllerName(new PeerAverageRankingRepository(new Container())))
            ->index($id, $report, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        (new Spreadsheet())->createCombinedLedgerSpreadsheet($expensesData, $rankingData); // create spreadsheet
    }

    /**
     * @param $client_id
     * @param $ledgerDataType
     * @param $id
     * @param $report
     * @param $year
     * @param $period
     * @param $area
     * @param $report_template_account_group_id
     * @throws Exception
     * @throws \App\Waypoint\Exceptions\GeneralException
     */
    public function createCombinedYearOverYearSpreadsheet($client_id, $ledgerDataType, $id, $report, $year, $period, $area, $report_template_account_group_id)
    {
        list($expenseControllerName, $rankingControllerName) = $this->getExpensesAndRankingControllerName(self::YEAR_OVER_YEAR, $ledgerDataType);
        $expensesData = (new $expenseControllerName(new YearOverYearRepository(new Container())))
            ->index($id, $report, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        $rankingData  = (new $rankingControllerName(new YearOverYearRankingRepository(new Container())))
            ->index($id, $report, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        (new Spreadsheet())->createCombinedLedgerSpreadsheet($expensesData, $rankingData); // create spreadsheet
    }

    /**
     * @param $client_id
     * @param $ledgerDataType
     * @param $entity_id
     * @param $report_template_account_group_id
     * @throws \App\Waypoint\Exceptions\GeneralException
     */
    public function createCombinedOverviewSpreadsheet($client_id, $ledgerDataType, $entity_id, $report_template_account_group_id)
    {
        $defaults = $this->getCurrentLoggedInUserObj()->client->getConfigJSON()->WAYPOINT_LEDGER_DROPDOWNS->DEFAULTS;
        list($operatingExpesesControllerName, $varianceControllerName, $yearOverYearControllerName) = $this->getAllExpensesControllerNames($ledgerDataType);

        $operatingExpensesData =
            (
            new $operatingExpesesControllerName(
                new OperatingExpensesRepository(
                    new Container()
                )
            )
            )->index(
                    $entity_id,
                    $defaults->report,
                    $defaults->year,
                    $defaults->period,
                    $defaults->area,
                    $report_template_account_group_id,
                    $this->suppressResponse
                );

        $varianceData =
            (
            new $varianceControllerName(
                new VarianceRepository(
                    new Container()
                )
            )
            )->index(
                    $entity_id,
                    $defaults->year,
                    $defaults->period,
                    $defaults->area,
                    $report_template_account_group_id,
                    $this->suppressResponse
                );

        $yearOverYearData =
            (
            new $yearOverYearControllerName(
                new YearOverYearRepository(
                    new Container()
                )
            )
            )->index(
                    $entity_id,
                    $defaults->report,
                    $defaults->year,
                    $defaults->period,
                    $defaults->area,
                    $report_template_account_group_id,
                    $this->suppressResponse
                );

        (new Spreadsheet())->createCombinedOverviewSpreadsheet(
            $operatingExpensesData,
            $varianceData,
            $yearOverYearData
        );
    }

    /**
     * @param $chartType
     * @param $ledgerDataType
     * @return array
     * @throws \League\Flysystem\Exception
     */
    private function getExpensesAndRankingControllerName($chartType, $ledgerDataType)
    {
        $ledgerDataTypeSignifier = $ledgerDataType == 'group' ? 'PropertyGroup' : 'Property';
        $ns                      = __NAMESPACE__ . '\\';
        switch ($chartType)
        {
            case self::OPERATING_EXPENSES:
                return [
                    $ns . 'OperatingExpenses' . $ledgerDataTypeSignifier . 'Controller',
                    $ns . 'OperatingExpenses' . $ledgerDataTypeSignifier . 'RankingController',
                ];
            case self::VARIANCE:
                return [
                    $ns . 'Variance' . $ledgerDataTypeSignifier . 'Controller',
                    $ns . 'Variance' . $ledgerDataTypeSignifier . 'RankingController',
                ];
            case self::PEER_AVERAGE:
                return [
                    $ns . 'PeerAverage' . $ledgerDataTypeSignifier . 'Controller',
                    $ns . 'PeerAverage' . $ledgerDataTypeSignifier . 'RankingController',
                ];
            case self::YEAR_OVER_YEAR:
                return [
                    $ns . 'YearOverYear' . $ledgerDataTypeSignifier . 'Controller',
                    $ns . 'YearOverYear' . $ledgerDataTypeSignifier . 'RankingController',
                ];
            default:
                throw new Exception('chart type unusable');
        }
    }

    /**
     * @param $ledgerDataType
     * @return array
     */
    private function getAllExpensesControllerNames($ledgerDataType)
    {
        $ledgerDataTypeSignifier = $ledgerDataType == 'group' ? 'PropertyGroup' : 'Property';
        $namespace               = __NAMESPACE__ . '\\';
        return [
            $namespace . 'OperatingExpenses' . $ledgerDataTypeSignifier . 'Controller',
            $namespace . 'Variance' . $ledgerDataTypeSignifier . 'Controller',
            $namespace . 'YearOverYear' . $ledgerDataTypeSignifier . 'Controller',
        ];
    }

}