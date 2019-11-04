<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Http\Controllers\Api\Ledger\LedgerController;
use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccountAmount;
use App\Waypoint\Models\NativeAccountTypeDetail;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Repositories\NativeChartAmountRepository;
use Carbon\Carbon;
use \FormulaInterpreter\Compiler as FormulaInterpreterCompiler;
use Illuminate\Support\Facades\Validator;

/**
 * Class NativeChartAmountController
 */
class NativeChartAmountController extends BaseApiController
{

    /** @var  $NativeChartAmountRepositoryObj */
    private $NativeChartAmountRepositoryObj;

    const LATEST_MONTH      = 'LM';
    const CALENDAR_YEAR     = 'CY';
    const CUSTOM_DATE_RANGE = 'CDR';

    public function __construct(NativeChartAmountRepository $NativeChartAmountRepositoryObj)
    {
        $this->NativeChartAmountRepositoryObj = $NativeChartAmountRepositoryObj;
        parent::__construct($NativeChartAmountRepositoryObj);
    }

    /**
     * This method parses the date ranges input from native analytics
     * It supports the following new parameters combination to create date ranges:
     *
     * # Year to Date | Custom Date Range: fromYear, fromMonth, toYear, toMonth
     * # Latest Month: fromYear, fromMonth
     * # Calendar Year: year
     *
     * All those new parameters must be inside the $input array. All the possible combinations
     * are triggered dynamically depending if the parameter is set or not.
     *
     * @param integer $client_id
     * @param array $input
     * @return array
     * @throws GeneralException if the years or months are invalid or the 'from date 'are greater than the 'to date'
     * also if there's no data for the requested dates
     */
    function processInputForNativeChartAmountController($client_id, $input)
    {
        //Input Validations
        $ValidatorObj = Validator::make(
            $input,
            [
                'fromYear'  => ['sometimes', 'required', 'numeric', 'regex:/(?:(?:19|20)[0-9]{2})/'],
                'toYear'    => ['sometimes', 'required', 'numeric', 'regex:/(?:(?:19|20)[0-9]{2})/'],
                'fromMonth' => 'sometimes|required|digits_between:1,12',
                'toMonth'   => 'sometimes|required|digits_between:1,12',
                'year'      => ['sometimes', 'required', 'numeric', 'regex:/(?:(?:19|20)[0-9]{2})/'],
            ]
        );
        if ($ValidatorObj->fails())
        {
            $Errors = $ValidatorObj->errors();
            if ($Errors->has('fromYear') || $Errors->has('toYear'))
            {
                throw new GeneralException('Invalid fromYear/toYear parameter ', 400);
            }
            if ($Errors->has('fromMonth') || $Errors->has('toMonth'))
            {
                throw new GeneralException('Invalid fromMonth/toMonth parameter ', 400);
            }
            if ($Errors->has('year'))
            {
                throw new GeneralException('Invalid year parameter ', 400);
            }
        }

        if ( ! $BenchmarkGenerationDateObj = LedgerController::getClientAsOfDate(Client::find($client_id)))
        {
            $BenchmarkGenerationDateObj = Carbon::now();
        }
        //Custom Date Range || Year to Date
        if (isset($input['fromYear'], $input['fromMonth'], $input['toYear'], $input['toMonth']))
        {
            $input['period']      = self::CUSTOM_DATE_RANGE;
            $RequestedFromDateObj = Carbon::createFromDate($input['fromYear'], $input['fromMonth']);
            $RequestedFromDateObj->startOfMonth();
            $RequestedToDateObj = Carbon::createFromDate($input['toYear'], $input['toMonth']);
            $RequestedToDateObj->endOfMonth();
        }
        //Latest Month
        elseif (isset($input['fromYear'], $input['fromMonth']) && ! isset($input['toMonth'], $input['toYear']))
        {
            $input['period']      = self::LATEST_MONTH;
            $RequestedFromDateObj = Carbon::create($input['fromYear'], $input['fromMonth']);
            $RequestedFromDateObj->startOfMonth();
            $RequestedToDateObj = clone $RequestedFromDateObj;
            $RequestedToDateObj->endOfMonth();
        }
        //Calendar Year
        elseif (isset($input['year']))
        {
            $input['period']      = self::CALENDAR_YEAR;
            $RequestedFromDateObj = Carbon::create($input['year'], 1, 1);
            $RequestedToDateObj   = Carbon::create($input['year'], 12, 31);
        }
        else
        {
            throw new GeneralException('Invalid year/period combo', 400);
        }

        /** @var Carbon $RequestedFromDateObj */
        if ($RequestedFromDateObj->greaterThan($BenchmarkGenerationDateObj))
        {
            throw new GeneralException(
                'RequestedFromDate ' . $RequestedFromDateObj->format('Y-m-d H:i:s') . ' is after as_of_date ' . $BenchmarkGenerationDateObj->format('Y-m-d H:i:s')
            );
        }
        /** @var Carbon $RequestedFromDateObj */
        if ($RequestedFromDateObj->greaterThan($RequestedToDateObj))
        {
            throw new GeneralException(
                'RequestedToDate ' . $RequestedFromDateObj->format('Y-m-d H:i:s') . ' is after as_of_date ' . $RequestedToDateObj->format('Y-m-d H:i:s')
            );
        }

        $RequestedFromDateObj->setTime(0, 0, 0);
        $RequestedToDateObj->endOfMonth();
        return [$input, $RequestedFromDateObj, $RequestedToDateObj, $BenchmarkGenerationDateObj];
    }

    /**
     * @param string $data_type
     * @param array $input
     * @param Carbon|null $ClientAsOfDate
     * @param Property|null $PropertyObj
     * @param PropertyGroup|null $PropertyGroupObj
     * @param ReportTemplateAccountGroup|null $ReportTemplateAccountGroupObj
     * @param Carbon|null $RequestedFromDateObj
     * @param Carbon|null $RequestedToDateObj
     * @return mixed
     */
    function generateNativeChartMetadata(
        $data_type,
        $input = [],
        Carbon $ClientAsOfDate = null,
        $property_id = null,
        $property_group_id = null,
        ReportTemplateAccountGroup $ReportTemplateAccountGroupObj = null,
        CalculatedField $CalculatedFieldObj = null,
        Carbon $RequestedFromDateObj = null,
        Carbon $RequestedToDateObj = null
    ) {
        $metadata_arr['query'] = $input;

        $metadata_arr['data_type']         = $data_type;
        $metadata_arr['client_as_of_date'] = $ClientAsOfDate ? $ClientAsOfDate->format('Y-m-d H:i:s') : null;

        $metadata_arr['property_id']       = $property_id ? $property_id : null;
        $metadata_arr['property_group_id'] = $property_group_id ? $property_group_id : null;

        $metadata_arr['target_report_template_account_group_id']   = null;
        $metadata_arr['target_report_template_account_group_name'] = null;
        $metadata_arr['target_calculated_field_id']                = null;
        $metadata_arr['target_calculated_field_name']              = null;
        $metadata_arr['native_account_id']                         = null;
        $metadata_arr['native_account_name']                       = null;
        $metadata_arr['calculated_field_id']                       = null;
        $metadata_arr['calculated_field_name']                     = null;

        $metadata_arr['parent__id']             = null;
        $metadata_arr['parent_name']            = null;
        $metadata_arr['grandparent_id']         = null;
        $metadata_arr['grandparent_name']       = null;
        $metadata_arr['native_account_type_id'] = null;

        if ($ReportTemplateAccountGroupObj)
        {
            $metadata_arr['target_report_template_account_group_id']   = $ReportTemplateAccountGroupObj->id;
            $metadata_arr['target_report_template_account_group_name'] = $ReportTemplateAccountGroupObj->name;
            $metadata_arr['target_id']                                 = $ReportTemplateAccountGroupObj->id;
            $metadata_arr['target_name']                               = $ReportTemplateAccountGroupObj->name;

            $metadata_arr['parent__id']  =
                $ReportTemplateAccountGroupObj &&
                $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent
                    ? $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->id
                    : null;
            $metadata_arr['parent_name'] =
                $ReportTemplateAccountGroupObj &&
                $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent
                    ? $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->report_template_account_group_name
                    : null;

            $metadata_arr['grandparent_id']   =
                $ReportTemplateAccountGroupObj &&
                $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent &&
                $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->reportTemplateAccountGroupParent
                    ? $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->reportTemplateAccountGroupParent->id
                    : null;
            $metadata_arr['grandparent_name'] =
                $ReportTemplateAccountGroupObj &&
                $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent &&
                $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->reportTemplateAccountGroupParent
                    ? $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->reportTemplateAccountGroupParent->report_template_account_group_name
                    : null;

            $metadata_arr['native_account_type_id'] = $ReportTemplateAccountGroupObj->native_account_type_id;

            $metadata_arr['nativeAccountTypeDetail'] =
                $ReportTemplateAccountGroupObj->native_account_type_id
                    ? NativeAccountTypeDetail::findOrFail($ReportTemplateAccountGroupObj->native_account_type_id)
                    : [];
        }
        elseif ($CalculatedFieldObj)
        {
            $metadata_arr['calculated_field_id']   = $CalculatedFieldObj->id;
            $metadata_arr['calculated_field_name'] = $CalculatedFieldObj->name;
        }

        $metadata_arr['from_date'] = $RequestedFromDateObj ? $RequestedFromDateObj->format('Y-m-d H:i:s') : null;
        $metadata_arr['to_date']   = $RequestedToDateObj ? $RequestedToDateObj->format('Y-m-d H:i:s') : null;

        return $metadata_arr;
    }

    /**
     * @param $PropertyObjArr
     * @param $CalculatedFieldObj
     * @param Carbon|null $FromDateObj
     * @param Carbon|null $ToDateObj
     * @return mixed
     */
    public function processTotalsForPropertyArrCalculatedField(
        $PropertyObjArr,
        array $property_id_arr,
        $CalculatedFieldObj,
        Carbon $FromDateObj = null,
        Carbon $ToDateObj = null
    ) {
        /** @var  $FormulaInterpreterCompiler */
        $FormulaInterpreterCompiler = new FormulaInterpreterCompiler();

        $property_return_me = $PropertyObjArr
            ->map(
                function (Property $PropertyObj) use ($CalculatedFieldObj, $FromDateObj, $ToDateObj, $FormulaInterpreterCompiler)
                {
                    if ( ! $CalculatedFieldEquationObj = $CalculatedFieldObj->calculatedFieldEquationsForProperty($PropertyObj->id))
                    {
                        /**
                         * this grabs the client default
                         */
                        $CalculatedFieldEquationObj =
                            $CalculatedFieldObj->calculatedFieldEquations
                                ->filter(
                                    function ($CalculatedFieldEquationObj)
                                    {
                                        return $CalculatedFieldEquationObj->properties->count() == 0;
                                    }
                                )->first();
                    }

                    $FormulaInterpreterExecutable = $FormulaInterpreterCompiler->compile($CalculatedFieldEquationObj->equation_string_parsed);

                    $actual_variable_element_arr = [];
                    $budget_variable_element_arr = [];
                    foreach ($CalculatedFieldEquationObj->calculatedFieldVariables as $CalculatedFieldVariableObj)
                    {
                        if ($CalculatedFieldVariableObj->native_account_id)
                        {
                            $native_account_id_arr[] = $CalculatedFieldVariableObj->native_account_id;
                            /** @var Collection $LocalNativeAcountAmountObjArr */
                            $LocalNativeAcountAmountObjArr = $PropertyObj->nativeAccountAmountsFiltered->filter(
                                function (NativeAccountAmount $NativeAccountAmountObj) use ($native_account_id_arr, $FromDateObj, $ToDateObj)
                                {
                                    return
                                        in_array($NativeAccountAmountObj->native_account_id, $native_account_id_arr) &&
                                        $NativeAccountAmountObj->month_year_timestamp->greaterThanOrEqualTo($FromDateObj) &&
                                        $NativeAccountAmountObj->month_year_timestamp->lessThanOrEqualTo($ToDateObj);
                                }
                            );

                            $actual_variable_element_arr['NA_' . $CalculatedFieldVariableObj->native_account_id] = $LocalNativeAcountAmountObjArr->sum('actual');
                            $budget_variable_element_arr['NA_' . $CalculatedFieldVariableObj->native_account_id] = $LocalNativeAcountAmountObjArr->sum('budget');
                        }
                        elseif ($CalculatedFieldVariableObj->report_template_account_group_id)
                        {
                            $native_account_id_arr         = $CalculatedFieldVariableObj->reportTemplateAccountGroup->get_native_account_id_arr();
                            $LocalNativeAcountAmountObjArr =
                                $PropertyObj
                                    ->nativeAccountAmountsFiltered
                                    ->filter(
                                        function (NativeAccountAmount $NativeAccountAmountsFilteredObj) use ($FromDateObj, $ToDateObj, $native_account_id_arr)
                                        {
                                            return in_array($NativeAccountAmountsFilteredObj->native_account_id, $native_account_id_arr) &&
                                                   $NativeAccountAmountsFilteredObj->month_year_timestamp->greaterThanOrEqualTo($FromDateObj) &&
                                                   $NativeAccountAmountsFilteredObj->month_year_timestamp->lessThanOrEqualTo($ToDateObj);
                                        }
                                    );

                            $actual_variable_element_arr['RTAG_' . $CalculatedFieldVariableObj->report_template_account_group_id] =
                                $LocalNativeAcountAmountObjArr->sum('actual');

                            $budget_variable_element_arr['RTAG_' . $CalculatedFieldVariableObj->report_template_account_group_id] =
                                $LocalNativeAcountAmountObjArr->sum('budget');
                        }
                        else
                        {
                            throw new GeneralException('Invalid equation in ' . self::class . $CalculatedFieldEquationObj->equation_string . ' ' . __FILE__ . ':' . __LINE__);
                        }
                    }

                    $local_return_me = [
                        'actual' => $FormulaInterpreterExecutable->run($actual_variable_element_arr),
                        'budget' => $FormulaInterpreterExecutable->run($budget_variable_element_arr),
                    ];

                    return $local_return_me;
                }
            )
            ->toArray();

        $return_me['budget'] = null;
        $return_me['actual'] = null;
        foreach ($property_return_me as $total_pair)
        {
            if (is_numeric($total_pair['budget']))
            {
                $return_me['budget'] += $total_pair['budget'];
            }
            if (is_numeric($total_pair['actual']))
            {
                $return_me['actual'] += $total_pair['actual'];
            }
        }

        return [$return_me['budget'], $return_me['actual']];
    }

}
