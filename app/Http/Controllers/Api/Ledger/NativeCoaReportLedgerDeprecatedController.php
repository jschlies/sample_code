<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Ledger\Metadata;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\Property;
use App\Waypoint\Repositories\Ledger\NativeCoaLedgerRepository;
use App\Waypoint\Models\Spreadsheet;

/**
 * Class NativeCoaReportLedgerDeprecatedController
 * @codeCoverageIgnore
 */
class NativeCoaReportLedgerDeprecatedController extends LedgerController
{

    protected $NativeCoaLedgerRepositoryObj = null;

    public $apiTitle = 'nativeCoaReport';

    public $apiDisplayName = 'Native Chart of Accounts';

    /** @var array */
    public $spreadsheetColumnFormattingRules = [
        Spreadsheet::NATIVE_COA_FORMATTING_RULES           => [
            'H' => '"$"#,##0.00',
            'I' => '"$"#,##0.00',
            'J' => '"$"#,##0.00',
            'K' => '"$"#,##0.00',
            'L' => '"$"#,##0.00',
            'M' => '"$"#,##0.00',
            'N' => '"$"#,##0.00',
            'O' => '"$"#,##0.00',
            'P' => '"$"#,##0.00',
            'Q' => '"$"#,##0.00',
            'R' => '"$"#,##0.00',
            'S' => '"$"#,##0.00',
        ],
        Spreadsheet::NATIVE_COA_OCCUPANCY_FORMATTING_RULES => [
            'D' => '#,##0" sq ft"',
            'E' => '#,##0" sq ft"',
            'F' => '#,##0" sq ft"',
            'G' => '#,##0" sq ft"',
            'H' => '#,##0" sq ft"',
            'I' => '#,##0" sq ft"',
            'J' => '#,##0" sq ft"',
            'K' => '#,##0" sq ft"',
            'L' => '#,##0" sq ft"',
            'M' => '#,##0" sq ft"',
            'N' => '#,##0" sq ft"',
            'O' => '#,##0" sq ft"',
        ],
    ];

    /**
     * OperatingExpensesPropertyController constructor.
     * @param NativeCoaLedgerRepository $NativeCoaLedgerRepositoryObj
     */
    public function __construct(NativeCoaLedgerRepository $NativeCoaLedgerRepositoryObj)
    {
        $this->NativeCoaLedgerRepositoryObj = $NativeCoaLedgerRepositoryObj;
        parent::__construct($NativeCoaLedgerRepositoryObj);
    }

    /**
     * @param integer $property_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function getPropertyReport($property_id)
    {
        $this->NativeCoaLedgerRepositoryObj->ClientObj = $this->getClientObject();

        if ( ! $PropertyObj = Property::find($property_id))
        {
            throw new GeneralException('could not find property group from property_group_id');
        }

        $payload = $this->NativeCoaLedgerRepositoryObj->getNativeCoaData([$PropertyObj->property_id_old]);

        $metadata = (new Metadata(
            [
                'LedgerController' => $this,
                'PropertyGroup'    => $PropertyObj,
                'as_of_date'       => $PropertyObj->client->get_client_asof_date()->format('M, Y'),
            ]
        ))->toArray(true);

        return $this->sendResponse(
            $payload,
            'native coa for group successful response',
            [], [],
            $metadata
        );
    }

    /**
     * @param integer $property_group_id
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function getPropertyGroupReport($property_group_id)
    {
        if ( ! $PropertyGroupObj = PropertyGroup::find($property_group_id))
        {
            throw new GeneralException('could not find group');
        }

        $this->NativeCoaLedgerRepositoryObj->ClientObj = $this->getClientObject();
        $property_id_old_array                         = $PropertyGroupObj->properties->pluck('property_id_old')->toArray();
        $payload                                       = $this->NativeCoaLedgerRepositoryObj->getNativeCoaData($property_id_old_array);
        $metadata                                      = (new Metadata(
            [
                'LedgerController' => $this,
                'PropertyGroup'    => $PropertyGroupObj,
                'as_of_date'       => $PropertyGroupObj->client->get_client_asof_date(),
            ]
        ))->toArray(true);

        return $this->sendResponse(
            $payload,
            'native coa for group successful response',
            [], [],
            $metadata
        );
    }
}
