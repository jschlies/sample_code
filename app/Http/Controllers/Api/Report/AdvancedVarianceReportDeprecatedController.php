<?php

namespace App\Waypoint\Http\Controllers\Api\Report;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Repositories\AdvancedVarianceLineItemReportRepository;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\SpreadsheetCollection;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * @codeCoverageIgnore
 */
class AdvancedVarianceReportDeprecatedController extends BaseApiController
{
    /** @var  AdvancedVarianceRepository */
    private $AdvancedVarianceRepositoryObj;
    /** @var  AdvancedVarianceLineItemReportRepository */
    private $AdvancedVarianceLineItemReportRepositoryObj;

    /**
     * AdvancedVarianceReportController constructor.
     * @param AdvancedVarianceRepository $AdvancedVarianceRepositoryObj
     */
    public function __construct(AdvancedVarianceLineItemReportRepository $AdvancedVarianceLineItemReportRepositoryObjObj)
    {
        $this->AdvancedVarianceLineItemReportRepositoryObj = $AdvancedVarianceLineItemReportRepositoryObjObj;
        $this->AdvancedVarianceRepositoryObj               = App::make(AdvancedVarianceRepository::class);

        parent::__construct($AdvancedVarianceLineItemReportRepositoryObjObj);
    }

    /**
     * Display a report of the Properties.
     *
     * @param Request $RequestObj
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     *
     * @todo non-standard response - fix me
     */
    public function index(Request $RequestObj, $client_id, $property_id, $advanced_variance_id)
    {
        $this->AdvancedVarianceLineItemReportRepositoryObj->pushCriteria(new RequestCriteria($RequestObj));
        $this->AdvancedVarianceLineItemReportRepositoryObj->pushCriteria(new LimitOffsetCriteria($RequestObj));

        /** @var Collection $PropertyObjArr */
        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->find($advanced_variance_id);

        $return_me = new  SpreadsheetCollection();
        /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
        foreach ($AdvancedVarianceObj->advancedVarianceLineItemsSorted() as $AdvancedVarianceLineItemObj)
        {
            $return_me[] = $AdvancedVarianceLineItemObj->toArray();
        }

        if ('application/json' == $RequestObj->header('Content-Type'))
        {
            return $this->sendResponse($return_me, 'AdvancedVariance data retrieved successfully');
        }
        $return_me->toCSVReportGeneric(
            $this->AdvancedVarianceLineItemReportRepositoryObj->model() . ' Report Generated at ' . date('Y-m-d H:i:s'), true, true
        );
    }
}
