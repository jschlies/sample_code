<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\Controllers\Api\Ledger\LedgerController;
use App\Waypoint\Model as ModelBase;
use App\Waypoint\Repositories\AdvancedVarianceLineItemRepository;
use Excel;
use Exception;
use Illuminate\Support\Facades\Request;
use function is_null;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;

/**
 * Class Spreadsheet
 * @package App\Waypoint\Models
 */
class Spreadsheet extends ModelBase
{
    /** @var null */
    public $columnsToHide = null;

    /** @var null */
    private $castColumnToType = null;

    /** @var null */
    public $columnTitles = null;

    /** @var null */
    private $columnsToContainEnergyUnits = null;

    /** @var null */
    private $row = null;

    /** @var null */
    private $filename = null;

    /** @var null */
    private $columnFormattingRules = null;

    const OPERATING_EXPENSES                    = 'opex';
    const VARIANCE                              = 'variance';
    const YEAR_OVER_YEAR                        = 'yoy';
    const PEER_AVERAGE                          = 'peer';
    const EXPENSE_FORMATTING_RULES              = 1;
    const RANKING_FORMATTING_RULES              = 2;
    const METADATA_FORMATTING_RULES             = 3;
    const NATIVE_COA_FORMATTING_RULES           = 4;
    const NATIVE_COA_OCCUPANCY_FORMATTING_RULES = 5;
    const HEADER_ROW                            = 1;
    const EXPENSE                               = 'expense';
    const RANKING                               = 'ranking';

    function __construct($ApiObj = null)
    {
        $this->filename = 'Waypoint Spreadsheet - ' . date('Y-m-d_H:i:s'); // set default filename
        // if spreadsheet transformations config data is pulled in only through the usual API response path
        isset($ApiObj->spreadsheetColumnsToHide) && $this->columnsToHide = $ApiObj->spreadsheetColumnsToHide;
        isset($ApiObj->spreadsheetColumnTitles) && $this->columnTitles = $ApiObj->spreadsheetColumnTitles;
        isset($ApiObj->spreadsheetColumnsToContainEnergyUnits) && $this->columnsToContainEnergyUnits = $ApiObj->spreadsheetColumnsToContainEnergyUnits;
        isset($ApiObj->spreadsheetColumnFormattingRules) && $this->columnFormattingRules = $ApiObj->spreadsheetColumnFormattingRules;

        parent::__construct();
    }

    /**
     * Get the collection of items as a plain array.
     *
     * NOTE - Only scalar values of $item->toArray() are inserted
     *
     * See http://www.maatwebsite.nl/laravel-excel/docs
     *
     * @param $filename
     * @param bool $headingGeneration
     */
    public function toCSVReport($filename = null, $headingGeneration = true)
    {
        ! empty($filename) && $this->filename = $filename;
        //$items = $this->items;
        /** @var LaravelExcelWriter $LaravelExcelWriterObj */
        $LaravelExcelWriterObj =
            Excel::create(
                $this->filename,
                function (LaravelExcelWriter $excel) use ($headingGeneration)
                {
                    $excel->sheet(
                        'CSVReport',
                        function (LaravelExcelWorksheet $sheet) use ($headingGeneration)
                        {
                            $sheet->fromArray($this->toArray(), null, 'A1', false, $headingGeneration);
                        }
                    );
                }
            );
        $LaravelExcelWriterObj->download('xls');
    }

    public function setEcmProjectSpreadsheetFormat()
    {
        /** @var null|array */
        $this->columnsToHide = [
            'id',
            'property_id',
            'model_name',
            'description',
            'estimated_start_date',
            'energy_units',
            'project_summary',
            'comments',
            'created_at',
            'updated_at',
        ];

        /** @var array */
        $this->columnTitles = [
            'name'                            => 'Name',
            'property_name'                   => 'Property',
            'description'                     => 'Description',
            'project_status'                  => 'Status',
            'project_category'                => 'Category',
            'costs'                           => 'Costs',
            'estimated_incentive'             => 'Est. Incentive',
            'estimated_annual_savings'        => 'Est. Annual Savings',
            'estimated_annual_energy_savings' => 'Est. Annual Energy Savings',
            'energy_units'                    => 'Energy Units',
            'estimated_start_date'            => 'Est. Start Date',
            'estimated_completion_date'       => 'Est. Completion Date',
            'project_summary'                 => 'ECM Project Summary',
        ];

        /** @var array */
        $this->columnsToContainEnergyUnits = [
            'estimated_annual_energy_savings',
        ];

        $this->castColumnToType = [
            'estimated_completion_date' => 'date',
        ];

        $this->columnFormattingRules = [
            'E' => '"$"#,##0.00',
            'F' => '"$"#,##0.00',
            'G' => '"$"#,##0.00',
        ];

        $this->filename = 'ECMProjects - ' . date('Y-m-d_H:i:s');
    }

    /**
     * @param $filename
     * @param bool $headingGeneration
     */
    public function createECMProjectSpreadsheet($result, $headingGeneration = true)
    {
        try
        {
            $this->setEcmProjectSpreadsheetFormat();
            $formatted_data_array = [];
            /** @noinspection PhpUnusedLocalVariableInspection */
            foreach ($result as $key => $this->row)
            {
                $this
                    ->addEnergyFieldUnits()
                    ->castColumnData()
                    ->hideColumns()
                    ->updateColumnTitles();
                $formatted_data_array = array_merge($formatted_data_array, $this->row);
            }

            /** @var LaravelExcelWriter $LaravelExcelWriterObj */
            $LaravelExcelWriterObj = Excel::create(
                $this->filename,
                function (LaravelExcelWriter $excel) use ($formatted_data_array, $headingGeneration)
                {
                    $excel->sheet(
                        'ECM Projects',
                        function (LaravelExcelWorksheet $sheet) use ($formatted_data_array, $headingGeneration)
                        {
                            // TODO : Alex to add back in the column and data formatting
                            $sheet->fromArray($formatted_data_array, null, 'A1', false, $headingGeneration);
                        }
                    );
                }
            );
            $LaravelExcelWriterObj->download('xls');
        }
        catch (Exception $e)
        {
            echo $e->getMessage() . ' - ' . $e->getLine();
        }
    }

    /**
     * @param AdvancedVariance $AdvancedVarianceObj
     * @param Property $PropertyObj
     * @param Client $ClientObj
     * @return array
     */
    static function createAdvancedVarianceSpreadsheet(AdvancedVariance $AdvancedVarianceObj, Property $PropertyObj, Client $ClientObj)
    {
        try
        {
            $report_metadata = self::packageAdvancedVarianceReportMetadata($AdvancedVarianceObj, $PropertyObj, $ClientObj);
            $filename        = $PropertyObj->display_name . ' - Variance Report Details - ' . date('Y-m-d_H_i_s');

            /** @var LaravelExcelWriter $LaravelExcelWriterObj */
            $LaravelExcelWriterObj = Excel::create(
                $filename,
                function (LaravelExcelWriter $excel) use ($AdvancedVarianceObj, $report_metadata)
                {

                    $excel->sheet(
                        'Budget Variance Report',
                        function (LaravelExcelWorksheet $sheet) use ($AdvancedVarianceObj)
                        {
                            $packagedAdvancedVarianceLineItems = self::packageAdvancedVarianceLineItemData($AdvancedVarianceObj);

                            $sheet->setColumnFormat(
                                [
                                    'C' => '$#,##0.00',
                                    'D' => '$#,##0.00',
                                    'E' => '$#,##0.00',
                                    'F' => '0.00"%"',
                                ]
                            );

                            for ($i = 0; $i <= count($packagedAdvancedVarianceLineItems) - 1; $i++)
                            {
                                $accountCode = $packagedAdvancedVarianceLineItems[$i]['Account Code'];

                                if (empty($accountCode))
                                {
                                    continue;
                                }

                                $AdvancedVarianceLineItemRepositoryObj = App::make(AdvancedVarianceLineItemRepository::class);

                                $AdvancedVarianceLineItemObj =
                                    $AdvancedVarianceLineItemRepositoryObj
                                        ->findWhere(
                                            [
                                                'line_item_code'       => $accountCode,
                                                'advanced_variance_id' => $AdvancedVarianceObj->id,
                                                ['report_template_account_group_id', '!=', null],
                                            ]
                                        )
                                        ->first();

                                if (
                                    $AdvancedVarianceLineItemObj
                                    ||
                                    $accountCode == '--'
                                )
                                {
                                    $sheet->row($i + 2, function ($row)
                                    {
                                        $row->setFontWeight('bold');
                                    });
                                }
                            }

                            $sheet->fromArray($packagedAdvancedVarianceLineItems);
                        }
                    );
                    $excel->sheet(
                        'Report Details',
                        function (LaravelExcelWorksheet $sheet) use ($report_metadata)
                        {
                            $sheet->fromArray([$report_metadata]);
                        }
                    );
                    $excel->setActiveSheetIndex(0);
                }
            );
            return [
                'excel_as_string' => base64_encode($LaravelExcelWriterObj->string()),
                'filename'        => $filename,
            ];
        }
        catch (Exception $e)
        {
            throw new GeneralException('unsuccesful spreadsheet generation | URL: ' . Request::getRequestUri() . ' | ' . $e->getMessage());
        }
    }

    /**
     * @param AdvancedVariance $AdvancedVarianceObj
     * @param Property $PropertyObj
     * @param Client $ClientObj
     * @return array
     * @throws GeneralException
     */
    static function packageAdvancedVarianceReportMetadata(AdvancedVariance $AdvancedVarianceObj, Property $PropertyObj, Client $ClientObj): array
    {
        try
        {
            return [
                'Property Name' => $PropertyObj->display_name,
                'Time Period'   => date('M Y', strtotime($AdvancedVarianceObj->advanced_variance_start_date)),
                'As of Date'    => LedgerController::getClientAsOfDate($ClientObj)->format('d-M-Y'),
            ];
        }
        catch (Exception $e)
        {
            throw new GeneralException($e->getMessage());
        }
    }

    /**
     * @param AdvancedVariance $AdvancedVarianceObj
     * @return array
     * @throws GeneralException
     */
    static function packageAdvancedVarianceLineItemData(AdvancedVariance $AdvancedVarianceObj): array
    {
        try
        {
            $advancedVarianceLineItemsSorted = $AdvancedVarianceObj->advancedVarianceLineItemsSorted();

            $hasExplanationTypes = (bool) $advancedVarianceLineItemsSorted->first(function ($item)
            {
                return ! is_null($item->advanced_variance_explanation_type_id);
            });

            /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
            foreach ($advancedVarianceLineItemsSorted as $AdvancedVarianceLineItemObj)
            {
                if ($AdvancedVarianceLineItemObj->nativeAccount)
                {
                    $advancedVariancePayloadSlice = [
                        'Account Code'     => $AdvancedVarianceLineItemObj->nativeAccount->native_account_code,
                        'Account Name'     => $AdvancedVarianceLineItemObj->nativeAccount->native_account_name,
                        'YTD Actual ($)'   => $AdvancedVarianceLineItemObj->ytd_actual,
                        'YTD Budget ($)'   => $AdvancedVarianceLineItemObj->ytd_budgeted,
                        'YTD Variance ($)' => $AdvancedVarianceLineItemObj->ytd_variance,
                        'YTD Variance (%)' => $AdvancedVarianceLineItemObj->ytd_percent_variance,
                        'YTD Explanation'  => $AdvancedVarianceLineItemObj->explanation,
                    ];
                }
                elseif ($AdvancedVarianceLineItemObj->reportTemplateAccountGroup)
                {
                    if ($AdvancedVarianceLineItemObj->reportTemplateAccountGroup->is_category)
                    {
                        $advancedVariancePayloadSlice = [
                            'Account Code'     => '--',
                            'Account Name'     => $AdvancedVarianceLineItemObj->reportTemplateAccountGroup->report_template_account_group_name,
                            'YTD Actual ($)'   => $AdvancedVarianceLineItemObj->total_ytd_actual,
                            'YTD Budget ($)'   => $AdvancedVarianceLineItemObj->total_ytd_budgeted,
                            'YTD Variance ($)' => $AdvancedVarianceLineItemObj->total_ytd_variance,
                            'YTD Variance (%)' => $AdvancedVarianceLineItemObj->total_ytd_percent_variance,
                            'YTD Explanation'  => $AdvancedVarianceLineItemObj->explanation,
                        ];
                    }
                    else
                    {
                        $advancedVariancePayloadSlice = [
                            'Account Code'     => $AdvancedVarianceLineItemObj->reportTemplateAccountGroup->report_template_account_group_code,
                            'Account Name'     => $AdvancedVarianceLineItemObj->reportTemplateAccountGroup->report_template_account_group_name,
                            'YTD Actual ($)'   => $AdvancedVarianceLineItemObj->total_ytd_actual,
                            'YTD Budget ($)'   => $AdvancedVarianceLineItemObj->total_ytd_budgeted,
                            'YTD Variance ($)' => $AdvancedVarianceLineItemObj->total_ytd_variance,
                            'YTD Variance (%)' => $AdvancedVarianceLineItemObj->total_ytd_percent_variance,
                            'YTD Explanation'  => $AdvancedVarianceLineItemObj->explanation,
                        ];
                    }
                }
                elseif ($AdvancedVarianceLineItemObj->calculatedField)
                {
                    $advancedVariancePayloadSlice = [
                        'Account Code'     => '--',
                        'Account Name'     => $AdvancedVarianceLineItemObj->calculatedField->name,
                        'YTD Actual ($)'   => $AdvancedVarianceLineItemObj->total_ytd_actual,
                        'YTD Budget ($)'   => $AdvancedVarianceLineItemObj->total_ytd_budgeted,
                        'YTD Variance ($)' => $AdvancedVarianceLineItemObj->total_ytd_variance,
                        'YTD Variance (%)' => $AdvancedVarianceLineItemObj->total_ytd_percent_variance,
                        'YTD Explanation'  => $AdvancedVarianceLineItemObj->explanation,
                    ];
                }

                if ($hasExplanationTypes)
                {
                    $explanationType = null;
                    if ( ! is_null($AdvancedVarianceLineItemObj->advanced_variance_explanation_type_id))
                    {
                        $explanationType = AdvancedVarianceExplanationType::find($AdvancedVarianceLineItemObj->advanced_variance_explanation_type_id)->name;
                    }
                    $advancedVariancePayloadSlice['YTD Explanation Type'] = $explanationType;
                }
                $payload[] = $advancedVariancePayloadSlice;
            }
            return isset($payload) ? $payload : [];
        }
        catch (Exception $e)
        {
            throw new GeneralException($e->getMessage());
        }
    }

    /**
     * @param $expenseResults
     * @param $rankingResults
     */
    public function createCombinedLedgerSpreadsheet($expenseResults, $rankingResults)
    {
        try
        {
            $this->setTransformations($expenseResults['transformations'], self::EXPENSE);

            foreach ($expenseResults['data'] as $key => $this->row)
            {
                $expenseResults['data'][$key] = $this->hideColumns()->updateColumnTitles()->row;
            }

            $this->setTransformations($rankingResults['transformations'], self::RANKING);

            foreach ($this->standardizeRankNumbers($rankingResults['data']) as $key => $this->row)
            {
                $rankingResults['data'][$key] = $this
                    ->hideColumns()
                    ->updateColumnTitles()
                    ->row;
            }

            $rankingResults['data'] = $this->sortByRank($rankingResults['data']);

            $metadataFormatted = [
                'Entity Name'                   => $expenseResults['metadata']['entityName'],
                'Account Breakdown Graph Title' => $expenseResults['metadata']['apiDisplayName'], // can't be longer than 31 chars due to excel plugin limits
                'Ranking Graph Title'           => $rankingResults['metadata']['apiDisplayName'], // can't be longer than 31 chars due to excel plugin limits
                'Area'                          => $expenseResults['metadata']['area'],
                'Report'                        => $expenseResults['metadata']['report'],
                'Period'                        => $expenseResults['metadata']['period'],
                'Year'                          => $expenseResults['metadata']['targetYear'],
                'Expense'                       => $rankingResults['metadata']['expenseCodeName'],
            ];

            /** @var LaravelExcelWriter $LaravelExcelWriterObj */
            $LaravelExcelWriterObj = Excel::create(
                $metadataFormatted['Entity Name'] . ' - ' . $metadataFormatted['Account Breakdown Graph Title'] . ' Combined Account Breakdown & Ranking Data - ' . date('Y-m-d_H:i:s'),
                function (LaravelExcelWriter $excel) use ($expenseResults, $rankingResults, $metadataFormatted)
                {
                    $excel->sheet(
                        $metadataFormatted['Account Breakdown Graph Title'],
                        function (LaravelExcelWorksheet $sheet) use ($expenseResults)
                        {
                            if (isset($this->columnFormattingRules[self::EXPENSE][self::EXPENSE_FORMATTING_RULES]))
                            {
                                foreach ($this->columnFormattingRules[self::EXPENSE][self::EXPENSE_FORMATTING_RULES] as $column => $rule)
                                {
                                    $sheet->setColumnFormat([$column => $rule]);
                                }
                            }
                            $sheet->fromArray($expenseResults['data'], null, 'A1', false, true);
                        }
                    );
                    $excel->sheet(
                        $metadataFormatted['Ranking Graph Title'],
                        function (LaravelExcelWorksheet $sheet) use ($rankingResults)
                        {
                            if (isset($this->columnFormattingRules[self::RANKING][self::RANKING_FORMATTING_RULES]))
                            {
                                foreach ($this->columnFormattingRules[self::RANKING][self::RANKING_FORMATTING_RULES] as $column => $rule)
                                {
                                    $sheet->setColumnFormat([$column => $rule]);
                                }
                            }
                            $sheet->fromArray($rankingResults['data'], null, 'A1', false, true);
                        }
                    );
                    $excel->sheet(
                        'Entity Details',
                        function (LaravelExcelWorksheet $sheet) use ($metadataFormatted)
                        {
                            if (isset($this->columnFormattingRules[self::METADATA_FORMATTING_RULES]))
                            {
                                foreach ($this->columnFormattingRules[self::METADATA_FORMATTING_RULES] as $column => $rule)
                                {
                                    $sheet->setColumnFormat([$column => $rule]);
                                }
                            }
                            unset($metadataFormatted['Account Breakdown Graph Title'], $metadataFormatted['Ranking Graph Title']);
                            $sheet->fromArray([$metadataFormatted], null, 'A1', false, true);
                        }
                    );
                    $excel->setActiveSheetIndex(0);
                }
            );
            $LaravelExcelWriterObj->download('xls');
        }
        catch (Exception $e)
        {
            echo $e->getMessage() . ' - ' . $e->getLine();
        }
    }

    /**
     * @param $operatingExpensesResults
     * @param $varianceResults
     * @param $yearOverYearResults
     * @param $peerAverageResults
     */
    public function createCombinedOverviewSpreadsheet($operatingExpensesResults, $varianceResults, $yearOverYearResults)
    {
        try
        {
            $this->filename = $operatingExpensesResults['metadata']['entityName'] . ' - Combined Overview - ' . date('Y-m-d_H:i:s');

            $this->setTransformations($operatingExpensesResults['transformations'], self::OPERATING_EXPENSES);
            foreach ($operatingExpensesResults['data'] as $key => $this->row)
            {
                $operatingExpensesResults['data'][$key] = $this->hideColumns()->updateColumnTitles()->row;
            }

            $this->setTransformations($varianceResults['transformations'], self::VARIANCE);
            foreach ($varianceResults['data'] as $key => $this->row)
            {
                $varianceResults['data'][$key] = $this->hideColumns()->updateColumnTitles()->row;
            }

            $this->setTransformations($yearOverYearResults['transformations'], self::YEAR_OVER_YEAR);
            foreach ($yearOverYearResults['data'] as $key => $this->row)
            {
                $yearOverYearResults['data'][$key] = $this->hideColumns()->updateColumnTitles()->row;
            }

            $metadataFormatted = [
                'Entity Name' => $operatingExpensesResults['metadata']['entityName'],
                'Area'        => $operatingExpensesResults['metadata']['area'],
                'Report'      => $operatingExpensesResults['metadata']['report'],
                'Period'      => $operatingExpensesResults['metadata']['period'],
                'Year'        => $operatingExpensesResults['metadata']['targetYear'],
                'Expense'     => 'Account Breakdown',
            ];

            /** @var LaravelExcelWriter $LaravelExcelWriterObj */
            $LaravelExcelWriterObj = Excel::create(
                $this->filename,
                function (LaravelExcelWriter $excel) use ($operatingExpensesResults, $varianceResults, $yearOverYearResults, $metadataFormatted)
                {
                    $excel->sheet(
                        'Account Breakdown Graph Data',
                        function (LaravelExcelWorksheet $sheet) use ($operatingExpensesResults)
                        {
                            if (isset($this->columnFormattingRules[self::OPERATING_EXPENSES][self::EXPENSE_FORMATTING_RULES]))
                            {
                                foreach ($this->columnFormattingRules[self::OPERATING_EXPENSES][self::EXPENSE_FORMATTING_RULES] as $column => $rule)
                                {
                                    $sheet->setColumnFormat([$column => $rule]);
                                }
                            }
                            $sheet->row(
                                self::HEADER_ROW, function ($row)
                            {

                            }
                            );
                            $sheet->fromArray($operatingExpensesResults['data'], null, 'A1', false, true);
                        }
                    );
                    $excel->sheet(
                        'Budget Variance Graph Data',
                        function (LaravelExcelWorksheet $sheet) use ($varianceResults)
                        {
                            if (isset($this->columnFormattingRules[self::VARIANCE][self::EXPENSE_FORMATTING_RULES]))
                            {
                                foreach ($this->columnFormattingRules[self::VARIANCE][self::EXPENSE_FORMATTING_RULES] as $column => $rule)
                                {
                                    $sheet->setColumnFormat([$column => $rule]);
                                }
                            }
                            $sheet->fromArray($varianceResults['data'], null, 'A1', false, true);
                        }
                    );
                    $excel->sheet(
                        'Year Over Year Graph Data',
                        function (LaravelExcelWorksheet $sheet) use ($yearOverYearResults)
                        {
                            if (isset($this->columnFormattingRules[self::YEAR_OVER_YEAR][self::EXPENSE_FORMATTING_RULES]))
                            {
                                foreach ($this->columnFormattingRules[self::YEAR_OVER_YEAR][self::EXPENSE_FORMATTING_RULES] as $column => $rule)
                                {
                                    $sheet->setColumnFormat([$column => $rule]);
                                }
                            }
                            $sheet->fromArray($yearOverYearResults['data'], null, 'A1', false, true);
                        }
                    );
                    $excel->sheet(
                        'Entity Details',
                        function (LaravelExcelWorksheet $sheet) use ($metadataFormatted)
                        {
                            if (isset($this->columnFormattingRules[self::METADATA_FORMATTING_RULES]))
                            {
                                foreach ($this->columnFormattingRules[self::METADATA_FORMATTING_RULES] as $column => $rule)
                                {
                                    $sheet->setColumnFormat([$column => $rule]);
                                }
                            }
                            $sheet->fromArray([$metadataFormatted], null, 'A1', false, true);
                        }
                    );
                    $excel->setActiveSheetIndex(0);
                }
            );
            $LaravelExcelWriterObj->download('xls');
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
        }
    }

    /**
     * @param $result
     * @param $metadata
     * @param bool $headingGeneration
     */
    public function createNativeChartOfAccountSpreadsheet($result, $metadata, $headingGeneration = true)
    {
        try
        {
            $this->filename = $metadata['entityName'] . ' - ' . $metadata['apiDisplayName'] . ' - ' . date('Y-m-d_H:i:s');

            $metadata_formatted_array = [
                'Entity Name'            => $metadata['entityName'],
                'As of Date'             => $metadata['as_of_date'],
                'Report Run Date / Time' => $metadata['run_date'],
            ];

            /** @var LaravelExcelWriter $LaravelExcelWriterObj */
            $LaravelExcelWriterObj = Excel::create(
                $this->filename,
                function (LaravelExcelWriter $excel) use ($result, $metadata_formatted_array, $headingGeneration)
                {
                    $excel->sheet(
                        'Actuals',
                        function (LaravelExcelWorksheet $sheet) use ($result, $headingGeneration)
                        {
                            if (isset($this->columnFormattingRules[self::NATIVE_COA_FORMATTING_RULES]))
                            {
                                foreach ($this->columnFormattingRules[self::NATIVE_COA_FORMATTING_RULES] as $column => $rule)
                                {
                                    $sheet->setColumnFormat([$column => $rule]);
                                }
                            }
                            $sheet->fromArray($result['actual'], null, 'A1', false, $headingGeneration);
                        }
                    );
                    $excel->sheet(
                        'Budget',
                        function (LaravelExcelWorksheet $sheet) use ($result, $headingGeneration)
                        {
                            if (isset($this->columnFormattingRules[self::NATIVE_COA_FORMATTING_RULES]))
                            {
                                foreach ($this->columnFormattingRules[self::NATIVE_COA_FORMATTING_RULES] as $column => $rule)
                                {
                                    $sheet->setColumnFormat([$column => $rule]);
                                }
                            }
                            $sheet->fromArray($result['budget'], null, 'A1', false, $headingGeneration);
                        }
                    );
                    $excel->sheet(
                        'Occupancy',
                        function (LaravelExcelWorksheet $sheet) use ($result, $headingGeneration)
                        {
                            if (isset($this->columnFormattingRules[self::NATIVE_COA_OCCUPANCY_FORMATTING_RULES]))
                            {
                                foreach ($this->columnFormattingRules[self::NATIVE_COA_OCCUPANCY_FORMATTING_RULES] as $column => $rule)
                                {
                                    $sheet->setColumnFormat([$column => $rule]);
                                }
                            }
                            $sheet->fromArray($result['occupancy'], null, 'A1', false, $headingGeneration);
                        }
                    );
                    $excel->sheet(
                        'Details',
                        function (LaravelExcelWorksheet $sheet) use ($metadata_formatted_array, $headingGeneration)
                        {
                            if (isset($this->columnFormattingRules[self::METADATA_FORMATTING_RULES]))
                            {
                                foreach ($this->columnFormattingRules[self::METADATA_FORMATTING_RULES] as $column => $rule)
                                {
                                    $sheet->setColumnFormat([$column => $rule]);
                                }
                            }
                            $sheet->fromArray([$metadata_formatted_array], null, 'A1', false, $headingGeneration);
                        }
                    );
                    $excel->setActiveSheetIndex(0);
                }
            );
            $LaravelExcelWriterObj->download('xls');
        }
        catch (Exception $e)
        {
            echo $e->getMessage() . ' - ' . $e->getLine();
        }
    }

    /**
     * @param $result
     * @param array $metadata
     * @param bool $headingGeneration
     */
    public function createLedgerSpreadsheet($result, $metadata = [], $headingGeneration = true)
    {
        try
        {
            $this->filename = $metadata['entityName'] . ' - ' . $metadata['apiDisplayName'] . ' - ' . date('Y-m-d_H:i:s');

            if ($this->isRankingData($metadata['apiTitle']))
            {
                $result = $this->standardizeRankNumbers($result);
            }

            foreach ($result as $key => $this->row)
            {
                $result[$key] = $this->hideColumns()->updateColumnTitles()->row;
            }

            if ($this->isRankingData($metadata['apiTitle']))
            {
                $result = $this->sortByRank($result);
            }

            $metadataFormatted = [
                'Entity Name'  => $metadata['entityName'],
                'Graph Title'  => $metadata['apiDisplayName'], // can't be longer than 31 chars due to excel plugin limits
                'Area'         => $metadata['area'],
                'Report'       => $metadata['report'],
                'Period'       => $metadata['period'],
                'Year'         => $metadata['targetYear'],
                'Date Updated' => date('M Y', strtotime($metadata['client_as_of_date'])),
            ];

            if ($this->isRankingData($metadata['apiTitle']))
            {
                $metadataFormatted = ['Expense' => $metadata['expenseCodeName']] + $metadataFormatted;
            }

            /** @var LaravelExcelWriter $LaravelExcelWriterObj */
            $LaravelExcelWriterObj = Excel::create(
                $this->filename,
                function (LaravelExcelWriter $excel) use ($result, $metadataFormatted, $headingGeneration)
                {
                    $formattingRulesFlag = $this->isRankingData($metadataFormatted['Graph Title']) ? self::RANKING_FORMATTING_RULES : self::EXPENSE_FORMATTING_RULES;
                    $excel->sheet(
                        $metadataFormatted['Graph Title'],
                        function (LaravelExcelWorksheet $sheet) use ($result, $metadataFormatted, $formattingRulesFlag, $headingGeneration)
                        {
                            if (isset($this->columnFormattingRules[$formattingRulesFlag]))
                            {
                                foreach ($this->columnFormattingRules[$formattingRulesFlag] as $column => $rule)
                                {
                                    $sheet->setColumnFormat([$column => $rule]);
                                }
                            }
                            $sheet->fromArray($result, null, 'A1', false, $headingGeneration);
                        }
                    );
                    $excel->sheet(
                        'Entity Details',
                        function (LaravelExcelWorksheet $sheet) use ($metadataFormatted, $headingGeneration)
                        {
                            if (isset($this->columnFormattingRules[self::METADATA_FORMATTING_RULES]))
                            {
                                foreach ($this->columnFormattingRules[self::METADATA_FORMATTING_RULES] as $column => $rule)
                                {
                                    $sheet->setColumnFormat([$column => $rule]);
                                }
                            }
                            unset($metadataFormatted['Graph Title']);
                            $sheet->fromArray([$metadataFormatted], null, 'A1', false, $headingGeneration);
                        }
                    );
                    $excel->setActiveSheetIndex(0);
                }
            );
            $LaravelExcelWriterObj->download('xls');
        }
        catch (Exception $e)
        {
            echo $e->getMessage() . ' - ' . $e->getLine();
        }
    }

    private function isRankingData($item)
    {
        return stripos($item, 'ranking') !== false;
    }

    /**
     * @param $transformations
     * @param $ledgerTypeName
     * @return $this
     */
    private function setTransformations($transformations, $ledgerTypeName = false)
    {
        foreach ($transformations as $key => $value)
        {
            $array = array_filter(
                get_object_vars($this),
                function ($k) use ($key)
                {
                    return stripos($k, preg_replace('/^spreadsheet/', '', $key)) !== false;
                },
                ARRAY_FILTER_USE_KEY
            );

            if ( ! empty($ledgerTypeName) && key($array) == 'columnFormattingRules')
            {
                ! empty($array) && $this->{key($array)}[$ledgerTypeName] = $value;
            }
            else
            {
                ! empty($array) && $this->{key($array)} = $value;
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function castColumnData()
    {
        foreach ($this->castColumnToType as $field => $type)
        {
            if ($this->isEcmProjectData())
            {
                foreach ($this->row as $key => $project)
                {
                    if (key_exists($field, $project))
                    {
                        if ($type == 'date')
                        {
                            $project[$field] = date('m/d/Y', strtotime($project[$field]));
                        }
                    }
                    $this->row[$key] = $project;
                }
            }
            else
            {
                if (key_exists($field, $this->row))
                {
                    if ($type == 'date')
                    {
                        $this->row[$field] = date('m/d/Y', strtotime($this->row[$field]));
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @return $this
     */
    private function addEnergyFieldUnits()
    {
        foreach ($this->columnsToContainEnergyUnits as $field)
        {
            if ($this->isEcmProjectData())
            {
                foreach ($this->row as $key => $project)
                {
                    if (key_exists($field, $project) && isset($project['energy_units']))
                    {
                        $project[$field] = ! empty($project[$field]) ? number_format($project[$field]) . ' ' . $project['energy_units'] : 'N/A';
                        $this->row[$key] = $project;
                    }
                }
            }
            else
            {
                if (key_exists($field, $this->row) && isset($this->row['energy_units']))
                {
                    $this->row[$field] = ! empty($this->row[$field]) ? number_format($this->row[$field]) . ' ' . $this->row['energy_units'] : 'N/A';
                }
            }
        }
        return $this;
    }

    /**
     * @return bool
     */
    private function isEcmProjectData(): bool
    {
        return (bool) stristr(key($this->row), 'ecmproject');
    }

    /**
     * @return $this
     */
    private function hideColumns()
    {
        // remove unnecessary keys
        if ($this->columnsToHide)
        {
            // if processing ecmproject data
            if ($this->isEcmProjectData())
            {
                foreach ($this->row as $key => $project)
                {
                    $project         = array_filter(
                        $project,
                        function ($key)
                        {
                            return ! in_array($key, $this->columnsToHide);
                        },
                        ARRAY_FILTER_USE_KEY
                    );
                    $this->row[$key] = $project;
                }
                return $this;
            }

            $this->row = array_filter(
                (array) $this->row,
                function ($key)
                {
                    return ! in_array($key, $this->columnsToHide);
                },
                ARRAY_FILTER_USE_KEY
            );
        }
        return $this;
    }

    /**
     * @return $this
     */
    private function updateColumnTitles()
    {
        // change column titles
        foreach ($this->columnTitles as $oldTitle => $newTitle)
        {
            // if processing ecmproject data
            if ($this->isEcmProjectData())
            {
                foreach ($this->row as $key => $project)
                {
                    if (key_exists($oldTitle, $project))
                    {
                        $project[$newTitle] = $project[$oldTitle];
                        unset($project[$oldTitle]);
                        $this->row[$key] = $project;
                    }
                }
            }
            else
            {
                if (key_exists($oldTitle, $this->row))
                {
                    $this->row[$newTitle] = $this->row[$oldTitle];
                    unset($this->row[$oldTitle]);
                }
            }
        }
        return $this;
    }

    /**
     * @param $results
     * @return array
     */
    protected function standardizeRankNumbers($results)
    {
        $rank            = 1;
        $resultsReRanked = [];
        $resultsSorted   = collect($results)->sortByDesc('rank');
        foreach ($resultsSorted as $key => &$value)
        {
            if ( ! empty($value['rank']))
            {
                $value['rank'] = $rank++;
            }
            $resultsReRanked[$key] = $value;
        }
        return $resultsReRanked;
    }

    /**
     * @param $results
     * @return array
     */
    protected function sortByRank($results)
    {
        $resultsIncompletes   = [];
        $resultsNoIncompletes = [];
        $rankKey              = isset($this->columnTitles['rank']) ? $this->columnTitles['rank'] : 'rank';

        foreach ($results as $key => $item)
        {
            if ( ! empty($item[$rankKey]))
            {
                $resultsNoIncompletes[$key] = $item;
            }
            else
            {
                $item[$rankKey]           = 'N/A';
                $resultsIncompletes[$key] = $item;
            }
        }

        $resultsSortedNoIncompletes = collect($resultsNoIncompletes)->sortBy(
            function ($item) use ($rankKey)
            {
                return $item[$rankKey];
            }
        );

        return $resultsSortedNoIncompletes->toArray() + $resultsIncompletes;
    }

    /**
     * @return array
     *
     * Config format:
     *      [
     *          'raw-field-name': [
     *              'header_name': 'Column Header Name',
     *              'cell_function: function() { do work on the specific cell data }
     *          ],
     *          ...
     *      ]
     */
    public static function getUserManagementConfig(): array
    {
        return [
            'email'                  => [
                'header_name' => 'Email',
            ],
            'firstname'              => [
                'header_name' => 'First Name',
            ],
            'lastname'               => [
                'header_name' => 'Last Name',
            ],
            'highest_role'           => [
                'header_name'   => 'Role',
                'cell_function' => function ($string)
                {
                    return str_replace('Client', '', $string);
                },
            ],
            'access_list_names_arr'  => [
                'header_name'   => 'Access Lists',
                'cell_function' => function ($array)
                {
                    return ! empty($array) ? implode(', ', $array) : '';
                },
            ],
            'active_status'          => [
                'header_name'   => 'Status',
                'cell_function' => function ($string)
                {
                    return title_case($string);
                },
            ],
            'user_invitation_status' => [
                'header_name'   => 'Invitation Status',
                'cell_function' => function ($string)
                {
                    return snakeToCapitalCase($string);
                },
            ],
            'created_at'             => [
                'header_name' => 'Date Created',
            ],
            'first_login_date'       => [
                'header_name' => 'First Login',
            ],
            'last_login_date'        => [
                'header_name' => 'Last Login',
            ],
        ];
    }

    /**
     * @param array $users_arr
     * @param string $filename
     */
    public static function downloadUserManagementSpreadsheet($users_arr, string $filename)
    {
        $user_arr_formatted = self::formatSpreadsheetData($users_arr, self::getUserManagementConfig());

        /** @var LaravelExcelWriter $LaravelExcelWriterObj */
        $LaravelExcelWriterObj = Excel::create(
            $filename,
            function (LaravelExcelWriter $excel) use ($user_arr_formatted)
            {
                $excel->sheet(
                    'Users',
                    function (LaravelExcelWorksheet $sheet) use ($user_arr_formatted)
                    {
                        $sheet->fromArray($user_arr_formatted);
                    }
                );
            }
        );
        $LaravelExcelWriterObj->download('xls');
    }

    /**
     * @param $spreadsheet_data_arr
     * @param $spreadsheet_config
     * @return array
     */
    public static function formatSpreadsheetData($spreadsheet_data_arr, $spreadsheet_config): array
    {
        foreach ($spreadsheet_data_arr as $row_index => $row_data_arr)
        {
            $spreadsheet_data_arr[$row_index] = self::formatRow($row_data_arr, $spreadsheet_config);
        }

        return $spreadsheet_data_arr;
    }

    /**
     * @param $data_array
     * @param $spreadsheet_config_obj
     * @return array|false
     */
    static function formatRow($data_array, $spreadsheet_config_obj)
    {
        $data_array = self::enforce_whitelist($data_array, $spreadsheet_config_obj);
        $data_array = self::reorder($data_array, $spreadsheet_config_obj);

        foreach ($spreadsheet_config_obj as $key => $value)
        {
            if (is_array($value))
            {
                if (isset($value['cell_function']))
                {
                    // update cell value using function
                    $data_array[$key] = $value['cell_function']($data_array[$key]);
                }
                if (isset($value['header_name']))
                {
                    // update header name
                    $data_array = self::replace_key_and_preserve_order($data_array, $key, $value['header_name']);
                }
            }
        }

        return $data_array;
    }

    /**
     * @param $data_array
     * @param $white_list
     * @return array
     */
    static function enforce_whitelist($data_array, $white_list)
    {
        return array_intersect_key($data_array, array_flip(array_keys($white_list)));
    }

    /**
     * @param $data_array
     * @param $ordered_list
     * @return array
     */
    static function reorder($data_array, $ordered_list)
    {
        return array_replace(array_flip(array_keys($ordered_list)), $data_array);
    }

    /**
     * @param $array
     * @param $old_key
     * @param $new_key
     * @return array|false
     */
    static function replace_key_and_preserve_order($array, $old_key, $new_key)
    {
        $keys = array_keys($array);
        if (false === $index = array_search($old_key, $keys))
        {
            throw new GeneralException(sprintf('Key "%s" does not exit', $old_key));
        }
        $keys[$index] = $new_key;
        return array_combine($keys, array_values($array));
    }
}
