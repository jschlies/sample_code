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
class SpreadsheetDeprecatedController extends LedgerController
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
     * @param $ledgerDataType
     * @param int $id
     * @param $report
     * @param $year
     * @param $period
     * @param $area
     * @param $account_code
     */
    public function createCombinedOperatingExpensesSpreadsheet($ledgerDataType, $id, $report, $year, $period, $area, $report_template_account_group_id)
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
     * @param $report
     * @param $year
     * @param $period
     * @param $area
     * @param $account_code
     */
    public function createCombinedVarianceSpreadsheet($ledgerDataType, $id, $report, $year, $period, $area, $report_template_account_group_id)
    {
        list($expenseControllerName, $rankingControllerName) = $this->getExpensesAndRankingControllerName(self::VARIANCE, $ledgerDataType);
        $expensesData = (new $expenseControllerName(new VarianceRepository(new Container())))
            ->index($id, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        $rankingData  = (new $rankingControllerName(new VarianceRankingRepository(new Container())))
            ->index($id, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        (new Spreadsheet())->createCombinedLedgerSpreadsheet($expensesData, $rankingData); // create spreadsheet
    }

    /**
     * @param $ledgerDataType
     * @param int $id
     * @param $report
     * @param $year
     * @param $period
     * @param $area
     * @param $account_code
     */
    public function createCombinedPeerAverageSpreadsheet($ledgerDataType, $id, $report, $year, $period, $area, $report_template_account_group_id)
    {
        list($expenseControllerName, $rankingControllerName) = $this->getExpensesAndRankingControllerName(self::PEER_AVERAGE, $ledgerDataType);
        $expensesData = (new $expenseControllerName(new PeerAverageRepository(new Container())))
            ->index($id, $report, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        $rankingData  = (new $rankingControllerName(new PeerAverageRankingRepository(new Container())))
            ->index($id, $report, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        (new Spreadsheet())->createCombinedLedgerSpreadsheet($expensesData, $rankingData); // create spreadsheet
    }

    /**
     * @param $ledgerDataType
     * @param int $id
     * @param $report
     * @param $year
     * @param $period
     * @param $area
     * @param $account_code
     */
    public function createCombinedYearOverYearSpreadsheet($ledgerDataType, $id, $report, $year, $period, $area, $report_template_account_group_id)
    {
        list($expenseControllerName, $rankingControllerName) = $this->getExpensesAndRankingControllerName(self::YEAR_OVER_YEAR, $ledgerDataType);
        $expensesData = (new $expenseControllerName(new YearOverYearRepository(new Container())))
            ->index($id, $report, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        $rankingData  = (new $rankingControllerName(new YearOverYearRankingRepository(new Container())))
            ->index($id, $report, $year, $period, $area, $report_template_account_group_id, $this->suppressResponse);
        (new Spreadsheet())->createCombinedLedgerSpreadsheet($expensesData, $rankingData); // create spreadsheet
    }

    /**
     * @param $ledgerDataType
     * @param int $id
     */
    public function createCombinedOverviewSpreadsheet(
        $ledgerDataType,
        $entity_id,
        $report_template_account_group_id
    ) {

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
                    $ns . 'OperatingExpenses' . $ledgerDataTypeSignifier . 'DeprecatedController',
                    $ns . 'OperatingExpenses' . $ledgerDataTypeSignifier . 'RankingDeprecatedController',
                ];
            case self::VARIANCE:
                return [
                    $ns . 'Variance' . $ledgerDataTypeSignifier . 'DeprecatedController',
                    $ns . 'Variance' . $ledgerDataTypeSignifier . 'RankingDeprecatedController',
                ];
            case self::PEER_AVERAGE:
                return [
                    $ns . 'PeerAverage' . $ledgerDataTypeSignifier . 'DeprecatedController',
                    $ns . 'PeerAverage' . $ledgerDataTypeSignifier . 'RankingDeprecatedController',
                ];
            case self::YEAR_OVER_YEAR:
                return [
                    $ns . 'YearOverYear' . $ledgerDataTypeSignifier . 'DeprecatedController',
                    $ns . 'YearOverYear' . $ledgerDataTypeSignifier . 'RankingDeprecatedController',
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
        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            return [
                $namespace . 'OperatingExpenses' . $ledgerDataTypeSignifier . 'DeprecatedController',
                $namespace . 'Variance' . $ledgerDataTypeSignifier . 'DeprecatedController',
                $namespace . 'YearOverYear' . $ledgerDataTypeSignifier . 'DeprecatedController',
            ];
        }
        return [
            $namespace . 'OperatingExpenses' . $ledgerDataTypeSignifier . 'DeprecatedController',
            $namespace . 'Variance' . $ledgerDataTypeSignifier . 'DeprecatedController',
            $namespace . 'YearOverYear' . $ledgerDataTypeSignifier . 'DeprecatedController',
        ];
    }

}