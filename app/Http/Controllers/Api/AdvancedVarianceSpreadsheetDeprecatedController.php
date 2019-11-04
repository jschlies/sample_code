<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\Spreadsheet;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use Exception;

/**
 * Class AdvancedVarianceSpreadsheetDeprecatedController
 * @codeCoverageIgnore
 */
class AdvancedVarianceSpreadsheetDeprecatedController extends BaseApiController
{

    /** @var AdvancedVarianceRepository|null $AdvancedVarianceRepositoryObj */
    private $AdvancedVarianceRepositoryObj = null;

    /**
     * AdvancedVarianceSpreadsheetController constructor.
     * @param AdvancedVarianceRepository $AdvancedVarianceRepositoryObj
     */
    public function __construct(AdvancedVarianceRepository $AdvancedVarianceRepositoryObj)
    {
        $this->AdvancedVarianceRepositoryObj = $AdvancedVarianceRepositoryObj;

        parent::__construct($AdvancedVarianceRepositoryObj);
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param integer $advanced_variance_id
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function index($client_id, $property_id, $advanced_variance_id)
    {
        try
        {
            /** @var Property $PropertyObj */
            if ( ! $PropertyObj = Property::find($property_id))
            {
                throw new GeneralException('cannot find property');
            }

            /** @var Client $ClientObj */
            if ( ! $ClientObj = Client::find($client_id))
            {
                throw new GeneralException('cannot find client');
            }

            /** @var AdvancedVariance $AdvancedVarianceObj */
            $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->find($advanced_variance_id);
            if (empty($AdvancedVarianceObj))
            {
                throw new GeneralException('cannot find this advanced variance report');
            }

            $spreadsheet_details_arr = Spreadsheet::createAdvancedVarianceSpreadsheet($AdvancedVarianceObj, $PropertyObj, $ClientObj);

            return $this->sendResponse(
                [
                    $spreadsheet_details_arr['excel_as_string'],
                ],
                'successful delivery of excel file',
                [], [],
                [
                    'filename' => $spreadsheet_details_arr['filename'] . '.xls',
                ]
            );
        }
        catch (Exception $e)
        {
            throw new GeneralException($e->getMessage());
        }
    }
}