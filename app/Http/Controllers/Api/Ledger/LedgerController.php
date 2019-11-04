<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App;
use App\Waypoint\Model;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\User;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Repositories\DatabaseConnectionRepository;
use App\Waypoint\Repositories\ReportTemplateRepository;
use App\Waypoint\Repositories\Ledger\LedgerRepository;
use App\Waypoint\Http\ApiController;
use App\Waypoint\Repositories\UserRepository;
use Auth;
use Carbon\Carbon;
use DB;
use App\Waypoint\Repositories\ReportTemplateAccountGroupRepository;
use Exception;
use function in_array;
use App\Waypoint\Exceptions\LedgerException;
use App\Waypoint\Models\Ledger\Metadata;
use App\Waypoint\Collection;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use Route;
use function strtolower;
use App\Waypoint\Traits\BenchmarkingHelperTrait;

/** @noinspection PhpInconsistentReturnPointsInspection */

/**
 * Class LedgerController
 * @package Controllers\Ledger
 */
class LedgerController extends ApiController
{
    use BenchmarkingHelperTrait;

    /** @var  LedgerRepository */
    private $LedgerRepositoryObj;

    /** @var null|Client */
    public $ClientObj = null;

    /** @var null|\App\Waypoint\Models\User */
    public $UserObj = null;

    /** @var null|App\Waypoint\Models\Property */
    public $PropertyObj = null;

    /** @var null|App\Waypoint\Models\PropertyGroup */
    public $PropertyGroupObj = null;

    public $PropertyGroupRepositoryObj = null;

    /** @var array */
    public $queryLog = [];

    /** @var  null|\Carbon\Carbon */
    private $client_as_of_date = null;

    /** @var string */
    public $rankingChartDisplayName = 'Property Ranking';

    /** @var array */
    public $warnings = [];

    /** @var array */
    public $errors = [];

    /** @var string|null */
    public $apiTitle = null;

    /** @var string|null */
    public $apiDisplayName = null;

    /** @var string|null */
    public $entityName = null;

    /** @var string|null */
    public $entityDisplayName = null;

    /** @var string|null */
    public $unitsDisplayText = null;

    /** @var null|Collection */
    public $payload = null;

    protected $query_result = null;

    protected $request_params_arr = [];

    /** @var array */
    public $units = [
        'expense' => [
            'prefix' => '$',
            'suffix' => '/sq ft',
        ],
        'change'  => [
            'prefix' => '',
            'suffix' => '%',
        ],
    ];

    /** @var null */
    public $titleBarUnits = null;

    // SPREADSHEET CONFIG
    // - these can be overwritten in the specific ledger api controllers
    // - collected in the Spreadsheet model when processing a spreadsheet response

    /** @var null|array */
    public $spreadsheetVisibleColumns = null;

    /** @var null|array */
    public $spreadsheetColumnsToHide = [
        'id',
        'property_id',
        'code',
        'childCount',
        'totalBarUnits',
        'targetYear',
        'targetYearAmount',
        'entityOccupancy',
        'occupancy',
        'entityName',
        'unitsDisplayText',
        'previousYear',
        'monthly_data',
        'previousYearAmount',
        'targetYearOccupancy',
        'previousYearOccupancy',
        'entityType',
        'targetAmount',
        'peerAvgAmount',
        'peerAvgOccupancy',
        'report_template_account_group_id',
        'units',
        'area',
        'rentable_area',
        'areaType',
        'native_account_type_id',
        'native_account_type_coefficient',
        'squareFootageTargetYear',
        'squareFootagePreviousYear',
        'peerAvgGrossAmount',
        'targetArea',
        'peerAvgArea',
    ];

    /** @var array */
    public $spreadsheetColumnTitles = [
        'name'             => 'Expense Name',
        'amount'           => 'Expense Amount ($/sq ft)',
        'entityName'       => 'Entity Name',
        'targetYearAmount' => 'Target Year Amount',
        'targetYear'       => 'Target Year',
        'rank'             => 'Rank',
        'grossAmount'      => 'Gross Amount',
    ];

    // end spreadsheet config

    /** @var null|array */
    public $targetResult = null;

    /** @var null|array */
    public $targetPayloadSlice = null;

    /** @var null|ReportTemplateAccountGroup */
    public $ReportTemplateAccountGroupObj = null;

    public $report_template_account_group_id = null;

    public $property_id = null;

    public $property_group_id = null;

    /** @var int|null */
    public $year = null;

    /** @var int|null */
    public $targetYear = null;

    /** @var int|null */
    public $previousYear = null;

    /** @var null|string */
    public $period = null;

    /** @var null|string */
    public $area = null;

    /** @var null|string */
    public $report = null;

    /** @var null|string */
    public $entityType = null;

    protected $user_id = null;

    /** @var null */
    public $targetYearOccupancy = null;

    /** @var null */
    public $previousYearOccupancy = null;

    /** @var null */
    public $occupancy = null;

    /** @var array */
    public $perPropertyOccupancy = [];

    /** @var null */
    public $propertyGroupAvgOccupancy = null;

    /** @var array */
    public $targetYearPerPropertyOccupancy = [];

    /** @var array */
    public $previousYearPerPropertyOccupancy = [];

    /** @var null|DB */
    public $DatabaseConnection = null;

    /** @var null */
    public $incompletePropertyCount;

    protected $ledger_table_name = null;

    /** @var null */
    public $status;

    /** @var null|string */
    protected $report_template_account_group_code_field_name = 'report_template_account_group_code';

    const OPERATING_EXPENSES_ROOT_CEILING_CODE             = 'root';
    const OPERATING_EXPENSES_CEILING_CODE                  = '40_h1';
    const OPERATING_EXPENSES_DEFAULT_CODE                  = '40_000_h2';
    const CALENDAR_YEAR_ABBREV                             = 'CY';
    const TRAILING_12_ABBREV                               = 'T12';
    const TRAILING_12_FIELD_ABBREV                         = '12';
    const YEAR_TO_DATE_ABBREV                              = 'YTD';
    const RENTABLE_ABBREV                                  = 'RNT';
    const OCCUPIED_ABBREV                                  = 'OCC';
    const ADJUSTED_ABBREV                                  = 'ADJ';
    const AMOUNT_RENTABLE_FIELD                            = 'AMOUNT_RNT';
    const AMOUNT_RENTABLE_DOUBLE_FIELD                     = 'AMOUNT_RNT_DOUBLE';
    const AMOUNT_OCCUPIED_FIELD                            = 'AMOUNT_OCC';
    const AMOUNT_OCCUPIED_DOUBLE_FIELD                     = 'AMOUNT_OCC_DOUBLE';
    const AMOUNT_ADJUSTED_FIELD                            = 'AMOUNT_ADJ';
    const AMOUNT_ADJUSTED_DOUBLE_FIELD                     = 'AMOUNT_ADJ_DOUBLE';
    const RANK_RENTABLE_FIELD                              = 'RANK_RNT';
    const RANK_OCCUPIED_FIELD                              = 'RANK_OCC';
    const RANK_ADJUSTED_FIELD                              = 'RANK_ADJ';
    const COLOR_RENTABLE_FIELD                             = 'COLOR_RNT';
    const COLOR_OCCUIPED_FIELD                             = 'COLOR_OCC';
    const RENTABLE_SELECTION                               = 'RENTABLE';
    const OCCUPIED_SELECTION                               = 'OCCUPIED';
    const ADJUSTED_SELECTION                               = 'ADJUSTED';
    const ACTUAL                                           = 'ACTUAL';
    const BUDGET                                           = 'BUDGET';
    const CALENDAR_YEAR_BENCHMARK_TYPE_ACTUAL              = 'ACTUAL';
    const CALENDAR_YEAR_BENCHMARK_TYPE_BUDGET              = 'BUDGET';
    const CALENDAR_YEAR_BENCHMARK_TYPE_VARIANCE            = 'ACTUAL VS BUDGET';
    const YEAR_TO_DATE_BENCHMARK_TYPE_ACTUAL               = 'ACTUAL_YTD';
    const YEAR_TO_DATE_BENCHMARK_TYPE_BUDGET               = 'BUDGET_YTD';
    const YEAR_TO_DATE_BENCHMARK_TYPE_VARIANCE             = 'ACTUAL_YTD VS BUDGET_YTD';
    const TRAILING_12_BENCHMARK_TYPE_BUDGET                = 'BUDGET_12';
    const TRAILING_12_BENCHMARK_TYPE_ACTUAL                = 'ACTUAL_12';
    const TRAILING_12_BENCHMARK_TYPE_VARIANCE              = 'ACTUAL_12 VS BUDGET_12';
    const BENCHMARK_AVERAGE_OCCUPIED_FIELD                 = 'BENCHMARK_AVG_OCC';
    const BENCHMARK_AVERAGE_RENTABLE_FIELD                 = 'BENCHMARK_AVG_RNT';
    const BENCHMARK_AVERAGE_ADJUSTED_FIELD                 = 'BENCHMARK_AVG_ADJ';
    const GROUP_AMOUNT_RENTABLE_FIELD                      = 'MEANVAL_RNT_DOUBLE';
    const GROUP_AMOUNT_OCCUPIED_FIELD                      = 'MEANVAL_OCC_DOUBLE';
    const GROUP_AMOUNT_ADJUSTED_FIELD                      = 'MEANVAL_ADJ_DOUBLE';
    const GROUP_AMOUNT_OCCUPIED_ACTUAL_CALENDAR_YEAR_FIELD = 'MEANVAL_OCC_DOUBLE_ACTUAL';
    const GROUP_AMOUNT_OCCUPIED_BUDGET_CALENDAR_YEAR_FIELD = 'MEANVAL_OCC_DOUBLE_BUDGET';
    const GROUP_AMOUNT_RENTABLE_ACTUAL_CALENDAR_YEAR_FIELD = 'MEANVAL_RNT_DOUBLE_ACTUAL';
    const GROUP_AMOUNT_RENTABLE_BUDGET_CALENDAR_YEAR_FIELD = 'MEANVAL_RNT_DOUBLE_BUDGET';
    const GROUP_AMOUNT_ADJUSTED_ACTUAL_CALENDAR_YEAR_FIELD = 'MEANVAL_ADJ_DOUBLE_ACTUAL';
    const GROUP_AMOUNT_ADJUSTED_BUDGET_CALENDAR_YEAR_FIELD = 'MEANVAL_ADJ_DOUBLE_BUDGET';
    const GROUP_AMOUNT_OCCUPIED_ACTUAL_YEAR_TO_DATE_FIELD  = 'MEANVAL_OCC_DOUBLE_ACTUAL_YTD';
    const GROUP_AMOUNT_OCCUPIED_BUDGET_YEAR_TO_DATE_FIELD  = 'MEANVAL_OCC_DOUBLE_BUDGET_YTD';
    const GROUP_AMOUNT_RENTABLE_ACTUAL_YEAR_TO_DATE_FIELD  = 'MEANVAL_RNT_DOUBLE_ACTUAL_YTD';
    const GROUP_AMOUNT_RENTABLE_BUDGET_YEAR_TO_DATE_FIELD  = 'MEANVAL_RNT_DOUBLE_BUDGET_YTD';
    const GROUP_AMOUNT_ADJUSTED_ACTUAL_YEAR_TO_DATE_FIELD  = 'MEANVAL_ADJ_DOUBLE_ACTUAL_YTD';
    const GROUP_AMOUNT_ADJUSTED_BUDGET_YEAR_TO_DATE_FIELD  = 'MEANVAL_ADJ_DOUBLE_BUDGET_YTD';
    const GROUP_AMOUNT_OCCUPIED_ACTUAL_TRAILING_12_FIELD   = 'MEANVAL_OCC_DOUBLE_ACTUAL_12';
    const GROUP_AMOUNT_OCCUPIED_BUDGET_TRAILING_12_FIELD   = 'MEANVAL_OCC_DOUBLE_BUDGET_12';
    const GROUP_AMOUNT_RENTABLE_ACTUAL_TRAILING_12_FIELD   = 'MEANVAL_RNT_DOUBLE_ACTUAL_12';
    const GROUP_AMOUNT_RENTABLE_BUDGET_TRAILING_12_FIELD   = 'MEANVAL_RNT_DOUBLE_BUDGET_12';
    const GROUP_AMOUNT_ADJUSTED_ACTUAL_TRAILING_12_FIELD   = 'MEANVAL_ADJ_DOUBLE_ACTUAL_12';
    const GROUP_AMOUNT_ADJUSTED_BUDGET_TRAILING_12_FIELD   = 'MEANVAL_ADJ_DOUBLE_BUDGET_12';
    const AMOUNT_RENTABLE_ACTUAL_CALENDAR_YEAR             = 'AMOUNT_RNT_ACTUAL';
    const AMOUNT_OCCUPIED_ACTUAL_CALENDAR_YEAR             = 'AMOUNT_OCC_ACTUAL';
    const AMOUNT_ADJUSTED_ACTUAL_CALENDAR_YEAR             = 'AMOUNT_ADJ_ACTUAL';
    const AMOUNT_RENTABLE_BUDGET_CALENDAR_YEAR             = 'AMOUNT_RNT_BUDGET';
    const AMOUNT_OCCUPIED_BUDGET_CALENDAR_YEAR             = 'AMOUNT_OCC_BUDGET';
    const AMOUNT_ADJUSTED_BUDGET_CALENDAR_YEAR             = 'AMOUNT_ADJ_BUDGET';
    const AMOUNT_RENTABLE_ACTUAL_YEAR_TO_DATE              = 'AMOUNT_RNT_ACTUAL_YTD';
    const AMOUNT_OCCUPIED_ACTUAL_YEAR_TO_DATE              = 'AMOUNT_OCC_ACTUAL_YTD';
    const AMOUNT_ADJUSTED_ACTUAL_YEAR_TO_DATE              = 'AMOUNT_ADJ_ACTUAL_YTD';
    const AMOUNT_RENTABLE_BUDGET_YEAR_TO_DATE              = 'AMOUNT_RNT_BUDGET_YTD';
    const AMOUNT_OCCUPIED_BUDGET_YEAR_TO_DATE              = 'AMOUNT_OCC_BUDGET_YTD';
    const AMOUNT_ADJUSTED_BUDGET_YEAR_TO_DATE              = 'AMOUNT_ADJ_BUDGET_YTD';
    const AMOUNT_RENTABLE_ACTUAL_TRAILING_12               = 'AMOUNT_RNT_ACTUAL_12';
    const AMOUNT_OCCUPIED_ACTUAL_TRAILING_12               = 'AMOUNT_OCC_ACTUAL_12';
    const AMOUNT_ADJUSTED_ACTUAL_TRAILING_12               = 'AMOUNT_ADJ_ACTUAL_12';
    const AMOUNT_RENTABLE_BUDGET_TRAILING_12               = 'AMOUNT_RNT_BUDGET_12';
    const AMOUNT_OCCUPIED_BUDGET_TRAILING_12               = 'AMOUNT_OCC_BUDGET_12';
    const AMOUNT_ADJUSTED_BUDGET_TRAILING_12               = 'AMOUNT_ADJ_BUDGET_12';
    const VARIANCE_RENTABLE_CALENDAR_YEAR                  = 'VARIANCE_RNT';
    const VARIANCE_OCCUPIED_CALENDAR_YEAR                  = 'VARIANCE_OCC';
    const VARIANCE_ADJUSTED_CALENDAR_YEAR                  = 'VARIANCE_ADJ';
    const VARIANCE_RENTABLE_YEAR_TO_DATE                   = 'VARIANCE_RNT_YTD';
    const VARIANCE_OCCUPIED_YEAR_TO_DATE                   = 'VARIANCE_OCC_YTD';
    const VARIANCE_ADJUSTED_YEAR_TO_DATE                   = 'VARIANCE_ADJ_YTD';
    const VARIANCE_RENTABLE_TRAILING_12                    = 'VARIANCE_RNT_12';
    const VARIANCE_OCCUPIED_TRAILING_12                    = 'VARIANCE_OCC_12';
    const VARIANCE_ADJUSTED_TRAILING_12                    = 'VARIANCE_ADJ_12';
    const GROUP_VARIANCE_CALENDAR_YEAR_RENTABLE            = 'GROUP_VARIANCE_RNT';
    const GROUP_VARIANCE_CALENDAR_YEAR_OCCUPIED            = 'GROUP_VARIANCE_OCC';
    const GROUP_VARIANCE_CALENDAR_YEAR_ADJUSTED            = 'GROUP_VARIANCE_ADJ';
    const GROUP_VARIANCE_TRAILING_12_RENTABLE              = 'GROUP_VARIANCE_RNT_12';
    const GROUP_VARIANCE_TRAILING_12_OCCUPIED              = 'GROUP_VARIANCE_OCC_12';
    const GROUP_VARIANCE_TRAILING_12_ADJUSTED              = 'GROUP_VARIANCE_ADJ_12';
    const GROUP_VARIANCE_YEAR_TO_DATE_RENTABLE             = 'GROUP_VARIANCE_RNT_YTD';
    const GROUP_VARIANCE_YEAR_TO_DATE_OCCUPIED             = 'GROUP_VARIANCE_OCC_YTD';
    const GROUP_VARIANCE_YEAR_TO_DATE_ADJUSTED             = 'GROUP_VARIANCE_ADJ_YTD';
    const RANK_RENTABLE_CALENDAR_YEAR_FIELD                = 'RANK_VARIANCE_RNT';
    const RANK_OCCUPIED_CALENDAR_YEAR_FIELD                = 'RANK_VARIANCE_OCC';
    const RANK_ADJUSTED_CALENDAR_YEAR_FIELD                = 'RANK_VARIANCE_ADJ';
    const RANK_RENTABLE_YEAR_TO_DATE_FIELD                 = 'RANK_VARIANCE_RNT_YTD';
    const RANK_OCCUPIED_YEAR_TO_DATE_FIELD                 = 'RANK_VARIANCE_OCC_YTD';
    const RANK_ADJUSTED_YEAR_TO_DATE_FIELD                 = 'RANK_VARIANCE_ADJ_YTD';
    const RANK_RENTABLE_TRAILING_12_FIELD                  = 'RANK_VARIANCE_RNT_12';
    const RANK_OCCUPIED_TRAILING_12_FIELD                  = 'RANK_VARIANCE_OCC_12';
    const RANK_ADJUSTED_TRAILING_12_FIELD                  = 'RANK_VARIANCE_ADJ_12';
    const COLOR_RENTABLE_CALENDAR_YEAR_FIELD               = 'COLOR_VARIANCE_RNT';
    const COLOR_OCCUPIED_CALENDAR_YEAR_FIELD               = 'COLOR_VARIANCE_OCC';
    const COLOR_ADJUSTED_CALENDAR_YEAR_FIELD               = 'COLOR_VARIANCE_ADJ';
    const COLOR_RENTABLE_YEAR_TO_DATE_FIELD                = 'COLOR_VARIANCE_RNT_YTD';
    const COLOR_OCCUPIED_YEAR_TO_DATE_FIELD                = 'COLOR_VARIANCE_OCC_YTD';
    const COLOR_ADJUSTED_YEAR_TO_DATE_FIELD                = 'COLOR_VARIANCE_ADJ_YTD';
    const COLOR_RENTABLE_TRAILING_12_FIELD                 = 'COLOR_VARIANCE_RNT_12';
    const COLOR_OCCUPIED_TRAILING_12_FIELD                 = 'COLOR_VARIANCE_OCC_12';
    const COLOR_ADJUSTED_TRAILING_12_FIELD                 = 'COLOR_VARIANCE_ADJ_12';
    const DATA_AVAILABILITY_TRAILING_12_FIELD              = 'TRAILING_12';
    const DATA_AVAILABILITY_CALENDAR_YEAR_FIELD            = 'CY';
    const DATA_AVAILABILITY_YEAR_TO_DATE_FIELD             = 'YTD';
    const HTTP_ERROR_RESPONSE_CODE                         = 403;
    const YEAR_OFFSET                                      = 1;
    const DEFAULT_PORTFOLIO_NAME                           = 'My Portfolio';
    const YEAR_OVER_YEAR                                   = 'yoy';
    const OPERATING_EXPENSES                               = 'opex';
    const VARIANCE                                         = 'variance';
    const PEER_AVERAGE                                     = 'peer';
    const EXPENSE_FORMATTING_RULES                         = 1;
    const RANKING_FORMATTING_RULES                         = 2;
    const METADATA_FORMATTING_RULES                        = 3;
    const NO_REPORT                                        = null;
    const STATUS_RENAMING                                  = 1;
    const STATUS_CALCULATING                               = 2;
    const STATUS_COMPLETE                                  = 3;
    const SPREADSHEET_DATE_FORMAT                          = 'F j, Y, g:i a';
    const NATIVE_ACCOUNT_TYPE_EXPENSES_TEXT                = 'EXPENSES';

    /** @var array $acceptablePeriods */
    const ACCEPTABLE_PERIODS = [
        self::TRAILING_12_ABBREV,
        self::CALENDAR_YEAR_ABBREV,
        self::YEAR_TO_DATE_ABBREV,
    ];

    const ACCEPTABLE_AREAS = [
        self::RENTABLE_SELECTION,
        self::RENTABLE_ABBREV,
        self::OCCUPIED_SELECTION,
        self::OCCUPIED_ABBREV,
        self::ADJUSTED_SELECTION,
        self::ADJUSTED_ABBREV,
    ];

    const ACCEPTABLE_AREAS_STRICT = [
        self::RENTABLE_SELECTION,
        self::OCCUPIED_SELECTION,
        self::ADJUSTED_SELECTION,
    ];

    const AREA_LOOKUP = [
        self::RENTABLE_SELECTION => self::RENTABLE_ABBREV,
        self::OCCUPIED_SELECTION => self::OCCUPIED_ABBREV,
        self::ADJUSTED_SELECTION => self::ADJUSTED_ABBREV,
    ];

    const PERIOD_LOOKUP = [
        self::CALENDAR_YEAR_ABBREV => self::CALENDAR_YEAR_ABBREV,
        self::TRAILING_12_ABBREV   => self::TRAILING_12_FIELD_ABBREV,
        self::YEAR_TO_DATE_ABBREV  => self::YEAR_TO_DATE_ABBREV,
    ];

    const ACCEPTABLE_REPORTS = [
        self::ACTUAL,
        self::BUDGET,
        self::NO_REPORT,
    ];

    const VARIANCE_BENCHMARK_TYPE_LOOKUP = [
        self::CALENDAR_YEAR_ABBREV => self::CALENDAR_YEAR_BENCHMARK_TYPE_VARIANCE,
        self::TRAILING_12_ABBREV   => self::TRAILING_12_BENCHMARK_TYPE_VARIANCE,
        self::YEAR_TO_DATE_ABBREV  => self::YEAR_TO_DATE_BENCHMARK_TYPE_VARIANCE,
    ];

    const ACCEPTABLE_GROUP_AMOUNT_FIELDS = [
        self::GROUP_AMOUNT_RENTABLE_FIELD,
        self::GROUP_AMOUNT_OCCUPIED_FIELD,
        self::GROUP_AMOUNT_ADJUSTED_FIELD,
    ];

    /** @var boolean */
    protected $controller_allow_caching = true;

    /** @var null|ReportTemplate */
    protected $BomaReportTemplateObj = null;

    /** @var null|ReportTemplate */
    protected $ReportTemplateObj = null;

    /** @var null|ReportTemplateRepository */
    protected $ReportTemplateRepositoryObj = null;

    /** @var null|ReportTemplateAccountGroupRepository */
    protected $ReportTemplateAccountGroupRepositoryObj = null;

    /**
     * LedgerController constructor.
     * @param LedgerRepository $LedgerRepositoryObj
     */
    public function __construct(LedgerRepository $LedgerRepositoryObj)
    {
        $this->LedgerRepositoryObj                     = $LedgerRepositoryObj;
        $this->ReportTemplateRepositoryObj             = App::make(ReportTemplateRepository::class);
        $this->ReportTemplateAccountGroupRepositoryObj = App::make(ReportTemplateAccountGroupRepository::class);
        $this->payload                                 = new Collection();
        $this->query_result                            = new \Illuminate\Support\Collection();
        $LedgerRepositoryObj->LedgerControllerObj      = $this;

        if (Route::current())
        {
            // set parameters
            $this->setRequestParams($LedgerRepositoryObj);
        }

        parent::__construct($LedgerRepositoryObj);
    }

    /**
     * @throws GeneralException
     * @throws LedgerException
     */
    protected function initializeClientObject()
    {
        $this->ClientObj = $this->LedgerRepositoryObj->ClientObj = $this->getClientObject();

        if (empty($this->ClientObj->client_id_old))
        {
            throw new LedgerException('cannot find this client in our benchmark data', self::HTTP_ERROR_RESPONSE_CODE);
        }
    }

    /**
     * @throws LedgerException
     */
    protected function isDefaultAnalyticsReportTemplate()
    {
        $this->ReportTemplateObj = $this->ClientObj->getDefaultAnalyticsReportTemplate();

        if (isset($this->ReportTemplateAccountGroupObj))
        {
            if ($this->ReportTemplateObj->id != $this->ReportTemplateAccountGroupObj->reportTemplate->id)
            {
                throw new LedgerException('the report_template_account_group requested does not match the default template', 404);
            }
        }
    }

    /**
     * @param $deprecated_code
     * @return mixed
     */
    protected function getNewCodeFromDeprecatedCode($deprecated_code)
    {
        return $this->ReportTemplateObj
            ->reportTemplateAccountGroups
            ->where('deprecated_waypoint_code', $deprecated_code)
            ->first()
            ->report_template_account_group_code;
    }

    /**
     * @param $deprecated_code
     * @return mixed
     */
    protected function getReportTemplateAccountGroupIdFromDeprecatedCode($deprecated_code)
    {
        return $this->ReportTemplateObj
            ->reportTemplateAccountGroups
            ->where('deprecated_waypoint_code', $deprecated_code)
            ->first()
            ->id;
    }

    /**
     * @param LedgerRepository $LedgerRepositoryObj
     * @throws LedgerException
     * - also sanitize input data
     */
    protected function setRequestParams(LedgerRepository $LedgerRepositoryObj)
    {
        $this->request_params_arr = filter_var_array(Route::current()->parameters, FILTER_SANITIZE_STRING, true);
        $this->year               = $LedgerRepositoryObj->year = isset($this->request_params_arr['year']) ? $this->request_params_arr['year'] : null;
        $this->period             = $LedgerRepositoryObj->period = isset($this->request_params_arr['period']) ? $this->request_params_arr['period'] : null;
        $this->report             = $LedgerRepositoryObj->report = isset($this->request_params_arr['report']) ? $this->request_params_arr['report'] : null;
        $this->area               = $LedgerRepositoryObj->area = isset($this->request_params_arr['area']) ? $this->request_params_arr['area'] : null;

        if (
            isset($this->request_params_arr['property_id'])
            && ! empty($this->request_params_arr['property_id'])
        )
        {
            $this->property_id = $this->request_params_arr['property_id'];

            if ( ! $this->PropertyObj = Property::find($this->property_id))
            {
                throw new LedgerException('cannot find this property from the id provided', self::HTTP_ERROR_RESPONSE_CODE);
            }
        }
        elseif (
            isset($this->request_params_arr['property_group_id'])
            &&
            ! empty($this->request_params_arr['property_group_id'])
        )
        {
            $this->property_group_id = $this->request_params_arr['property_group_id'];

            if ( ! $this->PropertyGroupObj = PropertyGroup::find($this->property_group_id))
            {
                throw new LedgerException('cannot find this property from the id provided', self::HTTP_ERROR_RESPONSE_CODE);
            }
        }
        elseif (
            isset($this->request_params_arr['ledgerDataType'])
            &&
            $this->request_params_arr['ledgerDataType'] == 'property'
            &&
            isset($this->request_params_arr['id'])
        )
        {
            $this->property_id = $this->request_params_arr['id'];

            if ( ! $this->PropertyObj = Property::find($this->property_id))
            {
                throw new LedgerException('cannot find this property from the id provided', self::HTTP_ERROR_RESPONSE_CODE);
            }
        }
        elseif (
            isset($this->request_params_arr['ledgerDataType'])
            &&
            $this->request_params_arr['ledgerDataType'] == 'group'
            &&
            isset($this->request_params_arr['id'])
        )
        {
            $this->property_group_id = $this->request_params_arr['id'];

            if ( ! $this->PropertyGroupObj = PropertyGroup::find($this->property_group_id))
            {
                throw new LedgerException('cannot find this property from the id provided', self::HTTP_ERROR_RESPONSE_CODE);
            }
        }
        else
        {
            throw new LedgerException('could not gather entity id from request input, please check your request', 404);
        }

        if (isset($this->request_params_arr['report_template_account_group_id']))
        {
            $this->report_template_account_group_id = isset($this->request_params_arr['report_template_account_group_id'])
                ?
                $this->request_params_arr['report_template_account_group_id']
                :
                null;
            $this->ReportTemplateAccountGroupObj    = $LedgerRepositoryObj->ReportTemplateAccountGroupObj = $this->getReportTemplateAccountGroup($this->report_template_account_group_id);
        }
    }

    /**
     * @param Client $Client
     * @param $group_calc_data_table
     * @param bool $include_status
     * @return array|bool|string
     * @throws GeneralException
     */
    public function getCorrectTableBasedOnAvailabilityStatus(Client $Client, $group_calc_data_table, $include_status = false)
    {
        $group_calc_status_table = 'GROUP_CALC_CLIENT_' . $Client->client_id_old . '_YEARLY_STATUS';
        // get group calc status

        $status = $this->LedgerRepositoryObj->getGroupDatabaseConnection()
                                            ->table($group_calc_status_table)
                                            ->where('STEP_DESCRIPTION', $group_calc_data_table)
                                            ->value('STATUS');

        // if there is no data to access (group status:renaming)
        if ($status == self::STATUS_RENAMING)
        {
            return false;
        }
        elseif ($status == self::STATUS_CALCULATING)
        {
            return $include_status ? [$group_calc_data_table . '_OLD', $status] : $group_calc_data_table . '_OLD';
        }
        else
        {
            return $include_status ? [$group_calc_data_table, $status] : $group_calc_data_table;
        }
    }

    /**
     * @param Client $Client
     * @param $peer_data_table
     * @param bool $include_status
     * @return array|bool|string
     * @throws GeneralException
     */
    public function getCorrectPeerTableBasedOnAvailabilityStatus(Client $Client, $peer_data_table, $include_status = false)
    {
        $peer_group_calc_status_table = 'PEER_GROUP_CALC_CLIENT_' . $Client->client_id_old . '_YEARLY_STATUS';
        $this->status                 = $this->LedgerRepositoryObj->getPeerDatabaseConnection()
                                                                  ->table($peer_group_calc_status_table)
                                                                  ->where('STEP_DESCRIPTION', $peer_data_table)
                                                                  ->value('STATUS');

        if ($this->status == self::STATUS_RENAMING)
        {
            return false;
        }
        elseif ($this->status == self::STATUS_CALCULATING)
        {
            return $include_status ? [$peer_data_table . '_OLD', $this->status] : $peer_data_table . '_OLD';
        }
        else
        {
            return $include_status ? [$peer_data_table, $this->status] : $peer_data_table;
        }
    }

    /**
     * @param $results
     * @return int
     */
    public function checkVarianceChildrenForNullResults($results)
    {
        $resultsCollection = collect($results);
        $resultsCount      = 0;
        foreach ($resultsCollection->pluck('code')->unique() as $code)
        {
            $resultsWithSameCode = $resultsCollection->filter(function ($item) use ($code)
            {
                return $item->code == $code && ! is_null($item->amount);
            });

            if ($resultsWithSameCode->count() == 2)
            {
                $resultsCount++;
            }
        }
        return $resultsCount;
    }

    /**
     * @param $results
     * @return int
     */
    public function checkYoyChildrenForNullResults($results)
    {
        $resultsCollection  = collect($results);
        $usableResultsCount = 0;
        foreach ($resultsCollection->pluck('code')->unique() as $code)
        {
            $resultsFiltered = $resultsCollection->filter(function ($item) use ($code)
            {
                return $item->code == $code && $item->code != $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                                      true)->deprecated_waypoint_code;
            });
            if ($resultsFiltered->count() == 2)
            {
                $usableResultsCount++;
            }
        }
        return $usableResultsCount;
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws GeneralException
     * @throws \Exception
     */
    public function callAction($method, $parameters)
    {
        /**
         * NOTE NOTE NOTE
         * Since Lavarel 5.3, the logged-in-user is not available in the controller constructor
         * thus we grab it here and save it to the controller for convenience
         */
        if ( ! $CurrentLoggedInUserObj = $this->getCurrentLoggedInUserObj())
        {
            /** @var  UserRepository $UserRepositoryObj */
            $UserRepositoryObj = App::make(UserRepository::class);
            if ($CurrentLoggedInUserObj = $UserRepositoryObj->getLoggedInUser())
            {
                $this->setCurrentLoggedInUserObj($CurrentLoggedInUserObj);
            }
            else
            {
                throw new GeneralException('Auth issue, please make sure you are logged in');
            }
        }

        /**
         * this is needed to filter on $UserObj->is_hidden.
         */
        Model::$requesting_user_role = $CurrentLoggedInUserObj->getHighestRole();

        /**
         * check for old client id
         */
        if (empty($this->getCurrentLoggedInUserObj()->client->client_id_old))
        {
            throw new GeneralException('Old client id missing', self::HTTP_ERROR_RESPONSE_CODE);
        }
        $this->client_as_of_date = $this->getClientObject()->get_client_asof_date();

        return parent::callAction($method, $parameters);
    }

    /**
     * @return Client|mixed
     * @throws GeneralException
     */
    public function getClientObject()
    {
        if ( ! empty($this->getCurrentLoggedInUserObj()))
        {
            return $this->getCurrentLoggedInUserObj()->client;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        if ( ! $ClientObj = Client::find(Auth::user()->client_id))
        {
            throw new LedgerException('Client id invalid', self::HTTP_ERROR_RESPONSE_CODE);
        }

        return $ClientObj;

    }

    /**
     * @return User|null
     * @throws GeneralException
     */
    public function getUserObject()
    {
        if ( ! empty($this->UserObj))
        {
            return $this->UserObj;
        }

        if ( ! empty($this->getCurrentLoggedInUserObj()))
        {
            $this->UserObj = $this->getCurrentLoggedInUserObj();
            return $this->getCurrentLoggedInUserObj();
        }

        /** @noinspection PhpUndefinedFieldInspection */
        if ( ! $this->UserObj = User::find(Auth::user()->id))
        {
            throw new GeneralException('Cannot find user', self::HTTP_ERROR_RESPONSE_CODE);
        }

        return $this->UserObj;
    }

    /**
     * @param $area
     * @return string
     * @throws GeneralException
     */
    public function getPeerAvgRankField($area)
    {
        switch ($area)
        {
            case 'RENTABLE':
                return 'RANK_TARGET_PEER_AMOUNT_RNT_DOUBLE';
                break;
            case 'OCCUPIED':
                return 'RANK_TARGET_PEER_AMOUNT_OCC_DOUBLE';
                break;
            case 'ADJUSTED':
                return 'RANK_TARGET_PEER_AMOUNT_ADJ_DOUBLE';
                break;
            default:
                throw new GeneralException('Area not defined correctly');
        }
    }

    /**
     * @param int $area
     * @return array
     * @throws GeneralException
     */
    public function getPeerAvgAmountFields($area)
    {
        switch ($area)
        {
            case 'RENTABLE':
                return [
                    'DIFF_TARGET_PEER_AMOUNT_RNT_DOUBLE',
                    'TARGET_AMOUNT_RNT_DOUBLE',
                    'PEER_MEANVAL_RNT_DOUBLE',
                ];
                break;
            case 'OCCUPIED':
                return [
                    'DIFF_TARGET_PEER_AMOUNT_OCC_DOUBLE',
                    'TARGET_AMOUNT_OCC_DOUBLE',
                    'PEER_MEANVAL_OCC_DOUBLE',
                ];
                break;
            case 'ADJUSTED':
                return [
                    'DIFF_TARGET_PEER_AMOUNT_ADJ_DOUBLE',
                    'TARGET_AMOUNT_ADJ_DOUBLE',
                    'PEER_MEANVAL_ADJ_DOUBLE',
                ];
                break;
            default:
                throw new GeneralException('Area not defined correctly');
        }
    }

    /**
     * @param int $area
     * @return array
     * @throws GeneralException
     */
    public function getPeerGroupAvgAmountFields($area)
    {
        if ($area == self::RENTABLE_SELECTION)
        {
            return [
                'GROUP_AVG_DIFF_TARGET_PEER_AMOUNT_RNT_DOUBLE',
                'GROUP_AVG_TARGET_AMOUNT_RNT_DOUBLE',
                'GROUP_AVG_PEER_MEANVAL_RNT_DOUBLE',
            ];

        }
        elseif ($area == self::OCCUPIED_SELECTION)
        {
            return [
                'GROUP_AVG_DIFF_TARGET_PEER_AMOUNT_OCC_DOUBLE',
                'GROUP_AVG_TARGET_AMOUNT_OCC_DOUBLE',
                'GROUP_AVG_PEER_MEANVAL_OCC_DOUBLE',
            ];
        }
        elseif ($area == self::ADJUSTED_SELECTION)
        {
            return [
                'GROUP_AVG_DIFF_TARGET_PEER_AMOUNT_ADJ_DOUBLE',
                'GROUP_AVG_TARGET_AMOUNT_ADJ_DOUBLE',
                'GROUP_AVG_PEER_MEANVAL_ADJ_DOUBLE',
            ];
        }
        else
        {
            throw new GeneralException('Unusable area value');
        }
    }

    /**
     * @param Client $ClientObj
     * @param [] $property_id_old_arr
     * @param int $year
     * @return float|int
     * @throws GeneralException'
     */
    public function getPropertyGroupAverageOccupancy($ClientObj, $property_id_old_arr, $year)
    {
        $this->LedgerRepositoryObj->ClientObj = $ClientObj;
        return $this->LedgerRepositoryObj->getGroupAverageOccupancy($property_id_old_arr, $year);
    }

    /**
     * @param Client $ClientObj
     * @param int $property_id_old
     * @param $year
     * @return float
     * @throws GeneralException
     */
    public function getOccupancyForSingleProperty($ClientObj, $property_id_old, $year)
    {

        $this->LedgerRepositoryObj->ClientObj = $ClientObj;
        return (double) current($this->LedgerRepositoryObj->getOccupancyForEachProperty([$property_id_old], $year));
    }

    /**
     * @param Client $ClientObj
     * @param array $property_id_old_arr
     * @param integer|string $year
     * @return array
     */
    public function getOccupancyForEachProperty($ClientObj, $property_id_old_arr, $year)
    {
        $this->LedgerRepositoryObj->ClientObj = $ClientObj;
        return $this->LedgerRepositoryObj->getOccupancyForEachProperty($property_id_old_arr, $year);
    }

    /**
     * @param $period
     * @param bool $targetYear
     * @param bool $targetYearMonths
     * @param bool $previousYearMonths
     * @return bool
     */
    public function checkSameMonthsAcrossYears($period, $targetYear = false, $targetYearMonths = false, $previousYearMonths = false)
    {
        if ($period == self::TRAILING_12_ABBREV)
        {
            $tmpYearMonths = str_replace($targetYear - 1, $targetYear - 2, $targetYearMonths);
            $tmpYearMonths = str_replace($targetYear, $targetYear - 1, $tmpYearMonths);

            return $tmpYearMonths == $previousYearMonths ? true : false;
        }
        elseif ($period == self::CALENDAR_YEAR_ABBREV || $period == self::YEAR_TO_DATE_ABBREV)
        {
            return str_replace($targetYear, $targetYear - 1, $targetYearMonths) == $previousYearMonths ? true : false;
        }
        else
        {
            throw new GeneralException('Unusable period input given');
        }
    }

    /**
     * @param $period
     * @param $year
     * @param $Client
     * @param null $DatabaseConnection
     * @return bool
     * @throws GeneralException
     */
    public function checkForAvailableDataGivenPeriodAndYear($period, $year, $Client, $DatabaseConnection = null)
    {
        if ( ! $DatabaseConnection)
        {
            $DatabaseConnection = DB::connection('mysql_WAYPOINT_LEDGER_' . $Client->client_id_old);
        }

        $dataAvailabilityPeriodField = $this->getDataAvailabilityPeriodField($period);

        $resultDataAvailabilityYears = $DatabaseConnection
            ->table('YEAR_MONTH_PERIOD')
            ->whereNotNull($dataAvailabilityPeriodField)
            ->select($dataAvailabilityPeriodField)
            ->groupBy($dataAvailabilityPeriodField)
            ->pluck($dataAvailabilityPeriodField);

        // check availability of data and only check ledger if data is available
        return ! (count($resultDataAvailabilityYears) == 0 || ! in_array((string) $year, $resultDataAvailabilityYears->toArray()));
    }

    /**
     * @return array
     * @throws GeneralException
     */
    protected function createDataPackageForCombinedSpreadsheets(Collection $payload = null)
    {
        return [
            'data'            => $payload ? $payload->toArray() : $this->payload->toArray(),
            'metadata'        => $this->getMetadata(),
            'transformations' => $this->getSpreadsheetFields(),
        ];
    }

    /**
     * @param Collection $payload
     * @return array|mixed
     * @throws GeneralException
     */
    protected function getMetadata(Collection $payload = null)
    {
        return (new Metadata(
            [
                'LedgerController'              => $this,
                'Property'                      => $this->PropertyObj,
                'PropertyGroup'                 => $this->PropertyGroupObj,
                'ReportTemplateAccountGroupObj' => $this->ReportTemplateAccountGroupObj,
                'count'                         => $payload ? $payload->count() : $this->payload->count(),
                'target'                        => $this->getTargetPayloadSlice(),
                'status'                        => $this->status,
            ]
        ))->toArray();
    }

    /**
     * @param array $additional_attributes
     * @return array
     * @throws GeneralException
     */
    protected function getTargetPayloadSlice(array $additional_attributes = null)
    {
        if ($this->targetPayloadSlice)
        {
            if ($additional_attributes)
            {
                $this->targetPayloadSlice = array_replace($this->targetPayloadSlice, $additional_attributes);
            }
            return $this->targetPayloadSlice;
        }

        $this->targetPayloadSlice = [
            'apiTitle'      => $this->apiTitle,
            'name'          => $this->ReportTemplateAccountGroupObj->display_name,
            'code'          => $this->ReportTemplateAccountGroupObj->report_template_account_group_code,
            'fromDate'      => $this->getFromDate($this->year, $this->period),
            'toDate'        => $this->getToDate($this->year, $this->period),
            'period'        => $this->period,
            'entityName'    => $this->entityName,
            'totalBarUnits' => 'expense',
            'units'         => $this->units,
        ];
        if ($additional_attributes)
        {
            $this->targetPayloadSlice = array_merge($this->targetPayloadSlice, $additional_attributes);
        }
        return $this->targetPayloadSlice;
    }

    /**
     * @param $period
     * @param $year
     * @param $Client
     * @return bool
     */
    function isCurrentYearEqualToAsOfYearAndCalendarYearRequested($period, $year, $Client)
    {
        return $period == self::CALENDAR_YEAR_ABBREV && $this->get_client_asof_date($Client->id)->year == $year && $this->get_client_asof_date($Client->id)->month != 12;
    }

    /**
     * @param $Client
     * @param $propertyIds
     * @param $period
     * @param $targetYear
     * @param $asOfDate
     * @return bool
     */
    public function checkValidRentableAreaForYoyComparison($Client, $propertyIds, $period, $targetYear, $asOfDate)
    {
        $previousYear           = $targetYear - self::YEAR_OFFSET;
        $yearBeforePreviousYear = $targetYear - 2 * self::YEAR_OFFSET;
        $yearsToCheck           = $period == self::TRAILING_12_ABBREV ? [$targetYear, $previousYear, $yearBeforePreviousYear] : [$targetYear, $previousYear];
        $asOfMonth              = (int) $asOfDate->month;

        $DatabaseConnection = DB::connection('mysql_WAYPOINT_LEDGER_' . $Client->client_id_old);

        $results = collect(
            $DatabaseConnection
                ->table('OCCUPANCY_MONTH')
                ->where([
                            ['RENTABLE_AREA', '!=', 0],
                        ])
                ->whereIn('FK_PROPERTY_ID', $propertyIds)
                ->whereIn('FROM_YEAR', $yearsToCheck)
                ->select(
                    'FROM_MONTH as monthText',
                    'YEARMONTH as yearMonth',
                    'FROM_YEAR as year',
                    'FK_PROPERTY_ID as property_id'
                )
                ->get()
        );

        $targetYearResultCount = $results->filter(function ($result) use ($targetYear)
        {
            return $result->year == $targetYear ? true : false;
        })->count();

        $previousYearResultCount = $results->filter(function ($result) use ($previousYear)
        {
            return $result->year == $previousYear ? true : false;
        })->count();

        if ($period == self::TRAILING_12_ABBREV)
        {
            $yearBeforePreviousYearResultCount = $results->filter(function ($result) use ($yearBeforePreviousYear)
            {
                return $result->year == $yearBeforePreviousYear ? true : false;
            })->count();

            return $targetYearResultCount >= $asOfMonth && $previousYearResultCount == 12 && $yearBeforePreviousYearResultCount >= 12 - $asOfMonth ? true : false;
        }
        elseif ($period == self::YEAR_TO_DATE_ABBREV)
        {
            return $targetYearResultCount >= $asOfMonth && $previousYearResultCount >= $asOfMonth ? true : false;
        }
        elseif ($period == self::CALENDAR_YEAR_ABBREV)
        {
            // calendar year period for the asOfYear will always be incomplete unless there's 12 months to compare
            if ($targetYear == $asOfDate->year && $asOfMonth < 12)
            {
                return false;
            }
            else
            {
                return $targetYearResultCount >= 12 && $previousYearResultCount >= 12 ? true : false;
            }
        }
        else
        {
            throw new GeneralException('unusable period given');
        }
    }

    /**
     * @param $Client
     * @param $propertyIds
     * @param $period
     * @param $targetYear
     * @param $asOfDate
     * @return bool
     */
    public function checkValidRentableArea($Client, $propertyIds, $period, $targetYear, $asOfDate)
    {
        $previousYear = $targetYear - self::YEAR_OFFSET;
        $yearsToCheck = $period == self::TRAILING_12_ABBREV ? [$targetYear, $previousYear] : [$targetYear];
        $asOfMonth    = (int) $asOfDate->month;

        $DatabaseConnection = DB::connection('mysql_WAYPOINT_LEDGER_' . $Client->client_id_old);

        $results = collect(
            $DatabaseConnection
                ->table('OCCUPANCY_MONTH')
                ->where([
                            ['RENTABLE_AREA', '!=', 0],
                        ])
                ->whereIn('FK_PROPERTY_ID', $propertyIds)
                ->whereIn('FROM_YEAR', $yearsToCheck)
                ->select(
                    'FROM_MONTH as monthText',
                    'YEARMONTH as yearMonth',
                    'FROM_YEAR as year',
                    'FK_PROPERTY_ID as property_id'
                )
                ->get()
        );

        $targetYearResultCount = $results->filter(function ($result) use ($targetYear)
        {
            return $result->year == $targetYear ? true : false;
        })->count();

        if ($period == self::TRAILING_12_ABBREV)
        {
            $previousYearResultCount = $results->filter(function ($result) use ($previousYear)
            {
                return $result->year == $previousYear ? true : false;
            })->count();

            return $targetYearResultCount >= $asOfMonth && $previousYearResultCount == 12 - $asOfMonth ? true : false;
        }
        elseif ($period == self::YEAR_TO_DATE_ABBREV)
        {
            return $targetYearResultCount >= $asOfMonth ? true : false;
        }
        elseif ($period == self::CALENDAR_YEAR_ABBREV)
        {
            // calendar year period for the asOfYear will always be incomplete unless there's 12 months to compare
            if ($targetYear == $asOfDate->year && $asOfMonth < 12)
            {
                return false;
            }
            else
            {
                return $targetYearResultCount == 12 ? true : false;
            }
        }
        else
        {
            throw new GeneralException('unusable period given');
        }
    }

    /**
     * @param $Client
     * @param $propertyIds
     * @param $period
     * @param $targetYear
     * @param $asOfDate
     * @return array
     */
    public function getDataAvailabilityResultFromRentableArea($Client, $propertyIds, $period, $targetYear, $asOfDate)
    {
        $this->targetYear       = (int) $targetYear;
        $previousYear           = $this->targetYear - self::YEAR_OFFSET;
        $yearBeforePreviousYear = $this->targetYear - 2 * self::YEAR_OFFSET;
        $yearsToCheck           = $period == self::TRAILING_12_ABBREV ? [$this->targetYear, $previousYear, $yearBeforePreviousYear] : [$this->targetYear, $previousYear];
        $asOfMonth              = (int) $asOfDate->month;
        $aggregatedResult       = [];

        $DatabaseConnection = DB::connection('mysql_WAYPOINT_LEDGER_' . $Client->client_id_old);

        $results = collect(
            $DatabaseConnection
                ->table('OCCUPANCY_MONTH')
                ->where([
                            ['RENTABLE_AREA', '!=', 0],
                        ])
                ->whereIn('FK_PROPERTY_ID', $propertyIds)
                ->whereIn('FROM_YEAR', $yearsToCheck)
                ->select(
                    'FROM_MONTH as monthText',
                    'YEARMONTH as yearMonth',
                    'FROM_YEAR as year',
                    'FK_PROPERTY_ID as propertyId'
                )
                ->get()
        );

        foreach ($propertyIds as $propertyId)
        {
            $targetYearResultCount = $results->filter(function ($result) use ($propertyId)
            {
                return $result->year == $this->targetYear && $result->propertyId == $propertyId ? true : false;
            })->count();

            $previousYearResultCount = $results->filter(function ($result) use ($previousYear, $propertyId)
            {
                return $result->year == $previousYear && $result->propertyId == $propertyId ? true : false;
            })->count();

            if ($period == self::TRAILING_12_ABBREV)
            {
                $yearBeforePreviousYearResultCount = $results->filter(function ($result) use ($yearBeforePreviousYear, $propertyId)
                {
                    return $result->year == $yearBeforePreviousYear && $result->propertyId == $propertyId ? true : false;
                })->count();

                $aggregatedResult += $targetYearResultCount >= $asOfMonth && $previousYearResultCount == 12 && $yearBeforePreviousYearResultCount >= 12 - $asOfMonth ? [$propertyId => true] : [$propertyId => false];
            }
            elseif ($period == self::YEAR_TO_DATE_ABBREV)
            {
                $aggregatedResult += $targetYearResultCount >= $asOfMonth && $previousYearResultCount >= $asOfMonth ? [$propertyId => true] : [$propertyId => false];
            }
            elseif ($period == self::CALENDAR_YEAR_ABBREV)
            {
                // calendar year period for the asOfYear will always be incomplete unless there's 12 months to compare
                if ($targetYear == $asOfDate->year && $asOfMonth < 12)
                {
                    $aggregatedResult += [$propertyId => false];
                }
                else
                {
                    $aggregatedResult += $targetYearResultCount == 12 && $previousYearResultCount == 12 ? [$propertyId => true] : [$propertyId => false];
                }
            }
            else
            {
                throw new GeneralException('unusable period given');
            }
        }

        return $aggregatedResult;
    }

    /**
     * @param $period
     * @return string
     */
    public function getDataAvailabilityPeriodField($period)
    {
        if ($period == self::CALENDAR_YEAR_ABBREV)
        {
            return self::DATA_AVAILABILITY_CALENDAR_YEAR_FIELD;
        }
        elseif ($period == self::TRAILING_12_ABBREV)
        {
            return self::DATA_AVAILABILITY_TRAILING_12_FIELD;
        }
        elseif ($period == self::YEAR_TO_DATE_ABBREV)
        {
            return self::DATA_AVAILABILITY_YEAR_TO_DATE_FIELD;
        }
        else
        {
            throw new GeneralException('Invalid period');
        }

    }

    /**
     * @param $area
     * @return array
     */
    public function getMonthlyAmountFields($area)
    {

        $data = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];

        if ($area == self::RENTABLE_SELECTION)
        {
            return array_map(function ($month) { return 'MEANVAL_RNT_DOUBLE_' . $month . ' as ' . strtolower($month); }, $data);
        }
        elseif ($area == self::OCCUPIED_SELECTION)
        {
            return array_map(function ($month) { return 'MEANVAL_OCC_DOUBLE_' . $month . ' as ' . strtolower($month); }, $data);
        }
        else
        {
            throw new GeneralException('Invalid area');
        }
    }

    /**
     * @param $area
     * @return bool|string
     */
    public function getBenchmarkAverageField($area)
    {

        if ($area == self::RENTABLE_SELECTION)
        {
            return self::BENCHMARK_AVERAGE_RENTABLE_FIELD;
        }
        elseif ($area == self::OCCUPIED_SELECTION)
        {
            return self::BENCHMARK_AVERAGE_OCCUPIED_FIELD;
        }
        else
        {
            return false;
        }

    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    /**
     * @param $period
     * @param $year
     * @param $asOfMonth
     * @return array|null
     */
    public function getPeriodDateRange($period, $year, $asOfMonth)
    {
        if ($period == self::CALENDAR_YEAR_ABBREV)
        {
            return [date('c', strtotime($asOfMonth . ' ' . $year))];
        }
    }

    /**
     * @param $area
     * @param bool $double
     * @return array
     * @throws GeneralException
     */
    public function getFields($area, $double = false)
    {
        if ($area == self::RENTABLE_SELECTION)
        {
            return [
                $double ? self::AMOUNT_RENTABLE_DOUBLE_FIELD : self::AMOUNT_RENTABLE_FIELD,
                self::RANK_RENTABLE_FIELD,
            ];
        }
        elseif ($area == self::OCCUPIED_SELECTION)
        {
            return [
                $double ? self::AMOUNT_OCCUPIED_DOUBLE_FIELD : self::AMOUNT_OCCUPIED_FIELD,
                self::RANK_OCCUPIED_FIELD,
            ];
        }
        elseif ($area == self::ADJUSTED_SELECTION)
        {
            return [
                $double ? self::AMOUNT_ADJUSTED_DOUBLE_FIELD : self::AMOUNT_ADJUSTED_FIELD,
                self::RANK_ADJUSTED_FIELD,
            ];
        }
        else
        {
            throw new GeneralException('unusable area value given');
        }
    }

    /**
     * @param $area
     * @return string
     * @throws GeneralException
     */
    public function getRankField($area)
    {
        switch ($area)
        {
            case self::RENTABLE_SELECTION:
                return self::RANK_RENTABLE_FIELD;
            case self::OCCUPIED_SELECTION:
                return self::RANK_OCCUPIED_FIELD;
            case self::ADJUSTED_SELECTION:
                return self::RANK_ADJUSTED_FIELD;
            default:
                throw new GeneralException('unusable area value given');
        }
    }

    /**
     * @param $area
     * @param bool $double
     * @return array
     * @throws GeneralException
     */
    public function getAmountAndRankFields($area, $double = false)
    {
        if ($area == self::RENTABLE_SELECTION)
        {
            return [
                $double ? self::AMOUNT_RENTABLE_DOUBLE_FIELD : self::AMOUNT_RENTABLE_FIELD,
                self::RANK_RENTABLE_FIELD,
            ];
        }
        elseif ($area == self::OCCUPIED_SELECTION)
        {
            return [
                $double ? self::AMOUNT_OCCUPIED_DOUBLE_FIELD : self::AMOUNT_OCCUPIED_FIELD,
                self::RANK_OCCUPIED_FIELD,
            ];
        }
        elseif ($area == self::ADJUSTED_SELECTION)
        {
            return [
                $double ? self::AMOUNT_ADJUSTED_DOUBLE_FIELD : self::AMOUNT_ADJUSTED_FIELD,
                self::RANK_ADJUSTED_FIELD,
            ];
        }
        else
        {
            throw new GeneralException('unusable area value given');
        }
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    /**
     * @param $area
     * @param $period
     * @return mixed
     * @throws GeneralException
     * @deprecated ??? JMS
     */
    public function getVarianceRankingFieldsFromAreaAndPeriod($area, $period)
    {
        if ($period == self::CALENDAR_YEAR_ABBREV)
        {
            if ($area == self::RENTABLE_SELECTION)
            {
                return [
                    self::AMOUNT_RENTABLE_ACTUAL_CALENDAR_YEAR,
                    self::AMOUNT_RENTABLE_BUDGET_CALENDAR_YEAR,
                    self::VARIANCE_RENTABLE_CALENDAR_YEAR,
                    self::RANK_RENTABLE_CALENDAR_YEAR_FIELD,
                ];
            }
            elseif ($area == self::OCCUPIED_SELECTION)
            {
                return [
                    self::AMOUNT_OCCUPIED_ACTUAL_CALENDAR_YEAR,
                    self::AMOUNT_OCCUPIED_BUDGET_CALENDAR_YEAR,
                    self::VARIANCE_OCCUPIED_CALENDAR_YEAR,
                    self::RANK_OCCUPIED_CALENDAR_YEAR_FIELD,
                ];
            }
            elseif ($area == self::ADJUSTED_SELECTION)
            {
                return [
                    self::AMOUNT_ADJUSTED_ACTUAL_CALENDAR_YEAR,
                    self::AMOUNT_ADJUSTED_BUDGET_CALENDAR_YEAR,
                    self::VARIANCE_ADJUSTED_CALENDAR_YEAR,
                    self::RANK_ADJUSTED_CALENDAR_YEAR_FIELD,
                ];
            }
            else
            {
                throw new GeneralException('unusable area value given');
            }
        }
        elseif ($period == self::TRAILING_12_ABBREV)
        {

            if ($area == self::RENTABLE_SELECTION)
            {
                return [
                    self::AMOUNT_RENTABLE_ACTUAL_TRAILING_12,
                    self::AMOUNT_RENTABLE_BUDGET_TRAILING_12,
                    self::VARIANCE_RENTABLE_TRAILING_12,
                    self::RANK_RENTABLE_TRAILING_12_FIELD,
                ];
            }
            elseif ($area == self::OCCUPIED_SELECTION)
            {
                return [
                    self::AMOUNT_OCCUPIED_ACTUAL_TRAILING_12,
                    self::AMOUNT_OCCUPIED_BUDGET_TRAILING_12,
                    self::VARIANCE_OCCUPIED_TRAILING_12,
                    self::RANK_OCCUPIED_TRAILING_12_FIELD,
                ];
            }
            elseif ($area == self::ADJUSTED_SELECTION)
            {
                return [
                    self::AMOUNT_ADJUSTED_ACTUAL_TRAILING_12,
                    self::AMOUNT_ADJUSTED_BUDGET_TRAILING_12,
                    self::VARIANCE_ADJUSTED_TRAILING_12,
                    self::RANK_ADJUSTED_TRAILING_12_FIELD,
                ];
            }
            else
            {
                throw new GeneralException('unusable area value given');
            }
        }
        elseif ($period == self::YEAR_TO_DATE_ABBREV)
        {
            if ($area == self::RENTABLE_SELECTION)
            {
                return [
                    self::AMOUNT_RENTABLE_ACTUAL_YEAR_TO_DATE,
                    self::AMOUNT_RENTABLE_BUDGET_YEAR_TO_DATE,
                    self::VARIANCE_RENTABLE_YEAR_TO_DATE,
                    self::RANK_RENTABLE_YEAR_TO_DATE_FIELD,
                ];
            }
            elseif ($area == self::OCCUPIED_SELECTION)
            {
                return [
                    self::AMOUNT_OCCUPIED_ACTUAL_YEAR_TO_DATE,
                    self::AMOUNT_OCCUPIED_BUDGET_YEAR_TO_DATE,
                    self::VARIANCE_OCCUPIED_YEAR_TO_DATE,
                    self::RANK_OCCUPIED_YEAR_TO_DATE_FIELD,
                ];
            }
            elseif ($area == self::ADJUSTED_SELECTION)
            {
                return [
                    self::AMOUNT_ADJUSTED_ACTUAL_YEAR_TO_DATE,
                    self::AMOUNT_ADJUSTED_BUDGET_YEAR_TO_DATE,
                    self::VARIANCE_ADJUSTED_YEAR_TO_DATE,
                    self::RANK_ADJUSTED_YEAR_TO_DATE_FIELD,
                ];
            }
        }
        else
        {
            throw new GeneralException('unusable period value given');
        }
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    /**
     * @param $type
     * @param $period
     * @return string
     */
    public function getBenchmarkType($type = null, $period = null)
    {
        if ($period == self::CALENDAR_YEAR_ABBREV)
        {
            return $type;
        }
        elseif ($period == self::TRAILING_12_ABBREV)
        {
            return $type . '_' . substr($period, 1, 2);
        }
        elseif ($period == self::YEAR_TO_DATE_ABBREV)
        {
            return $type . '_' . $period;
        }
    }

    /**
     * @param $period
     * @return string
     * @throws GeneralException
     */
    public function getBenchmarkTypeVariance($period)
    {
        if ($period == self::CALENDAR_YEAR_ABBREV)
        {
            return self::CALENDAR_YEAR_BENCHMARK_TYPE_VARIANCE;
        }
        elseif ($period == self::TRAILING_12_ABBREV)
        {
            return self::TRAILING_12_BENCHMARK_TYPE_VARIANCE;
        }
        elseif ($period == self::YEAR_TO_DATE_ABBREV)
        {
            return self::YEAR_TO_DATE_BENCHMARK_TYPE_VARIANCE;
        }
        else
        {
            throw new GeneralException('unusable period value given');
        }
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    /**
     * @param $period
     * @param $area
     * @return array
     * @throws GeneralException
     */
    public function getGroupVarianceFieldsFromPeriodAndArea($period, $area)
    {
        if ($period == self::CALENDAR_YEAR_ABBREV)
        {

            if ($area == self::RENTABLE_SELECTION)
            {
                return [
                    self::GROUP_AMOUNT_RENTABLE_ACTUAL_CALENDAR_YEAR_FIELD,
                    self::GROUP_AMOUNT_RENTABLE_BUDGET_CALENDAR_YEAR_FIELD,
                    self::GROUP_VARIANCE_CALENDAR_YEAR_RENTABLE,
                ];
            }
            elseif ($area == self::OCCUPIED_SELECTION)
            {
                return [
                    self::GROUP_AMOUNT_OCCUPIED_ACTUAL_CALENDAR_YEAR_FIELD,
                    self::GROUP_AMOUNT_OCCUPIED_BUDGET_CALENDAR_YEAR_FIELD,
                    self::GROUP_VARIANCE_CALENDAR_YEAR_OCCUPIED,
                ];
            }
            elseif ($area == self::ADJUSTED_SELECTION)
            {
                return [
                    self::GROUP_AMOUNT_ADJUSTED_ACTUAL_CALENDAR_YEAR_FIELD,
                    self::GROUP_AMOUNT_ADJUSTED_BUDGET_CALENDAR_YEAR_FIELD,
                    self::GROUP_VARIANCE_CALENDAR_YEAR_ADJUSTED,
                ];
            }
            else
            {
                throw new GeneralException('unusable area value given');
            }
        }
        elseif ($period == self::TRAILING_12_ABBREV)
        {
            if ($area == self::RENTABLE_SELECTION)
            {
                return [
                    self::GROUP_AMOUNT_RENTABLE_ACTUAL_TRAILING_12_FIELD,
                    self::GROUP_AMOUNT_RENTABLE_BUDGET_TRAILING_12_FIELD,
                    self::GROUP_VARIANCE_TRAILING_12_RENTABLE,
                ];
            }
            elseif ($area == self::OCCUPIED_SELECTION)
            {
                return [
                    self::GROUP_AMOUNT_OCCUPIED_ACTUAL_TRAILING_12_FIELD,
                    self::GROUP_AMOUNT_OCCUPIED_BUDGET_TRAILING_12_FIELD,
                    self::GROUP_VARIANCE_TRAILING_12_OCCUPIED,
                ];
            }
            elseif ($area == self::ADJUSTED_SELECTION)
            {
                return [
                    self::GROUP_AMOUNT_ADJUSTED_ACTUAL_TRAILING_12_FIELD,
                    self::GROUP_AMOUNT_ADJUSTED_BUDGET_TRAILING_12_FIELD,
                    self::GROUP_VARIANCE_TRAILING_12_ADJUSTED,
                ];
            }
            else
            {
                throw new GeneralException('unusable area value given');
            }
        }
        elseif ($period == self::YEAR_TO_DATE_ABBREV)
        {
            if ($area == self::RENTABLE_SELECTION)
            {
                return [
                    self::GROUP_AMOUNT_RENTABLE_ACTUAL_YEAR_TO_DATE_FIELD,
                    self::GROUP_AMOUNT_RENTABLE_BUDGET_YEAR_TO_DATE_FIELD,
                    self::GROUP_VARIANCE_YEAR_TO_DATE_RENTABLE,
                ];
            }
            elseif ($area == self::OCCUPIED_SELECTION)
            {
                return [
                    self::GROUP_AMOUNT_OCCUPIED_ACTUAL_YEAR_TO_DATE_FIELD,
                    self::GROUP_AMOUNT_OCCUPIED_BUDGET_YEAR_TO_DATE_FIELD,
                    self::GROUP_VARIANCE_YEAR_TO_DATE_OCCUPIED,
                ];
            }
            elseif ($area == self::ADJUSTED_SELECTION)
            {
                return [
                    self::GROUP_AMOUNT_ADJUSTED_ACTUAL_YEAR_TO_DATE_FIELD,
                    self::GROUP_AMOUNT_ADJUSTED_BUDGET_YEAR_TO_DATE_FIELD,
                    self::GROUP_VARIANCE_YEAR_TO_DATE_ADJUSTED,
                ];
            }
            else
            {
                throw new GeneralException('unusable area value given');
            }
        }
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    /**
     * @param $period
     * @return array
     */
    public function getBenchmarkTypes($period)
    {
        if ($period == self::CALENDAR_YEAR_ABBREV)
        {
            return [
                self::CALENDAR_YEAR_BENCHMARK_TYPE_ACTUAL,
                self::CALENDAR_YEAR_BENCHMARK_TYPE_BUDGET,
                self::CALENDAR_YEAR_BENCHMARK_TYPE_VARIANCE,
            ];
        }
        elseif ($period == self::TRAILING_12_ABBREV)
        {
            return [
                self::TRAILING_12_BENCHMARK_TYPE_ACTUAL,
                self::TRAILING_12_BENCHMARK_TYPE_BUDGET,
                self::TRAILING_12_BENCHMARK_TYPE_VARIANCE,
            ];
        }
        elseif ($period == self::YEAR_TO_DATE_ABBREV)
        {
            return [
                self::YEAR_TO_DATE_BENCHMARK_TYPE_ACTUAL,
                self::YEAR_TO_DATE_BENCHMARK_TYPE_BUDGET,
                self::YEAR_TO_DATE_BENCHMARK_TYPE_VARIANCE,
            ];
        }
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    /**
     * @param $period
     * @return array
     */
    public function getVarianceBenchmarkTypesPropertyOnly($period)
    {
        if ($period == self::CALENDAR_YEAR_ABBREV)
        {
            return [
                self::CALENDAR_YEAR_BENCHMARK_TYPE_ACTUAL,
                self::CALENDAR_YEAR_BENCHMARK_TYPE_BUDGET,
            ];
        }
        elseif ($period == self::TRAILING_12_ABBREV)
        {
            return [
                self::TRAILING_12_BENCHMARK_TYPE_ACTUAL,
                self::TRAILING_12_BENCHMARK_TYPE_BUDGET,
            ];
        }
        elseif ($period == self::YEAR_TO_DATE_ABBREV)
        {
            return [
                self::YEAR_TO_DATE_BENCHMARK_TYPE_ACTUAL,
                self::YEAR_TO_DATE_BENCHMARK_TYPE_BUDGET,
            ];
        }
    }

    /**
     * @param $area
     * @param bool $double
     * @return string
     * @throws GeneralException
     */
    public function getAmountFieldFromArea($area, $double = false)
    {
        if ($area == self::RENTABLE_SELECTION)
        {
            return $double ? self::AMOUNT_RENTABLE_DOUBLE_FIELD : self::AMOUNT_RENTABLE_FIELD;
        }
        elseif ($area == self::OCCUPIED_SELECTION)
        {
            return $double ? self::AMOUNT_OCCUPIED_DOUBLE_FIELD : self::AMOUNT_OCCUPIED_FIELD;
        }
        elseif ($area == self::ADJUSTED_SELECTION)
        {
            return $double ? self::AMOUNT_ADJUSTED_DOUBLE_FIELD : self::AMOUNT_ADJUSTED_FIELD;
        }
        else
        {
            throw new GeneralException('unusable area value given');
        }
    }

    /**
     * @param $area
     * @return string
     */
    public function getGroupAmountFieldFromArea($area)
    {
        return ($area == self::RENTABLE_SELECTION) ? self::GROUP_AMOUNT_RENTABLE_FIELD : self::GROUP_AMOUNT_OCCUPIED_FIELD;
    }

    /**
     * @param $area
     * @param bool $double
     * @return array
     * @throws GeneralException
     */
    public function getAmountFieldsFromArea($area, $double = false)
    {
        if ($area == self::RENTABLE_SELECTION)
        {
            return $double ? [self::AMOUNT_RENTABLE_DOUBLE_FIELD, self::BENCHMARK_AVERAGE_RENTABLE_FIELD] : [self::AMOUNT_RENTABLE_FIELD, self::BENCHMARK_AVERAGE_RENTABLE_FIELD];
        }
        elseif ($area == self::OCCUPIED_SELECTION)
        {
            return $double ? [self::AMOUNT_OCCUPIED_DOUBLE_FIELD, self::BENCHMARK_AVERAGE_OCCUPIED_FIELD] : [self::AMOUNT_OCCUPIED_FIELD, self::BENCHMARK_AVERAGE_OCCUPIED_FIELD];
        }
        elseif ($area == self::ADJUSTED_SELECTION)
        {
            return $double ? [self::AMOUNT_ADJUSTED_DOUBLE_FIELD, self::BENCHMARK_AVERAGE_ADJUSTED_FIELD] : [self::AMOUNT_ADJUSTED_FIELD, self::BENCHMARK_AVERAGE_ADJUSTED_FIELD];
        }
        else
        {
            throw new GeneralException('unusable area value given');
        }
    }

    /**
     * @param $area
     * @return array
     * @throws GeneralException
     */
    public function getGroupAmountFieldsFromArea($area)
    {
        if ($area == self::RENTABLE_SELECTION)
        {
            return [self::GROUP_AMOUNT_RENTABLE_FIELD, self::BENCHMARK_AVERAGE_RENTABLE_FIELD];
        }
        elseif ($area == self::OCCUPIED_SELECTION)
        {
            return [self::GROUP_AMOUNT_OCCUPIED_FIELD, self::BENCHMARK_AVERAGE_OCCUPIED_FIELD];
        }
        elseif ($area == self::ADJUSTED_SELECTION)
        {
            return [self::GROUP_AMOUNT_ADJUSTED_FIELD, self::BENCHMARK_AVERAGE_ADJUSTED_FIELD];
        }
        else
        {
            throw new GeneralException('unusable area value given');
        }
    }

    /**
     * @param $area
     * @return string
     * @throws GeneralException
     */
    public function getGroupAmountField($area = null)
    {
        if (($area && ! $this->isValidArea($area)) || (isset($this->area) && ! $this->isValidArea()))
        {
            throw new GeneralException('the area given is invalid, please double check the input parameter');
        }
        return 'MEANVAL_' . self::AREA_LOOKUP[$area ? $area : $this->area] . '_DOUBLE';
    }

    /**
     * @param null $area
     * @return bool
     * @throws GeneralException
     */
    public function isValidArea($area = null)
    {
        if ($area)
        {
            return in_array($area, self::ACCEPTABLE_AREAS);
        }
        elseif (isset($this->area))
        {
            return in_array($this->area, self::ACCEPTABLE_AREAS);
        }
        throw new GeneralException('No area input value given to this validatation function, please double check the input parameter');
    }

    /**
     * @return array
     */
    public function getLineage()
    {
        $count   = 0;
        $lineage = [];
        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupObj;

        while ($ReportTemplateAccountGroupObj)
        {
            $lineage[] = [
                'order'                            => $count,
                'name'                             => $ReportTemplateAccountGroupObj->display_name,
                'code'                             => $ReportTemplateAccountGroupObj->report_template_account_group_code,
                'report_template_account_group_id' => $ReportTemplateAccountGroupObj->id,
            ];
            $count++;

            // TODO (Alex) [283y98dsf] - remove breaking on this condition if/when we choose to use the top of the expenses tree
            if (
                stri_equal($ReportTemplateAccountGroupObj->nativeAccountType->native_account_type_name, self::NATIVE_ACCOUNT_TYPE_EXPENSES_TEXT)
                &&
                $ReportTemplateAccountGroupObj->report_template_account_group_code == $this->ReportTemplateAccountGroupObj->nativeAccountType->getUltimateParentForReportTemplateAccountGroup($this->ClientObj->id,
                                                                                                                                                                                              true)->deprecated_waypoint_code
            )
            {
                break;
            }

            $ReportTemplateAccountGroupObj = $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent;
        }
        return $lineage;
    }

    /**
     * @param $year
     * @param $period
     * @return string
     * @throws GeneralException
     */
    public function getFromDate($year, $period)
    {
        if (empty($this->client_as_of_date))
        {
            $this->client_as_of_date = $this->getClientObject()->get_client_asof_date();
        }

        switch ($period)
        {
            case self::CALENDAR_YEAR_ABBREV:
                return Carbon::createFromDate($year, 1, 1, 'America/Los_Angeles')->toAtomString();
                break;
            case self::TRAILING_12_ABBREV:
                return Carbon::createFromDate($year - 1, $this->client_as_of_date->month + 1, 1, 'America/Los_Angeles')->toAtomString();
                break;
            case self::YEAR_TO_DATE_ABBREV:
                return Carbon::createFromDate($year, 1, 1, 'America/Los_Angeles')->toAtomString();
                break;

            default:
                throw new GeneralException('Invalid period given');
        }
    }

    /**
     * @param $year
     * @param $period
     * @return string
     * @throws GeneralException
     */
    public function getToDate($year, $period)
    {
        if (empty($this->client_as_of_date))
        {
            $this->client_as_of_date = $this->getClientObject()->get_client_asof_date();
        }

        if ($year == $this->client_as_of_date->year)
        {
            switch ($period)
            {
                case self::CALENDAR_YEAR_ABBREV:
                    return Carbon::createFromDate($year, $this->client_as_of_date->month, 1, 'America/Los_Angeles')->modify('last day of this month')->toAtomString();
                    break;
                case self::TRAILING_12_ABBREV:
                    return Carbon::createFromDate($year, $this->client_as_of_date->month, 1, 'America/Los_Angeles')->modify('last day of this month')->toAtomString();
                    break;
                case self::YEAR_TO_DATE_ABBREV:
                    return Carbon::createFromDate($year, $this->client_as_of_date->month, 1, 'America/Los_Angeles')->modify('last day of this month')->toAtomString();
                    break;

                default:
                    throw new GeneralException('Invalid period given', 404);
            }
        }
        else
        {
            switch ($period)
            {
                case self::CALENDAR_YEAR_ABBREV:
                    return Carbon::createFromDate($year, 12, 1, 'America/Los_Angeles')->modify('last day of this month')->toAtomString();
                    break;
                case self::TRAILING_12_ABBREV:
                    return Carbon::createFromDate($year, $this->client_as_of_date->month, 1, 'America/Los_Angeles')->modify('last day of this month')->toAtomString();
                    break;
                case self::YEAR_TO_DATE_ABBREV:
                    return Carbon::createFromDate($year, $this->client_as_of_date->month, 1, 'America/Los_Angeles')->modify('last day of this month')->toAtomString();
                    break;

                default:
                    throw new GeneralException('Invalid period given', self::HTTP_ERROR_RESPONSE_CODE);
            }
        }
    }

    /**
     * @param integer $client_id
     * @return Carbon
     * @throws GeneralException
     */
    public function get_client_asof_date($client_id)
    {
        if ( ! $Client = Client::find($client_id))
        {
            throw new GeneralException('Invalid Client ID', self::HTTP_ERROR_RESPONSE_CODE);
        }
        if ( ! empty($this->client_as_of_date))
        {
            return $this->client_as_of_date;
        }

        $DatabaseConnection = DB::connection('mysql_WAYPOINT_LEDGER_' . $Client->client_id_old);
        $result             = $DatabaseConnection
            ->table('TARGET_ASOF_MONTH')
            ->select(
                'TARGET_ASOF_MONTH.FROM_YEAR as FROM_YEAR',
                'TARGET_ASOF_MONTH.COVERED_YEAR as COVERED_YEAR',
                'TARGET_ASOF_MONTH.MOY as MOY',
                'TARGET_ASOF_MONTH.YEARMONTH as YEARMONTH',
                'TARGET_ASOF_MONTH.FROM_MONTH as FROM_MONTH'
            )
            ->first();
        return Carbon::create($result->FROM_YEAR, $result->MOY, 1, 0, 0, 0)->modify('last day of this month');
    }

    /**
     * @param Client $ClientObj
     * @return Carbon|null|static
     */
    static function getClientAsOfDate(Client $ClientObj): Carbon
    {
        $result = DB::connection('mysql_WAYPOINT_LEDGER_' . $ClientObj->client_id_old)
                    ->table('TARGET_ASOF_MONTH')
                    ->select(
                        'TARGET_ASOF_MONTH.FROM_YEAR as FROM_YEAR',
                        'TARGET_ASOF_MONTH.COVERED_YEAR as COVERED_YEAR',
                        'TARGET_ASOF_MONTH.MOY as MOY',
                        'TARGET_ASOF_MONTH.YEARMONTH as YEARMONTH',
                        'TARGET_ASOF_MONTH.FROM_MONTH as FROM_MONTH'
                    )
                    ->first();
        return Carbon::create($result->FROM_YEAR, $result->MOY, 1, 0, 0, 0)->modify('last day of this month');
    }

    /**
     * @param $result
     * @param $message
     * @param array $errors
     * @param array $warnings
     * @param array $metadata
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function sendResponse($result, $message, $errors = [], $warnings = [], $metadata = [])
    {
        $metadata['ledger_download_link'] = $this->getLedgerDownloadLink();
        $metadata['client_as_of_date']    = $this->client_as_of_date ? $this->client_as_of_date->format("F j, Y, g:i a T") : null;
        if ( ! empty($this->queryLog))
        {
            $metadata['queryLog'] = $this->queryLog;
        }
        return parent::sendResponse($result, $message, $errors, $warnings, $metadata);
    }

    /**
     * @return array
     */
    public function getSpreadsheetFields()
    {
        $results = [];
        foreach ($this as $key => $value)
        {
            if (strpos($key, 'spreadsheet') !== false)
            {
                $results[$key] = $value;
            }
        }
        return $results;
    }

    /**
     * @param $from
     * @param $to
     * @param $subject
     * @return mixed
     */
    function str_replace_first($from, $to, $subject)
    {
        $newstring = null;
        $pos       = strpos($subject, $from);
        if ($pos !== false)
        {
            $newstring = substr_replace($subject, $to, $pos, 1);
        }
        return $newstring;
    }

    /**
     *
     */
    public function enableQueryLog()
    {
        try
        {
            DB::listen(function ($sql)
            {
                $queryText = $sql->sql;
                foreach ($sql->bindings as $binding)
                {
                    if (is_string($binding) && is_numeric($binding))
                    {
                        $binding = (float) $binding;
                    }
                    elseif (is_string($binding))
                    {
                        $binding = "'$binding'";
                    }
                    $queryText = $this->str_replace_first('?', $binding, $queryText);
                }
                $this->queryLog[] = [
                    'query'         => $queryText,
                    'query-details' => $sql,
                ];
            });
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
        }
    }

    /**
     * @param null|string
     * @return string
     * @throws GeneralException
     */
    protected function getPropertyAreaField()
    {
        // this to ensure function definition is the same for child classes
        if (func_num_args() > 0)
        {
            list($area) = func_get_args();
            if ( ! in_array($area, self::ACCEPTABLE_AREAS))
            {
                throw new GeneralException('Unusable area given');
            }
            return $area . '_AREA';
        }
        elseif ($this->area)
        {
            if ( ! in_array($this->area, self::ACCEPTABLE_AREAS))
            {
                throw new GeneralException('Unusable area given');
            }
            return $this->area . '_AREA';
        }

        throw new GeneralException('No area given');
    }

    /**
     * @return string
     * @throws GeneralException
     */
    protected function getPropertyGroupAreaField()
    {
        if ( ! $this->area && in_array($this->area, self::ACCEPTABLE_AREAS))
        {
            throw new GeneralException('Unusable report given');
        }
        return 'GROUP_SUM_' . $this->area . '_AREA';
    }

    /**
     * getTotalPeerCountForGroup
     *      - counts total number of peers in all peersets for a group
     *      - this includes duplicate peers
     * @return int
     * @throws \App\Waypoint\Exceptions\GeneralException
     */
    public function getTotalPeerCountForGroup()
    {
        $ConnectionObj = DatabaseConnectionRepository::getPeerDatabaseConnection($this->ClientObj);
        return $ConnectionObj
            ->table('PEER_GROUP_INFO_CLIENT_' . $this->ClientObj->client_id_old)
            ->where('FROM_YEAR', '=', $this->year)
            ->whereIn('TARGET_PROPERTY_ID',
                      $this->PropertyGroupObj->properties->pluck('property_id_old')->toArray())
            ->select('FK_PROPERTY_ID')
            ->count();
    }

    /**
     * @param $report_template_account_group_id int
     * @return mixed
     */
    protected function getReportTemplateAccountGroup($report_template_account_group_id)
    {
        if ( ! $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupRepositoryObj->find($report_template_account_group_id))
        {
            throw new LedgerException('could not find report template for this group, please double check the report template');
        }
        return $ReportTemplateAccountGroupObj;
    }

    protected function prepareQueryLog()
    {
        $query_log_arr = $this->DatabaseConnection->getQueryLog();

        foreach ($query_log_arr as $query_arr)
        {
            $placeholder = '?';
            $query       = $query_arr['query'];
            foreach ($query_arr['bindings'] as $binding)
            {
                $substring_position = strpos($query, $placeholder);
                if ($substring_position !== false)
                {
                    $query = substr_replace($query, $binding, $substring_position, strlen($placeholder));
                }
            }
            $this->queryLog[] = $query;
        }
    }
}
