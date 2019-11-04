<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\Ledger\CompareRepository;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Repositories\ReportTemplateRepository;

/**
 * Class LedgerNativeAccountDeprecatedController
 * @package App\Waypoint\Http\Controllers\Ledger
 * @codeCoverageIgnore
 */
class LedgerNativeAccountDeprecatedController extends LedgerController
{

    /**
     * ComparePropertyController constructor.
     * @param CompareRepository $CompareRepositoryObj
     */
    public function __construct(CompareRepository $CompareRepositoryObj)
    {
        $this->CompareRepositoryObj = $CompareRepositoryObj;
        parent::__construct($CompareRepositoryObj);
    }

    /**
     * @param $client_id
     * @param $property_id
     * @param $report_template_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function index(
        $client_id,
        $property_id,
        $report_template_id,
        $as_of_month,
        $as_of_year,
        $quarterly
    ) {
        try
        {
            $AdvancedVarianceRepositoryObj = \App::make(AdvancedVarianceRepository::class);
            $ReportTemplateRepositoryObj   = \App::make(ReportTemplateRepository::class);
            $PropertyRepositoryObj         = \App::make(PropertyRepository::class);

            /** @var Property $PropertyObj */
            $PropertyObj = $PropertyRepositoryObj
                ->with('nativeCoas.nativeAccounts.nativeAccountType.nativeAccountTypeTrailers')
                ->find($property_id);

            /** @var ReportTemplate $ReportTemplateObj */
            $ReportTemplateObj = $ReportTemplateRepositoryObj
                ->with('reportTemplateAccountGroups.reportTemplateMappings.nativeAccount.nativeAccountType.nativeAccountTypeTrailers')
                ->with('reportTemplateAccountGroups.nativeAccountType.nativeAccountTypeTrailers')
                ->find($report_template_id);

            $advanced_variance_default_native_coa_codes =
                $ReportTemplateObj
                    ->getAllNativeAccounts()
                    ->pluck('native_account_code')->filter(
                        function ($native_code, $key) use ($PropertyObj)
                        {
                            if ( ! $PropertyObj
                                ->nativeCoas
                                ->first()
                                ->nativeAccounts
                                ->where('native_account_code', $native_code)->count()
                            )
                            {
                                return false;
                            }

                            return $PropertyObj
                                ->nativeCoas
                                ->first()
                                ->nativeAccounts
                                ->where('native_account_code', $native_code)
                                ->first()->getCoeffients($PropertyObj->id);
                        }
                    )->sort()->toArray();

            $this->payload = $AdvancedVarianceRepositoryObj->getLedgerVarianceDataArr($advanced_variance_default_native_coa_codes, $property_id, $as_of_month, $as_of_year,
                                                                                      $quarterly);

            // return json payload
            return $this->sendResponse(
                $this->payload,
                'compare benchmark data generated successfully',
                [],
                $this->warnings,
                [
                    'count' => count($this->payload),
                ]
            );
        }
        catch (GeneralException $e)
        {
            return $this->sendResponse(
                [],
                'unsuccessful benchmark data generation',
                $e->getMessage(),
                [],
                [
                    'apiTitle'    => 'CompareProperty',
                    'displayName' => 'Compare Property',
                    'count'       => 0,
                ]
            );
        }
        catch (\Exception $e)
        {
            return $this->sendResponse(
                [],
                'unsuccessful benchmark data generation',
                $e->getMessage(),
                [],
                [
                    'apiTitle'    => 'CompareProperty',
                    'displayName' => 'Compare Property',
                    'count'       => 0,
                ]
            );
        }
    }
}