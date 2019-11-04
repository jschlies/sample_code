<?php

namespace App\Waypoint\Http\Controllers\Api\Report;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiGuardController;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Models\NativeAccountTypeTrailer;
use App\Waypoint\Models\NativeCoa;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyNativeCoa;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\ReportTemplateMapping;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Repositories\PropertyNativeCoaRepository;
use App\Waypoint\Repositories\ReportTemplateRepository;
use App\Waypoint\SpreadsheetCollection;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;

class WaypointMasterBridgeController extends ApiGuardController
{
    /**
     * @todo - either merge this (and other Report controllers) into app/Http/Controllers/ApiRequest or
     *       come up w/ a naming system for all controllers
     */
    /** @var  ClientRepository */
    private $ClientRepositoryObj;

    /** @var  ClientRepository */
    private $ReportTemplateRepositoryObj;

    /**
     * @var boolean
     */
    protected $skip_policies = true;

    /**
     * WaypointMasterBridgeController constructor.
     */
    public function __construct()
    {
        $this->ClientRepositoryObj         = App::make(ClientRepository::class);
        $this->ReportTemplateRepositoryObj = App::make(ReportTemplateRepository::class);
        parent::__construct();
    }

    /**
     * Produces csv download of CLIENT_DETAIL table
     * CREATE TABLE 'CLIENT_DETAIL' (
     * 'CLIENT_ID' bigint(20) NOT NULL AUTO_INCREMENT,
     * 'CLIENT_NAME' varchar(100) DEFAULT NULL,
     * 'CLIENT_CODE' varchar(10) DEFAULT NULL,
     * 'STAGING_DB' varchar(80) DEFAULT NULL,
     * 'RAW_UTILITY_DB' varchar(80) DEFAULT NULL,
     * 'LEDGER_DB' varchar(80) DEFAULT NULL,
     * 'UTILITY_DB' varchar(80) DEFAULT NULL,
     * 'DISPLAY_NAME' varchar(100) DEFAULT NULL,
     * PRIMARY KEY ('CLIENT_ID')
     * ) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
     *
     * curl -X GET
     *      -H "Content-Type: application/json"
     *      -H "X-Authorization: xxxxxxxxxx"
     *      -H "Cache-Control: no-cache"
     *      "http://homestead.app/api/v1/waypoint_hermes_master_bridge/Root/waypointMasterBridge/client_detail"
     *
     * @param \Illuminate\Http\Request $RequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index_client_detail(Request $RequestObj)
    {
        $this->ClientRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /** @var Collection $ClientObjArr */
        $ClientObjArr = $this->ClientRepositoryObj->findWhere([['client_id_old', '<>', null]]);
        $return_me    = new  SpreadsheetCollection();

        $client_id_old_de_duper_arr = [];
        /** @var Client $ClientObj */
        foreach ($ClientObjArr as $ClientObj)
        {
            if (isset($client_id_old_de_duper_arr[$ClientObj->client_id_old]))
            {
                continue;
            }
            $return_me[] = [
                'CLIENT_ID'    => $ClientObj->client_id_old,
                'CLIENT_NAME'  => $ClientObj->name,
                'DISPLAY_NAME' => $ClientObj->display_name_old,

                'STAGING_DB'     => 'waypoint_staging_' . $ClientObj->client_id_old,
                'RAW_UTILITY_DB' => 'waypoint_utilities_raw_' . $ClientObj->client_id_old,
                'LEDGER_DB'      => 'waypoint_ledger_' . $ClientObj->client_id_old,
                'UTILITY_DB'     => 'waypoint_utilities_' . $ClientObj->client_id_old,
                'CLIENT_CODE'    => $ClientObj->client_code,
            ];

            $client_id_old_de_duper_arr[$ClientObj->client_id_old] = true;
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'client_detail retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'client_detail'
        );
    }

    /**
     * Produces csv download of PROPERTY_DETAILS table
     *
     * CREATE TABLE 'PROPERTY_DETAILS' (
     * 'MASTER_PROPERTY_ID' bigint(20) NOT NULL AUTO_INCREMENT,
     * 'FK_CLIENT_ID' bigint(20) NOT NULL,
     * 'WP_PROPERTY_ID' bigint(20) NOT NULL,
     * 'PROPERTY_CODE' varchar(10) DEFAULT NULL,
     * 'PROPERTY_NAME' varchar(100) NOT NULL,
     * 'PROPERTY_PHYSICAL_ADDRESS' varchar(500) DEFAULT NULL,
     * 'PROPERTY_CITY' varchar(100) DEFAULT NULL,
     * 'PROPERTY_STATE' varchar(20) DEFAULT NULL,
     * 'PROPERTY_ZIP' varchar(15) DEFAULT NULL,
     * 'PROPERTY_COUNTRY' varchar(50) DEFAULT NULL,
     * 'PROPERTY_LONGITUDE' decimal(16,8) DEFAULT NULL,
     * 'PROPERTY_LATITUDE' decimal(16,8) DEFAULT NULL,
     * 'ACTIVE_INACTIVE' tinyint(4) NOT NULL DEFAULT '1',
     * 'ACTIVE_INACTIVE_DATE' datetime DEFAULT NULL,
     * 'PROPERTY_SQUARE_FOOTAGE' decimal(20,2) DEFAULT NULL,
     * 'LOAD_FACTOR' double DEFAULT '0',
     * 'PROPERTY_TYPE' int(11) DEFAULT NULL,
     * 'LEASE_TYPE' varchar(100) DEFAULT NULL,
     * 'YEAR_BUILT' varchar(4) DEFAULT NULL,
     * PRIMARY KEY ('MASTER_PROPERTY_ID'),
     * KEY 'PROPERTY_CODE' ('PROPERTY_CODE')
     * ) ENGINE=InnoDB AUTO_INCREMENT=29312 DEFAULT CHARSET=latin1;
     *
     * curl -X GET
     *      -H "Content-Type: application/json"
     *      -H "X-Authorization: xxxxxxxxxx"
     *      -H "Cache-Control: no-cache"
     *      "http://homestead.app/api/v1/waypoint_hermes_master_bridge/Root/waypointMasterBridge/clients/2/2/property_details"
     *
     * @param Request $RequestObj
     * @param integer $client_id_old
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index_property_details(Request $RequestObj, $client_id_old)
    {
        $this->ClientRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        $ClientObj = $this->ClientRepositoryObj->findWhere(
            [
                'client_id_old' => $client_id_old,
            ]
        )->first();

        $return_me = new  SpreadsheetCollection();

        /** @var Client $ClientObj */
        foreach ($ClientObj->properties as $PropertyObj)
        {
            $return_me[] = [
                'MASTER_PROPERTY_ID'        => $PropertyObj->property_id_old,
                'FK_CLIENT_ID'              => $PropertyObj->client->client_id_old,
                'WP_PROPERTY_ID'            => $PropertyObj->wp_property_id_old,
                'PROPERTY_CODE'             => $PropertyObj->property_code,
                'PROPERTY_NAME'             => $PropertyObj->name,
                'PROPERTY_PHYSICAL_ADDRESS' => $PropertyObj->street_address,
                'PROPERTY_CITY'             => $PropertyObj->city,
                'PROPERTY_STATE'            => $PropertyObj->state,
                'PROPERTY_ZIP'              => $PropertyObj->postal_code,
                'PROPERTY_COUNTRY'          => 'USA',
                'PROPERTY_LONGITUDE'        => $PropertyObj->longitude,
                'PROPERTY_LATITUDE'         => $PropertyObj->latitude,
                'ACTIVE_INACTIVE'           => $PropertyObj->active_status == Property::ACTIVE_STATUS_ACTIVE ? 1 : 0,
                'ACTIVE_INACTIVE_DATE'      => $PropertyObj->active_status_date,
                'PROPERTY_SQUARE_FOOTAGE'   => $PropertyObj->square_footage,
                'LOAD_FACTOR'               => $PropertyObj->load_factor_old,
                'PROPERTY_TYPE'             => isset($PropertyObj->assetType) ? $PropertyObj->assetType->asset_type_name : null,
                'LEASE_TYPE'                => $PropertyObj->lease_type,
                'YEAR_BUILT'                => $PropertyObj->year_built,
            ];
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'WaypointMasterBridgeController_property_details retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'WaypointMasterBridgeController_property_details'
        );
    }

    /**
     * Produces csv download of PROPERTY_CODE_MAPPING table
     * CREATE TABLE 'PROPERTY_CODE_MAPPING' (
     * 'PROPERTY_CODE_MAPPING_ID' bigint(20) NOT NULL AUTO_INCREMENT,
     * 'FK_CLIENT_ID' bigint(20) NOT NULL,
     * 'PROPERTY_CODE' varchar(10) NOT NULL,
     * 'ORIGINAL_PROPERTY_CODE' varchar(10) NOT NULL,
     * 'PROPERTY_OWNED' decimal(5,2) DEFAULT NULL,
     * PRIMARY KEY ('PROPERTY_CODE_MAPPING_ID'),
     * KEY 'CLIENT_ID' ('FK_CLIENT_ID'),
     * KEY 'CLIENT_ID_PROPERTY_CODE' ('FK_CLIENT_ID','PROPERTY_CODE') USING BTREE,
     * KEY 'CLIENT_ID_ORIGINAL_PROPERTY_CODE' ('FK_CLIENT_ID','ORIGINAL_PROPERTY_CODE')
     * ) ENGINE=InnoDB AUTO_INCREMENT=3930 DEFAULT CHARSET=latin1;
     *
     * curl -X GET
     *      -H "Content-Type: application/json"
     *      -H "X-Authorization: xxxxxxxxxx"
     *      -H "Cache-Control: no-cache"
     *      "http://homestead.app/api/v1/waypoint_hermes_master_bridge/Root/waypointMasterBridge/clients/2/2/property_code_mapping"
     *
     * @param Request $RequestObj
     * @param integer $client_id_old
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index_property_code_mapping(Request $RequestObj, $client_id_old)
    {
        $this->ClientRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        $ClientObj = $this->ClientRepositoryObj->findWhere(
            [
                'client_id_old' => $client_id_old,
            ]
        )->first();

        $return_me = new  SpreadsheetCollection();

        /** @var Client $ClientObj */
        foreach ($ClientObj->properties as $PropertyObj)
        {
            /**
             * Remember that $PropertyObj->original_property_code is (potentially per Brian) a
             * comma seperated list of original_property_code's
             */
            $original_property_code_arr = explode(',', $PropertyObj->original_property_code);

            foreach ($original_property_code_arr as $original_property_code)
            {
                $return_me[] = [
                    'FK_CLIENT_ID'           => $PropertyObj->client->client_id_old,
                    'PROPERTY_CODE'          => $PropertyObj->property_code,
                    'ORIGINAL_PROPERTY_CODE' => $original_property_code,
                    'PROPERTY_OWNED'         => $PropertyObj->property_owned,
                ];
            }
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'property_code_mapping retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'property_code_mapping'
        );
    }

    /**
     * Produces csv download of PROPERTY_CODE_MAPPING table
     * CREATE TABLE `CLIENT_SFTP_DETAILS` (
     *  `CLIENT_SFTP_DETAILS_ID` int(11) NOT NULL AUTO_INCREMENT,
     *  `CLIENT_ID` int(11) NOT NULL,
     *  `HOST_NAME` varchar(255) NOT NULL,
     *  `USER_NAME` varchar(255) NOT NULL,
     *  `PASSWORD` varchar(255) NOT NULL,
     *      PRIMARY KEY (`CLIENT_SFTP_DETAILS_ID`)
     *  ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
     * ) ENGINE=InnoDB AUTO_INCREMENT=3930 DEFAULT CHARSET=latin1;
     *
     * curl -X GET
     *      -H "X-Authorization: xxxxxxxxxx"
     *      -H "Cache-Control: no-cache"
     *      "http://homestead.app//api/v1/waypointMasterBridge/Root/clients/client_sftp_details"
     *
     * @param Request $RequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index_client_sftp_details(Request $RequestObj)
    {
        $this->ClientRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        $return_me = new  SpreadsheetCollection();

        $client_id_old_de_duper_arr = [];

        /** @var Client $ClientObj */
        foreach ($this->ClientRepositoryObj->all() as $ClientObj)
        {
            if ( ! $ClientObj->client_id_old)
            {
                continue;
            }
            if (isset($client_id_old_de_duper_arr[$ClientObj->client_id_old]))
            {
                continue;
            }
            $return_me[] = [
                'CLIENT_ID' => $ClientObj->client_id_old,
                'HOST_NAME' => $ClientObj->sftp_host_name,
                'USER_NAME' => $ClientObj->sftp_user_name,
                'PASSWORD'  => $ClientObj->sftp_password,
            ];

            $client_id_old_de_duper_arr[$ClientObj->client_id_old] = true;
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'client_sftp_details retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'client_sftp_details'
        );
    }

    /**
     * Produces csv download of BOMA_COA_CODES table
     * CREATE TABLE 'BOMA_COA_CODES' (
     * 'BOMA_COA_CODES_ID' bigint(20) NOT NULL AUTO_INCREMENT,
     * 'FK_BOMA_CLIENT_ID' bigint(20) DEFAULT NULL,
     * 'BOMA_ACCOUNT_CODE' varchar(20) NOT NULL,
     * 'BOMA_ACCOUNT_NAME' varchar(100) NOT NULL,
     * 'BOMA_ACCOUNT_NAME_UPPER' varchar(100) NOT NULL,
     * 'BOMA_USAGE_TYPE' varchar(1) DEFAULT NULL,
     * 'BOMA_ACCOUNT_HEADER_1_CODE' varchar(20) DEFAULT NULL,
     * 'BOMA_ACCOUNT_HEADER_1_NAME' varchar(100) DEFAULT NULL,
     * 'BOMA_ACCOUNT_HEADER_2_CODE' varchar(20) DEFAULT NULL,
     * 'BOMA_ACCOUNT_HEADER_2_NAME' varchar(100) DEFAULT NULL,
     * 'BOMA_ACCOUNT_HEADER_3_CODE' varchar(20) DEFAULT NULL,
     * 'BOMA_ACCOUNT_HEADER_3_NAME' varchar(100) DEFAULT NULL,
     * 'BOMA_ACCOUNT_HEADER_4_CODE' varchar(20) DEFAULT NULL,
     * 'BOMA_ACCOUNT_HEADER_4_NAME' varchar(100) DEFAULT NULL,
     * 'BOMA_ACCOUNT_HEADER_5_CODE' varchar(20) DEFAULT NULL,
     * 'BOMA_ACCOUNT_HEADER_5_NAME' varchar(100) DEFAULT NULL,
     * 'BOMA_ACCOUNT_HEADER_6_CODE' varchar(20) DEFAULT NULL,
     * 'BOMA_ACCOUNT_HEADER_6_NAME' varchar(100) DEFAULT NULL,
     * 'BOMA_SORTING' int(11) DEFAULT NULL,
     * 'BOMA_DATA_SOURCE' int(11) DEFAULT NULL,
     * 'VERSION_NUM'' varchar(15) NOT NULL,
     * 'DATA_SOURCE' int(11) DEFAULT NULL,
     * PRIMARY KEY ('BOMA_COA_CODES_ID'),
     * KEY 'FK_VERSION_METADATA_NUM_idx' ('VERSION_NUM'),
     * CONSTRAINT 'FK_VERSION_METADATA_NUM' FOREIGN KEY ('VERSION_NUM') REFERENCES 'VERSION_METADATA' ('VERSION_NUM')
     * ) ENGINE=InnoDB AUTO_INCREMENT=599 DEFAULT CHARSET=latin1;
     *
     * curl -X GET
     *      -H "Content-Type: application/json"
     *      -H "X-Authorization: xxxxxxxxxx"
     *      -H "Cache-Control: no-cache"
     *      "http://homestead.app/api/v1/waypoint_hermes_master_bridge/Root/waypointMasterBridge/clients/2/2/boma_coa_codes"
     *
     * @param Request $RequestObj
     * @return \Illuminate\Http\JsonResponse|null|void
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index_boma_coa_codes(Request $RequestObj)
    {
        $this->ClientRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /** @var ReportTemplateRepository $ReportTemplateRepositoryObj */
        $ReportTemplateRepositoryObj = App::make(ReportTemplateRepository::class);

        /** @var ReportTemplate $ReportTemplateObj */
        $ReportTemplateObj = $ReportTemplateRepositoryObj
            ->with('reportTemplateAccountGroups')
            ->findWhere(
                [
                    'client_id'            => 1,
                    'report_template_name' => 'BOMA Chart of Accounts',
                ]
            )->first();
        $return_me         = new  SpreadsheetCollection();

        foreach ($ReportTemplateObj->reportTemplateAccountGroups as $ReportTemplateAccountGroupObj)
        {
            $return_me[] = [
                'BOMA_COA_CODES_ID'       => $ReportTemplateAccountGroupObj->id,
                'FK_BOMA_CLIENT_ID'       => 2,
                'BOMA_ACCOUNT_CODE'       => $ReportTemplateAccountGroupObj->report_template_account_group_code,
                'BOMA_ACCOUNT_NAME'       => $ReportTemplateAccountGroupObj->report_template_account_group_name,
                'BOMA_ACCOUNT_NAME_UPPER' => strtoupper($ReportTemplateAccountGroupObj->report_template_account_group_name),
                'VERSION_NUM'             => $ReportTemplateAccountGroupObj->version_num,
                'BOMA_USAGE_TYPE'         => $ReportTemplateAccountGroupObj->usage_type,
                'DATA_SOURCE'             => 1,

                "BOMA_ACCOUNT_HEADER_1_CODE" => $ReportTemplateAccountGroupObj->boma_account_header_1_code_old,
                "BOMA_ACCOUNT_HEADER_1_NAME" => $ReportTemplateAccountGroupObj->boma_account_header_1_name_old,
                "BOMA_ACCOUNT_HEADER_2_CODE" => $ReportTemplateAccountGroupObj->boma_account_header_2_code_old,
                "BOMA_ACCOUNT_HEADER_2_NAME" => $ReportTemplateAccountGroupObj->boma_account_header_2_name_old,
                "BOMA_ACCOUNT_HEADER_3_CODE" => $ReportTemplateAccountGroupObj->boma_account_header_3_code_old,
                "BOMA_ACCOUNT_HEADER_3_NAME" => $ReportTemplateAccountGroupObj->boma_account_header_3_name_old,
                "BOMA_ACCOUNT_HEADER_4_CODE" => $ReportTemplateAccountGroupObj->boma_account_header_4_code_old,
                "BOMA_ACCOUNT_HEADER_4_NAME" => $ReportTemplateAccountGroupObj->boma_account_header_4_name_old,
                "BOMA_ACCOUNT_HEADER_5_CODE" => $ReportTemplateAccountGroupObj->boma_account_header_5_code_old,
                "BOMA_ACCOUNT_HEADER_5_NAME" => $ReportTemplateAccountGroupObj->boma_account_header_5_name_old,
                "BOMA_ACCOUNT_HEADER_6_CODE" => $ReportTemplateAccountGroupObj->boma_account_header_6_code_old,
                "BOMA_ACCOUNT_HEADER_6_NAME" => $ReportTemplateAccountGroupObj->boma_account_header_6_name_old,
            ];
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'boma_coa_codes retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'boma_coa_codes'
        );
    }

    /**
     * Produces csv download of WAYPOINT_BOMA_COA_MAPPING table
     *
     * CREATE TABLE 'WAYPOINT_BOMA_COA_MAPPING' (
     * 'WAYPOINT_BOMA_ID' bigint(20) NOT NULL DEFAULT '0',
     * 'FK_WAYPOINT_ACCOUNT_CODES_ID' bigint(20) DEFAULT NULL,
     * 'FK_PROPERTY_ID' bigint(20) DEFAULT NULL,
     * 'ACCOUNT_CODE' varchar(20) DEFAULT NULL,
     * 'ACCOUNT_NAME' varchar(100) DEFAULT NULL,
     * 'FK_BOMA_COA_CODES_ID' bigint(20) DEFAULT NULL,
     * 'BOMA_ACCOUNT_CODE' varchar(20) DEFAULT NULL,
     * 'BOMA_ACCOUNT_NAME' varchar(100) DEFAULT NULL,
     * 'AMOUNT_SIGN' float(10,5) NOT NULL DEFAULT '1.00000'
     * ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
     *
     *
     * curl -X GET -H "Content-Type: application/json"
     *      -H "X-Authorization: xxxxxxxxxx"
     *      -H "Cache-Control: no-cache"
     *      "http://homestead.app/api/v1/waypoint_hermes_master_bridge/Root/waypointMasterBridge/clients/2/2/waypoint_boma_coa_mapping"
     *
     * @param Request $RequestObj
     * @param integer $client_id_old
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index_waypoint_boma_coa_mapping(Request $RequestObj, $client_id_old)
    {
        $this->ClientRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        $ClientObjArr = $this->ClientRepositoryObj
            ->with('properties.nativeCoas.nativeAccounts.nativeAccountType.nativeAccountTypeTrailers')
            ->with('reportTemplates.reportTemplateAccountGroups.nativeAccounts.nativeAccountType.nativeAccountTypeTrailers')
            ->with('reportTemplates.reportTemplateAccountGroups.reportTemplateMappings.reportTemplateAccountGroup.reportTemplate')
            ->with('reportTemplates.reportTemplateAccountGroups.reportTemplateMappings.reportTemplateAccountGroup.reportTemplateAccountGroupParent')
            ->with('reportTemplates.reportTemplateAccountGroups.reportTemplateMappings.reportTemplateAccountGroup.nativeAccountType.nativeAccountTypeTrailers')
            ->with('reportTemplates.reportTemplateAccountGroups.reportTemplateMappings.nativeAccount.nativeAccountType.nativeAccountTypeTrailers')
            ->with('reportTemplates.reportTemplateAccountGroups.reportTemplateMappings.reportTemplateAccountGroup.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent')
            ->with('reportTemplates.reportTemplateAccountGroups.reportTemplateAccountGroupParent')
            ->with('reportTemplates.reportTemplateAccountGroups.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent.reportTemplateAccountGroupParent')
            ->findWhere(
                [
                    'client_id_old' => $client_id_old,
                ]
            );

        $return_me = new  SpreadsheetCollection();

        /** @var Client $ClientObj */
        foreach ($ClientObjArr as $ClientObj)
        {
            /** @var ReportTemplate $BomaReportTemplateObj */
            $BomaReportTemplateObj       =
                $ClientObj
                    ->reportTemplates
                    ->filter(
                        function ($ReportTemplateObj)
                        {
                            return $ReportTemplateObj->is_boma_report_template;
                        }
                    )
                    ->first();
            $ReportTemplateMappingObjArr = $BomaReportTemplateObj->reportTemplateAccountGroups->flatMap(
                function (ReportTemplateAccountGroup $ReportTemplateAccountGroupobj, $key)
                {
                    return $ReportTemplateAccountGroupobj->reportTemplateMappings;
                }
            );

            /** @var Property $PropertyObj */
            foreach ($ClientObj->properties as $PropertyObj)
            {
                foreach ($PropertyObj->nativeCoas as $NativeCoaObj)
                {
                    /** @var NativeAccount $NativeAccountObj */
                    foreach ($NativeCoaObj->nativeAccounts as $NativeAccountObj)
                    {
                        if ($NativeAccountObj->is_category)
                        {
                            continue;
                        }

                        /** @var ReportTemplateMapping $ReportTemplateMappingObj */
                        $ReportTemplateMappingObj = $ReportTemplateMappingObjArr->filter(
                            function ($ReportTemplateMappingObj) use ($NativeAccountObj)
                            {
                                return $ReportTemplateMappingObj->native_account_id == $NativeAccountObj->id;
                            }
                        )->first();

                        if ( ! $ReportTemplateMappingObj)
                        {
                            /*****
                             * READ ME - LOOK AT THIS - I MEAN YOU - Yea YOU
                             * if your unit test is getting here, it's because your data, Waypoint Commercial or Waypoint Commercial I'll bet,
                             * had a native code that is not mapped to a report_template_account_group
                             * READ ME - LOOK AT THIS - I MEAN YOU - Yea YOU
                             */
                            continue;
                        }

                        if ( ! $ReportTemplateMappingObj->reportTemplateAccountGroup->reportTemplate->is_boma_report_template)
                        {
                            continue;
                        }

                        /** @var NativeAccountTypeTrailer $NativeAccountTypeTrailerObj */
                        $NativeAccountTypeTrailerObj = $ReportTemplateMappingObj->nativeAccount->getCoeffients($PropertyObj->id);

                        $deprecated_waypoint_code = $ReportTemplateMappingObj->reportTemplateAccountGroup->deprecated_waypoint_code;
                        $fk_boma_coa_codes_id     = null;
                        if ($deprecated_waypoint_code)
                        {
                            if (array_key_exists($deprecated_waypoint_code, self::BOMA_CODES_TO_TITAN_BOMA_CODE_IDS))
                            {
                                $fk_boma_coa_codes_id = self::BOMA_CODES_TO_TITAN_BOMA_CODE_IDS[$deprecated_waypoint_code];
                            }
                        }

                        $return_me[] = [
                            'WAYPOINT_BOMA_ID'             => $ReportTemplateMappingObj->native_account_id,
                            'FK_WAYPOINT_ACCOUNT_CODES_ID' => $ReportTemplateMappingObj->report_template_account_group_id,
                            'FK_PROPERTY_ID'               => $PropertyObj->property_id_old,
                            'ACCOUNT_CODE'                 => $NativeAccountObj->native_account_code,
                            'ACCOUNT_NAME'                 => $NativeAccountObj->native_account_name,
                            'FK_BOMA_COA_CODES_ID'         => $fk_boma_coa_codes_id,
                            'BOMA_ACCOUNT_CODE'            => $deprecated_waypoint_code,
                            'BOMA_ACCOUNT_NAME'            => $ReportTemplateMappingObj->reportTemplateAccountGroup->report_template_account_group_name,
                            'AMOUNT_SIGN'                  => $NativeAccountTypeTrailerObj->actual_coefficient,
                            'AMOUNT_SIGN_1'                => $NativeAccountTypeTrailerObj->budgeted_coefficient,
                        ];

                        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
                        foreach ($ReportTemplateMappingObj->reportTemplateAccountGroup->getLineage() as $ReportTemplateAccountGroupObj)
                        {
                            $deprecated_waypoint_code = $ReportTemplateAccountGroupObj->deprecated_waypoint_code;
                            $fk_boma_coa_codes_id     = null;
                            if ($deprecated_waypoint_code)
                            {
                                if (array_key_exists($deprecated_waypoint_code, self::BOMA_CODES_TO_TITAN_BOMA_CODE_IDS))
                                {
                                    $fk_boma_coa_codes_id = self::BOMA_CODES_TO_TITAN_BOMA_CODE_IDS[$deprecated_waypoint_code];
                                }
                            }

                            $return_me[] = [
                                'WAYPOINT_BOMA_ID'             => $ReportTemplateMappingObj->native_account_id,
                                'FK_WAYPOINT_ACCOUNT_CODES_ID' => $ReportTemplateAccountGroupObj->id,
                                'FK_PROPERTY_ID'               => $PropertyObj->property_id_old,
                                'ACCOUNT_CODE'                 => $NativeAccountObj->native_account_code,
                                'ACCOUNT_NAME'                 => $NativeAccountObj->native_account_name,
                                'FK_BOMA_COA_CODES_ID'         => $fk_boma_coa_codes_id,
                                'BOMA_ACCOUNT_CODE'            => $deprecated_waypoint_code,
                                'BOMA_ACCOUNT_NAME'            => $ReportTemplateAccountGroupObj->report_template_account_group_name,
                                'AMOUNT_SIGN'                  => $NativeAccountTypeTrailerObj->actual_coefficient,
                                'AMOUNT_SIGN_1'                => $NativeAccountTypeTrailerObj->budgeted_coefficient,
                            ];
                        }
                    }
                }
            }
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'property_codes retrieved successfully');
        }

        return $return_me->toCSVReportGeneric(
            'property_codes'
        );
    }

    const BOMA_CODES_TO_TITAN_BOMA_CODE_IDS = [
        "30_h1"     => 1,
        "30_000_h2" => 2,
        "30 000"    => 3,
        "30 100"    => 4,
        "30 200"    => 5,
        "30_210"    => 6,
        "30_220"    => 7,
        "30_230"    => 8,
        "30 300"    => 9,
        "30_310"    => 10,
        "30_320"    => 11,
        "30_330"    => 12,
        "30_340"    => 13,
        "30_341"    => 14,
        "30_342"    => 15,
        "30_343"    => 16,
        "30_344"    => 17,
        "30 400"    => 18,
        "30 500"    => 19,
        "30_510"    => 20,
        "30 600"    => 21,
        "30 700"    => 22,
        "31 000"    => 23,
        "31 100"    => 24,
        "31 150"    => 25,
        "31 200"    => 26,
        "31_210"    => 27,
        "31_220"    => 28,
        "31_230"    => 29,
        "31_240"    => 30,
        "31 300"    => 31,
        "31 400"    => 32,
        "31 500"    => 33,
        "31 600"    => 34,
        "31 700"    => 35,
        "31 800"    => 36,
        "31 900"    => 37,
        "32 000"    => 38,
        "32 100"    => 39,
        "32 200"    => 40,
        "32 300"    => 41,
        "32 400"    => 42,
        "33 000"    => 43,
        "33 100"    => 44,
        "33 200"    => 45,
        "33 300"    => 46,
        "33 400"    => 47,
        "33 500"    => 48,
        "33 600"    => 49,
        "33 700"    => 50,
        "34_000_h2" => 51,
        "34 000"    => 52,
        "34 100"    => 53,
        "34_110"    => 54,
        "34_120"    => 55,
        "34_130"    => 56,
        "34 200"    => 57,
        "34_210"    => 58,
        "34_220"    => 59,
        "34 300"    => 60,
        "34_310"    => 61,
        "34_320"    => 62,
        "34_330"    => 63,
        "34_340"    => 64,
        "34_350"    => 65,
        "34 400"    => 66,
        "34_410"    => 67,
        "34_420"    => 68,
        "35_000_h2" => 69,
        "35 000"    => 70,
        "35 100"    => 71,
        "35 200"    => 72,
        "35 300"    => 73,
        "35 400"    => 74,
        "35 500"    => 75,
        "35 600"    => 76,
        "35 700"    => 77,
        "35 800"    => 78,
        "35 850"    => 79,
        "35 900"    => 80,
        "40_h1"     => 81,
        "40_000_h2" => 82,
        "40 000"    => 83,
        "40 100"    => 84,
        "40_110"    => 85,
        "40_111"    => 86,
        "40_112"    => 87,
        "40_113"    => 88,
        "40_114"    => 89,
        "40_115"    => 90,
        "40_120"    => 91,
        "40_121"    => 92,
        "40_122"    => 93,
        "40_123"    => 94,
        "40_124"    => 95,
        "40_130"    => 96,
        "40_131"    => 97,
        "40_132"    => 98,
        "40_133"    => 99,
        "40_134"    => 100,
        "40_135"    => 101,
        "40_136"    => 102,
        "40_137"    => 103,
        "40_138"    => 104,
        "40_139"    => 105,
        "40 200"    => 106,
        "40_210"    => 107,
        "40_220"    => 108,
        "40 300"    => 109,
        "40_310"    => 110,
        "40_320"    => 111,
        "40_330"    => 112,
        "40_340"    => 113,
        "40_350"    => 114,
        "40_360"    => 115,
        "40 400"    => 116,
        "40_410"    => 117,
        "40_420"    => 118,
        "40_430"    => 119,
        "40_440"    => 120,
        "40_450"    => 121,
        "40_451"    => 122,
        "40_452"    => 123,
        "40_453"    => 124,
        "40 500"    => 125,
        "40_510"    => 126,
        "40_520"    => 127,
        "40_530"    => 128,
        "40_540"    => 129,
        "41 000"    => 130,
        "41 100"    => 131,
        "41_110"    => 132,
        "41_111"    => 133,
        "41_112"    => 134,
        "41_113"    => 135,
        "41_114"    => 136,
        "41_115"    => 137,
        "41_120"    => 138,
        "41_121"    => 139,
        "41_122"    => 140,
        "41_123"    => 141,
        "41_124"    => 142,
        "41_130"    => 143,
        "41_131"    => 144,
        "41_132"    => 145,
        "41_133"    => 146,
        "41_134"    => 147,
        "41_135"    => 148,
        "41_136"    => 149,
        "41_137"    => 150,
        "41_138"    => 151,
        "41_139"    => 152,
        "41 150"    => 153,
        "41 200"    => 154,
        "41_210"    => 155,
        "41_220"    => 156,
        "41_230"    => 157,
        "41_240"    => 158,
        "41_250"    => 159,
        "41_260"    => 160,
        "41 300"    => 161,
        "41_310"    => 162,
        "41_320"    => 163,
        "41_330"    => 164,
        "41_340"    => 165,
        "41_350"    => 166,
        "41_360"    => 167,
        "41_370"    => 168,
        "41_380"    => 169,
        "41_390"    => 170,
        "41_391"    => 171,
        "41 400"    => 172,
        "41_410"    => 173,
        "41_420"    => 174,
        "41_430"    => 175,
        "41_440"    => 176,
        "41_450"    => 177,
        "41_460"    => 178,
        "41 500"    => 179,
        "41_510"    => 180,
        "41_520"    => 181,
        "41_530"    => 182,
        "41 600"    => 183,
        "41_610"    => 184,
        "41_620"    => 185,
        "41_630"    => 186,
        "41 700"    => 187,
        "41_710"    => 188,
        "41_720"    => 189,
        "41_730"    => 190,
        "41_740"    => 191,
        "41_750"    => 192,
        "41 800"    => 193,
        "41_810"    => 194,
        "41_820"    => 195,
        "41_830"    => 196,
        "41_840"    => 197,
        "41_841"    => 198,
        "41_860"    => 199,
        "41_870"    => 200,
        "41_880"    => 201,
        "41_881"    => 202,
        "41_890"    => 203,
        "41_891"    => 204,
        "41 850"    => 205,
        "41_851"    => 206,
        "41_852"    => 207,
        "41_853"    => 208,
        "41_854"    => 209,
        "41_855"    => 210,
        "41_856"    => 211,
        "41 900"    => 212,
        "41_910"    => 213,
        "41_920"    => 214,
        "41_930"    => 215,
        "42 000"    => 216,
        "42 100"    => 217,
        "42_101"    => 218,
        "42_102"    => 219,
        "42 200"    => 220,
        "42_201"    => 221,
        "42_202"    => 222,
        "42 300"    => 223,
        "42 400"    => 224,
        "42 500"    => 225,
        "42 600"    => 226,
        "42 700"    => 227,
        "42 800"    => 228,
        "42 900"    => 229,
        "43 000"    => 230,
        "43 100"    => 231,
        "43_110"    => 232,
        "43_111"    => 233,
        "43_112"    => 234,
        "43_113"    => 235,
        "43_114"    => 236,
        "43_115"    => 237,
        "43_116"    => 238,
        "43_120"    => 239,
        "43_121"    => 240,
        "43_122"    => 241,
        "43_123"    => 242,
        "43_124"    => 243,
        "43_130"    => 244,
        "43_131"    => 245,
        "43_132"    => 246,
        "43_133"    => 247,
        "43_134"    => 248,
        "43_135"    => 249,
        "43_136"    => 250,
        "43_137"    => 251,
        "43_138"    => 252,
        "43_139"    => 253,
        "43_140"    => 254,
        "43_141"    => 255,
        "43_142"    => 256,
        "43_143"    => 257,
        "43 200"    => 258,
        "43_210"    => 259,
        "43_211"    => 260,
        "43_212"    => 261,
        "43_213"    => 262,
        "43_214"    => 263,
        "43_215"    => 264,
        "43_216"    => 265,
        "43_217"    => 266,
        "43_218"    => 267,
        "43_219"    => 268,
        "43_220"    => 269,
        "43_221"    => 270,
        "43_222"    => 271,
        "43_223"    => 272,
        "43_224"    => 273,
        "43_225"    => 274,
        "43_226"    => 275,
        "43_227"    => 276,
        "43_228"    => 277,
        "43_229"    => 278,
        "43_230"    => 279,
        "43_231"    => 280,
        "43_232"    => 281,
        "43_233"    => 282,
        "43_234"    => 283,
        "43_235"    => 284,
        "43_236"    => 285,
        "43_237"    => 286,
        "43_238"    => 287,
        "43 300"    => 288,
        "43_310"    => 289,
        "43_320"    => 290,
        "43_330"    => 291,
        "43 400"    => 292,
        "43_410"    => 293,
        "43_420"    => 294,
        "43_430"    => 295,
        "44 000"    => 296,
        "44 100"    => 297,
        "44_110"    => 298,
        "44_111"    => 299,
        "44_112"    => 300,
        "44_113"    => 301,
        "44_114"    => 302,
        "44_115"    => 303,
        "44_120"    => 304,
        "44_121"    => 305,
        "44_122"    => 306,
        "44_123"    => 307,
        "44_124"    => 308,
        "44_130"    => 309,
        "44_131"    => 310,
        "44_132"    => 311,
        "44_133"    => 312,
        "44_134"    => 313,
        "44_135"    => 314,
        "44_136"    => 315,
        "44_137"    => 316,
        "44_138"    => 317,
        "44_139"    => 318,
        "44 200"    => 319,
        "44 300"    => 320,
        "44 400"    => 321,
        "44_410"    => 322,
        "44_420"    => 323,
        "44_430"    => 324,
        "44_440"    => 325,
        "44_450"    => 326,
        "44_460"    => 327,
        "44 500"    => 328,
        "44 600"    => 329,
        "45 000"    => 330,
        "45 100"    => 331,
        "45_110"    => 332,
        "45_111"    => 333,
        "45_112"    => 334,
        "45_113"    => 335,
        "45_114"    => 336,
        "45_115"    => 337,
        "45_120"    => 338,
        "45_121"    => 339,
        "45_122"    => 340,
        "45_123"    => 341,
        "45_124"    => 342,
        "45_130"    => 343,
        "45_131"    => 344,
        "45_132"    => 345,
        "45_133"    => 346,
        "45_134"    => 347,
        "45_135"    => 348,
        "45_136"    => 349,
        "45_137"    => 350,
        "45_138"    => 351,
        "45_139"    => 352,
        "45 200"    => 353,
        "45 300"    => 354,
        "45_310"    => 355,
        "45_320"    => 356,
        "45_330"    => 357,
        "45_340"    => 358,
        "45_350"    => 359,
        "45_360"    => 360,
        "45 400"    => 361,
        "45_410"    => 362,
        "45_420"    => 363,
        "45_430"    => 364,
        "45_440"    => 365,
        "45_450"    => 366,
        "45_460"    => 367,
        "45_470"    => 368,
        "45_480"    => 369,
        "45 500"    => 370,
        "45_510"    => 371,
        "45_520"    => 372,
        "45_530"    => 373,
        "45_540"    => 374,
        "45_550"    => 375,
        "45_560"    => 376,
        "45_570"    => 377,
        "45_580"    => 378,
        "45_590"    => 379,
        "45_591"    => 380,
        "45 600"    => 381,
        "45_610"    => 382,
        "45_620"    => 383,
        "45_630"    => 384,
        "45 700"    => 385,
        "45_710"    => 386,
        "45_720"    => 387,
        "46 000"    => 388,
        "46 100"    => 389,
        "46_110"    => 390,
        "46_111"    => 391,
        "46_112"    => 392,
        "46_113"    => 393,
        "46_114"    => 394,
        "46_115"    => 395,
        "46_116"    => 396,
        "46_120"    => 397,
        "46_121"    => 398,
        "46_122"    => 399,
        "46_123"    => 400,
        "46_124"    => 401,
        "46_130"    => 402,
        "46_131"    => 403,
        "46_132"    => 404,
        "46_133"    => 405,
        "46_134"    => 406,
        "46_135"    => 407,
        "46_136"    => 408,
        "46_137"    => 409,
        "46_138"    => 410,
        "46_139"    => 411,
        "46_140"    => 412,
        "46_141"    => 413,
        "46_142"    => 414,
        "46_150"    => 415,
        "46_151"    => 416,
        "46_152"    => 417,
        "46_153"    => 418,
        "46_154"    => 419,
        "46 200"    => 420,
        "46_210"    => 421,
        "46_220"    => 422,
        "46 300"    => 423,
        "46 400"    => 424,
        "46_410"    => 425,
        "46_420"    => 426,
        "47 000"    => 427,
        "47 100"    => 428,
        "47 200"    => 429,
        "47 300"    => 430,
        "47 400"    => 431,
        "47_410"    => 432,
        "47_420"    => 433,
        "47_430"    => 434,
        "47_440"    => 435,
        "47 500"    => 436,
        "50_000_h2" => 437,
        "50 000"    => 438,
        "50 100"    => 439,
        "50_110"    => 440,
        "50_111"    => 441,
        "50_112"    => 442,
        "50_113"    => 443,
        "50_114"    => 444,
        "50_115"    => 445,
        "50_120"    => 446,
        "50_121"    => 447,
        "50_122"    => 448,
        "50_123"    => 449,
        "50_124"    => 450,
        "50_130"    => 451,
        "50_131"    => 452,
        "50_132"    => 453,
        "50_133"    => 454,
        "50_134"    => 455,
        "50_135"    => 456,
        "50_136"    => 457,
        "50_137"    => 458,
        "50_138"    => 459,
        "50_139"    => 460,
        "50 160"    => 461,
        "50 200"    => 462,
        "50 300"    => 463,
        "50 400"    => 464,
        "50 500"    => 465,
        "50 600"    => 466,
        "51 000"    => 467,
        "51 100"    => 468,
        "51 200"    => 469,
        "51 300"    => 470,
        "51 400"    => 471,
        "51 500"    => 472,
        "60_000_h2" => 473,
        "60 000"    => 474,
        "60 100"    => 475,
        "60 200"    => 476,
        "60 300"    => 477,
        "60 400"    => 478,
        "61 000"    => 479,
        "61 100"    => 480,
        "61 200"    => 481,
        "61 300"    => 482,
        "61_310"    => 483,
        "61_320"    => 484,
        "61 400"    => 485,
        "61 500"    => 486,
        "61_510"    => 487,
        "61_520"    => 488,
        "61_530"    => 489,
        "61_540"    => 490,
        "61_550"    => 491,
        "61 700"    => 492,
        "61_710"    => 493,
        "61_720"    => 494,
        "61_730"    => 495,
        "61_740"    => 496,
        "61 800"    => 497,
        "61 900"    => 498,
        "70_000_h2" => 499,
        "70_000_h3" => 500,
        "70 000"    => 501,
        "71_000_h3" => 502,
        "71 000"    => 503,
        "72_000_h3" => 504,
        "72 000"    => 505,
        "73_000_h3" => 506,
        "73 000"    => 507,
        "74_000_h3" => 508,
        "74 000"    => 509,
        "74 100"    => 510,
        "74 200"    => 511,
        "74 300"    => 512,
        "74 400"    => 513,
        "79_000_h3" => 514,
        "79 000"    => 515,
        "80_000"    => 516,
        "80_050"    => 517,
        "80_099"    => 518,
        "81_000"    => 519,
        "81_100"    => 520,
        "81_101"    => 521,
        "81_200"    => 522,
        "81_300"    => 523,
        "81_400"    => 524,
        "81_600"    => 525,
        "81_700"    => 526,
        "81_900"    => 527,
        "82_000"    => 528,
        "82_100"    => 529,
        "82_101"    => 530,
        "82_200"    => 531,
        "82_300"    => 532,
        "82_400"    => 533,
        "82_600"    => 534,
        "82_700"    => 535,
        "82_900"    => 536,
        "83_000"    => 537,
        "83_100"    => 538,
        "84_000"    => 539,
        "84_100"    => 540,
        "84_101"    => 541,
        "84_102"    => 542,
        "84_120"    => 543,
        "84_121"    => 544,
        "84_122"    => 545,
        "84_140"    => 546,
        "84_141"    => 547,
        "84_142"    => 548,
        "84_200"    => 549,
        "84_201"    => 550,
        "84_202"    => 551,
        "84_220"    => 552,
        "84_221"    => 553,
        "84_222"    => 554,
        "84_240"    => 555,
        "84_241"    => 556,
        "84_242"    => 557,
        "85_110"    => 558,
        "85_120"    => 559,
        "85_130"    => 560,
        "84_150"    => 571,
        "84_160"    => 572,
        "84_170"    => 573,
        "84_600"    => 574,
        "84_620"    => 575,
        "84_640"    => 576,
        "84_110"    => 577,
        "84_700"    => 578,
        "84_720"    => 579,
        "84_740"    => 580,
        "44_900"    => 581,
        "51_600"    => 582,
        "51_700"    => 583,
        "51_800"    => 584,
        "51_900"    => 585,
        "51_910"    => 586,
        "51_920"    => 587,
        "51_930"    => 588,
        "61_330"    => 589,
        "61_340"    => 590,
        "61_350"    => 591,
        "80_h1"     => 598,
    ];

    /**
     * Produces csv download of WP_ASSET_TYPE table
     *
     * CREATE TABLE `WP_ASSET_TYPE` (
     *  `WP_ASSET_TYPE_ID` int(11) NOT NULL AUTO_INCREMENT,
     *  `WP_ASSET_TYPE_VALUE` varchar(100) DEFAULT NULL,
     *      PRIMARY KEY (`WP_ASSET_TYPE_ID`),
     *      KEY `index_WP_ASSET_TYPE_VALUE` (`WP_ASSET_TYPE_VALUE`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;
     *
     * curl -X GET -H "Content-Type: application/json"
     *      -H "X-Authorization: xxxxxxxxxx"
     *      -H "Cache-Control: no-cache"
     *      "http://homestead.app/api/v1/waypoint_hermes_master_bridge/Root/waypointMasterBridge/clients/wp_asset_type"
     *
     * @param Request $RequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function index_wp_asset_type(Request $RequestObj)
    {
        $json_str          = file_get_contents(resource_path('conversion_data/WP_ASSET_TYPE.txt.json'));
        $wp_asset_type_arr = json_decode($json_str, true);

        $return_me = new  SpreadsheetCollection();

        foreach ($wp_asset_type_arr as $wp_asset_type)
        {
            $return_me[] = [
                'WP_ASSET_TYPE_ID'    => $wp_asset_type['WP_ASSET_TYPE_ID'],
                'WP_ASSET_TYPE_VALUE' => $wp_asset_type['WP_ASSET_TYPE_VALUE'],
            ];
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'wp_asset_type retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'boma_coa_codes'
        );
    }

    /**
     * @param Request $RequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function index_version_metadata(Request $RequestObj)
    {
        $json_str             = file_get_contents(resource_path('conversion_data/VERSION_METADATA.txt.json'));
        $version_metadata_arr = json_decode($json_str, true);

        $return_me = new  SpreadsheetCollection();

        foreach ($version_metadata_arr as $wp_asset_type)
        {
            $return_me[] = [
                'VERSION_NUM'   => $wp_asset_type['VERSION_NUM'],
                'METADATA_NAME' => $wp_asset_type['METADATA_NAME'],
                'RELEASE_DATE'  => $wp_asset_type['RELEASE_DATE'],
                'RELEASE_NOTES' => $wp_asset_type['RELEASE_NOTES'],
                'CREATE_ON'     => $wp_asset_type['CREATE_ON'],
                'MODIFIED_ON'   => $wp_asset_type['MODIFIED_ON'],
            ];
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'version_metadata retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'version_metadata'
        );
    }

    /**
     * @param Request $RequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function index_column_datatypes(Request $RequestObj)
    {
        $json_str             = file_get_contents(resource_path('conversion_data/COLUMN_DATATYPES.txt.json'));
        $column_datatypes_arr = json_decode($json_str, true);

        $return_me = new  SpreadsheetCollection();

        foreach ($column_datatypes_arr as $column_datatypes)
        {
            $return_me[] = [
                'COLUMN_DATATYPES_ID' => $column_datatypes['COLUMN_DATATYPES_ID'],
                'COLUMN_NAME_TEXT'    => $column_datatypes['COLUMN_NAME_TEXT'],
                'DATA_TYPE_VALUES'    => $column_datatypes['DATA_TYPE_VALUES'],
            ];
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'column_datatypes retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'column_datatypes'
        );
    }

    /**
     * Produces csv download of WP_ASSET_TYPE table
     *
     * CREATE TABLE `WAYPOINT_ACCOUNT_CODES` (
     *  `WAYPOINT_ACCOUNT_CODES_ID` bigint(20) NOT NULL AUTO_INCREMENT,
     * `FK_ACCOUNT_CLIENT_ID` bigint(20) DEFAULT NULL,
     * `FK_PROPERTY_ID` bigint(20) NOT NULL DEFAULT '0',
     * `PROPERTY_CODE` varchar(20) DEFAULT '0',
     * `ACCOUNT_CODE` varchar(20) NOT NULL,
     * `ACCOUNT_NAME` varchar(100) DEFAULT NULL,
     * `ACCOUNT_NAME_UPPER` varchar(100) DEFAULT NULL,
     * `USAGE_TYPE` varchar(1) DEFAULT NULL,
     * `ACCOUNT_HEADER_CODE` varchar(20) DEFAULT NULL,
     * `ACCOUNT_HEADER_NAME` varchar(100) DEFAULT NULL,
     * `DATA_SOURCE` int(11) DEFAULT NULL,
     * PRIMARY KEY (`WAYPOINT_ACCOUNT_CODES_ID`),
     * KEY `ACCOUNT_CODE_PROPERTY_CODE` (`ACCOUNT_CODE`,`PROPERTY_CODE`),
     * KEY `ACCOUNT_CODE` (`ACCOUNT_CODE`)
     * ) ENGINE=InnoDB AUTO_INCREMENT=85642 DEFAULT CHARSET=latin1;
     *
     * curl -X GET -H "Content-Type: application/json"
     *      -H "X-Authorization: xxxxxxxxxx"
     *      -H "Cache-Control: no-cache"
     *      "http://homestead.app/api/v1/waypoint_hermes_master_bridge/Root/waypointMasterBridge/clients/wp_asset_type"
     *
     * @param Request $RequestObj
     * @param integer $client_id_old
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index_waypoint_account_codes(Request $RequestObj, $client_id_old)
    {
        $this->ClientRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));
        /** @var PropertyNativeCoaRepository $PropertyNativeCoaRepositoryObj */
        $PropertyNativeCoaRepositoryObj = App::make(PropertyNativeCoaRepository::class);

        $return_me = new  SpreadsheetCollection();
        $ClientObj = $this->ClientRepositoryObj
            ->with('properties')
            ->findWhere(
                [
                    'client_id_old' => $client_id_old,
                ]
            )->first();
        foreach ($ClientObj->properties as $PropertyObj)
        {
            /** @var PropertyNativeCoa $PropertyNativeCoaObj */
            $PropertyNativeCoaObjArr = $PropertyNativeCoaRepositoryObj
                ->with('nativeCoa.nativeAccounts')
                ->findWhere(
                    [
                        'property_id' => $PropertyObj->id,
                    ]
                );

            foreach ($PropertyNativeCoaObjArr as $PropertyNativeCoaObj)
            {
                /** @var NativeAccount $NativeAccountObj */
                foreach ($PropertyNativeCoaObj->nativeCoa->nativeAccounts as $NativeAccountObj)
                {
                    if ($NativeAccountObj->is_category)
                    {
                        continue;
                    }

                    $return_me[] = [
                        'FK_ACCOUNT_CLIENT_ID' => $ClientObj->client_id_old,
                        'FK_PROPERTY_ID'       => $PropertyObj->property_id_old,
                        'PROPERTY_CODE'        => $PropertyObj->property_code,
                        'ACCOUNT_CODE'         => $NativeAccountObj->native_account_code,
                        'ACCOUNT_NAME'         => $NativeAccountObj->native_account_name,
                        'ACCOUNT_NAME_UPPER'   => strtoupper($NativeAccountObj->native_account_name),
                        'USAGE_TYPE'           => 'C',
                    ];
                }
            }
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'property_codes retrieved successfully');
        }

        return $return_me->toCSVReportGeneric(
            'property_codes'
        );
    }

    /**
     * Produces csv download of WP_ASSET_TYPE table
     *
     * CREATE TABLE `OCCUPANCY_LEASE_TYPE_D` (
     * `LEASE_TYPE` varchar(100) NOT NULL DEFAULT 'null',
     * `OCCUPIED_FACTOR` decimal(10,0) NOT NULL DEFAULT '0',
     * `RENTABLE_FACTOR` decimal(10,0) NOT NULL DEFAULT '1'
     * ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
     *
     * curl -X GET
     *      -H "Content-Type: application/json"
     *      -H "X-Authorization: xxxxxxxxxx"
     *      -H "Cache-Control: no-cache"
     *      "http://homestead.app/api/v1/waypointMasterBridge/Root/clients/2/2/occupancy_lease_type_d"
     *
     * @param Request $RequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function index_occupancy_lease_type_d(Request $RequestObj)
    {
        $json_str                   = file_get_contents(resource_path('conversion_data/OCCUPANCY_LEASE_TYPE_D.txt.json'));
        $occupancy_lease_type_d_arr = json_decode($json_str, true);

        $return_me = new  SpreadsheetCollection();

        foreach ($occupancy_lease_type_d_arr as $occupancy_lease_type_d)
        {
            $return_me[] = [
                'LEASE_TYPE'      => $occupancy_lease_type_d['LEASE_TYPE'],
                'OCCUPIED_FACTOR' => $occupancy_lease_type_d['OCCUPIED_FACTOR'],
                'RENTABLE_FACTOR' => $occupancy_lease_type_d['RENTABLE_FACTOR'],
            ];
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'occupancy_lease_type_d retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'occupancy_lease_type_d'
        );
    }

    /**
     * @param Request $RequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index_client_detail_map(Request $RequestObj)
    {
        $this->ClientRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /**
         * NOTE NOTE NOTE
         * pert HER-1337, only pass data for a single client with client_id_old
         * @var Collection $ClientObjArr
         */
        $ClientObjArr               = $this->ClientRepositoryObj->findWhere([['client_id_old', '<>', null]]);
        $return_me                  = new  SpreadsheetCollection();
        $client_id_old_de_duper_arr = [];
        /** @var Client $ClientObj */
        foreach ($ClientObjArr as $ClientObj)
        {
            if (isset($client_id_old_de_duper_arr[$ClientObj->client_id_old]))
            {
                continue;
            }
            $return_me[]                                           = [
                'id'               => $ClientObj->id,
                'client_id_old'    => $ClientObj->client_id_old,
                'name'             => $ClientObj->name,
                'display_name_old' => $ClientObj->display_name_old,

                'STAGING_DB'     => 'waypoint_staging_' . $ClientObj->client_id_old,
                'RAW_UTILITY_DB' => 'waypoint_utilities_raw_' . $ClientObj->client_id_old,
                'LEDGER_DB'      => 'waypoint_ledger_' . $ClientObj->client_id_old,
                'UTILITY_DB'     => 'waypoint_utilities_' . $ClientObj->client_id_old,
                'CLIENT_CODE'    => $ClientObj->client_code,
            ];
            $client_id_old_de_duper_arr[$ClientObj->client_id_old] = true;
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'client_detail retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'client_detail'
        );
    }

    /**
     * @param Request $RequestObj
     * @param $client_id_old
     * @return \Illuminate\Http\JsonResponse|null|void
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index_report_templates(Request $RequestObj, $client_id_old)
    {
        $this->ClientRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        $ClientObj = $this->ClientRepositoryObj
            ->with('reportTemplates')
            ->findWhere(
                [
                    'client_id_old' => $client_id_old,
                ]
            )->first();
        /**
         * we need to 'toArray() this because we are not returning
         * standard objects
         * @var SpreadsheetCollection $return_me
         */
        $return_me = collect_waypoint_spreadsheet(
            $ClientObj->reportTemplates->map(
                function ($ReportTemplateObj) use ($ClientObj)
                {
                    return [
                        'Hermes_CLIENT_ID'     => $ClientObj->id,
                        'OLD_CLIENT_ID'        => $ClientObj->client_id_old,
                        'Report Template ID'   => $ReportTemplateObj->id,
                        'report_template_name' => $ReportTemplateObj->report_template_name,
                        'BOMA Template'        => $ReportTemplateObj->is_boma_report_template ? 1 : 0,

                        'is_boma_report_template'                     => $ReportTemplateObj->is_boma_report_template ? 1 : 0,
                        'is_default_advance_variance_report_template' => $ReportTemplateObj->is_default_advance_variance_report_template ? 1 : 0,
                        'is_default_analytics_report_template'        => $ReportTemplateObj->is_default_analytics_report_template ? 1 : 0,
                        'is_data_calcs_enabled'                       => $ReportTemplateObj->is_data_calcs_enabled ? 1 : 0,
                    ];
                }
            )->toArray()
        );

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'Client_detail retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'client_detail'
        );
    }

    /**
     * @param Request $RequestObj
     * @param $client_id_old
     * @param $report_template_id
     * @return \Illuminate\Http\JsonResponse|null|void
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index_report_template_coas(Request $RequestObj, $client_id_old, $report_template_id)
    {
        $this->ClientRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        $return_me = new  SpreadsheetCollection();
        /** @var ReportTemplate $ReportTemplateObj */
        $ReportTemplateObj = $this->ReportTemplateRepositoryObj
            ->with('client.properties.nativeCoas')
            ->with('reportTemplateAccountGroups.reportTemplateMappings.nativeAccount')
            ->findWhere(
                [
                    'id' => $report_template_id,
                ]
            )->first();

        $dedup = [];
        foreach ($ReportTemplateObj->getAllNativeAccounts() as $NativeAccountObj)
        {
            foreach ($ReportTemplateObj->client->properties as $PropertyObj)
            {
                if ($PropertyObj
                    ->nativeCoas->filter(
                        function ($NativeCoaObj) use ($NativeAccountObj)
                        {
                            return $NativeCoaObj->id == $NativeAccountObj->native_coa_id;
                        }
                    )->first()
                )
                {
                    if (isset($dedup[$PropertyObj->property_id_old][$NativeAccountObj->native_coa_id]))
                    {
                        continue;
                    }

                    $return_me[] = [
                        'Hermes_CLIENT_ID'   => $ReportTemplateObj->client_id,
                        'OLD_CLIENT_ID'      => $ReportTemplateObj->client->client_id_old,
                        'COA_ID'             => $NativeAccountObj->native_coa_id,
                        'Hermes_property_ID' => $PropertyObj->id,
                        'FK_PROPERTY_ID'     => $PropertyObj->property_id_old,
                    ];

                    $dedup[$PropertyObj->property_id_old][$NativeAccountObj->native_coa_id] = 1;
                }
            }
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'client_detail retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'client_detail'
        );
    }

    /**
     * @param Request $RequestObj
     * @param $client_id_old
     * @param $report_template_id
     * @return \Illuminate\Http\JsonResponse|null|void
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index_report_template_coas_detail(Request $RequestObj, $client_id_old, $report_template_id)
    {
        $this->ClientRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        $return_me = new  SpreadsheetCollection();
        /** @var ReportTemplate $ReportTemplateObj */
        $ReportTemplateObj = $this->ReportTemplateRepositoryObj
            ->with('client.properties.nativeCoas')
            ->with('reportTemplateAccountGroups')
            ->findWhere(
                [
                    'id' => $report_template_id,
                ]
            )->first();
        foreach ($ReportTemplateObj->reportTemplateAccountGroups as $ReportTemplateAccountGroupObj)
        {
            if ($ReportTemplateAccountGroupObj->deprecated_waypoint_code)
            {
                $ACCOUNT_CODE_ID = isset(self::BOMA_CODES_TO_TITAN_BOMA_CODE_IDS[$ReportTemplateAccountGroupObj->deprecated_waypoint_code])
                    ?
                    self::BOMA_CODES_TO_TITAN_BOMA_CODE_IDS[$ReportTemplateAccountGroupObj->deprecated_waypoint_code]
                    :
                    null;
            }
            else
            {
                $ACCOUNT_CODE_ID = $ReportTemplateAccountGroupObj->id;
            }

            $AMOUNT_SIGN_OBJ = $ReportTemplateAccountGroupObj->nativeAccountType->nativeAccountTypeTrailers->filter(
                function ($NativeAccountTypeTrailerObj)
                {
                    return $NativeAccountTypeTrailerObj->property_id == null;
                }
            )->first();

            $return_me[] = [
                'ACCOUNT_CODE_ID'    => $ACCOUNT_CODE_ID,
                'HEADER_CODE'        => $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent ? $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->report_template_account_group_code : null,
                'HEADER_NAME'        => $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent ? $ReportTemplateAccountGroupObj->reportTemplateAccountGroupParent->report_template_account_group_name : null,
                'ACCOUNT_CODE'       => $ReportTemplateAccountGroupObj->report_template_account_group_code,
                'ACCOUNT_CODE_NAME'  => $ReportTemplateAccountGroupObj->report_template_account_group_name,
                'ACCOUNT_CODE_LEVEL' => $ReportTemplateAccountGroupObj->get_generations(),
                'DATA_SOURCE'        => 1,
                'COA_ID'             => null,
                'AMOUNT_SIGN'        => $AMOUNT_SIGN_OBJ ? $AMOUNT_SIGN_OBJ->actual_coefficient : null,
                'AMOUNT_SIGN_1'      => $AMOUNT_SIGN_OBJ ? $AMOUNT_SIGN_OBJ->budgeted_coefficient : null,
            ];

            foreach ($ReportTemplateAccountGroupObj->nativeAccounts as $NativeAccountObj)
            {
                $AMOUNT_SIGN_OBJ = $NativeAccountObj->nativeAccountType->nativeAccountTypeTrailers->filter(
                    function ($NativeAccountTypeTrailerObj)
                    {
                        return $NativeAccountTypeTrailerObj->property_id == null;
                    }
                )->first();
                $return_me[]     = [
                    'ACCOUNT_CODE_ID'    => $NativeAccountObj->id,
                    'HEADER_CODE'        => $ReportTemplateAccountGroupObj->report_template_account_group_code,
                    'HEADER_NAME'        => $ReportTemplateAccountGroupObj->report_template_account_group_name,
                    'ACCOUNT_CODE'       => $NativeAccountObj->native_account_code,
                    'ACCOUNT_CODE_NAME'  => $NativeAccountObj->native_account_name,
                    'ACCOUNT_CODE_LEVEL' => $ReportTemplateAccountGroupObj->get_generations() + 1,
                    'DATA_SOURCE'        => 1,
                    'COA_ID'             => $NativeAccountObj->native_coa_id,
                    'AMOUNT_SIGN'        => $AMOUNT_SIGN_OBJ->actual_coefficient,
                    'AMOUNT_SIGN_1'      => $AMOUNT_SIGN_OBJ->budgeted_coefficient,
                ];
            }
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'client_detail retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'client_detail'
        );
    }

    /**
     * @param Request $RequestObj
     * @param $client_id_old
     * @param $report_template_id
     * @return \Illuminate\Http\JsonResponse|null|void
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index_report_template_cross_reference(Request $RequestObj, $client_id_old, $report_template_id)
    {
        $this->ClientRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->ClientRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /** @var Client $ClientObj */
        if ( ! $ClientObj = $this->ClientRepositoryObj
            ->with('properties.nativeCoas.nativeAccounts.nativeAccountType.nativeAccountTypeTrailers')
            ->findWhere(
                [
                    'client_id_old' => $client_id_old,
                ]
            )->first())
        {
            throw new GeneralException('No client found');
        }
        /** @var ReportTemplate $ReportTemplateObj */
        if ( ! $ReportTemplateObj = $this->ReportTemplateRepositoryObj
            ->with('reportTemplateAccountGroups.reportTemplateMappings.nativeAccount')
            ->findWhere(
                [
                    'id'        => $report_template_id,
                    'client_id' => $ClientObj->id,
                ]
            )->first()
        )
        {
            throw new GeneralException('No report template found');
        }
        $all_report_template_native_account_ids = $ReportTemplateObj->getAllNativeAccounts()->pluck('id')->toArray();

        $return_me = new  SpreadsheetCollection();
        foreach ($ClientObj->properties as $PropertyObj)
        {
            /** @var NativeCoa $PropertyNativeCoaObj */
            $PropertyNativeCoaObj = $PropertyObj
                ->nativeCoas
                ->first();

            /** @var NativeAccount $PropertyNativeAccountObj */
            foreach ($PropertyNativeCoaObj->nativeAccounts as $PropertyNativeAccountObj)
            {
                if (in_array($PropertyNativeAccountObj->id, $all_report_template_native_account_ids))
                {
                    if ( ! $NativeAccountTypeTrailerObj = $PropertyNativeAccountObj->getCoeffients($PropertyObj->id))
                    {
                        throw new GeneralException('No NativeAccountTypeTrailer');
                    }

                    $return_me[] = [
                        'ACCOUNT_CODE_ID'    => $PropertyNativeAccountObj->id,
                        'REPORT_TEMPLATE_ID' => $ReportTemplateObj->id,
                        'PROPERTY_CODE'      => $PropertyObj->property_code,
                        'DATA_SOURCE'        => 1,
                        'COA_ID'             => $PropertyNativeAccountObj->native_coa_id,
                        'AMOUNT_SIGN'        => $NativeAccountTypeTrailerObj->actual_coefficient ?: null,
                        'AMOUNT_SIGN_1'      => $NativeAccountTypeTrailerObj->budgeted_coefficient ?: null,
                        'HERMES_PROPERTY_ID' => $PropertyObj->id,
                        'FK_PROPERTY_ID'     => $PropertyObj->property_id_old,
                    ];
                }
            }
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'report_template_cross_reference retrieved successfully');
        }
        return $return_me->toCSVReportGeneric(
            'report_template_cross_reference'
        );
    }
}
