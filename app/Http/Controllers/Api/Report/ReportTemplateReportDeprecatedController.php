<?php

namespace App\Waypoint\Http\Controllers\Api\Report;

use App\Waypoint\Collection;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Repositories\ReportTemplateRepository;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;

/**
 * @codeCoverageIgnore
 */
class ReportTemplateReportDeprecatedController extends BaseApiController
{
    /**
     * @todo - why all the repos????? Tighten this up
     */
    /** @var  ReportTemplateRepository */
    private $ReportTemplateRepositoryObj;

    /**
     * ReportTemplateController constructor.
     * @param ReportTemplateRepository $ReportTemplateRepositoryObj
     */
    public function __construct(ReportTemplateRepository $ReportTemplateRepositoryObj)
    {
        $this->ReportTemplateRepositoryObj = $ReportTemplateRepositoryObj;
        parent::__construct($ReportTemplateRepositoryObj);
    }

    /**
     * @param integer $report_template_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     * @throws \App\Waypoint\Exceptions\GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function show($report_template_id, Request $request)
    {
        /**
         * @todo use a custom model to eliminate id from report
         */
        $this->ReportTemplateRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->ReportTemplateRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));

        /** @var Collection $ReportTemplateObj */
        $ReportTemplateObj = $this->ReportTemplateRepositoryObj->find($report_template_id);

        if ('application/json' == $request->header('Content-Type'))
        {
            return $this->sendResponse($ReportTemplateObj->reportTemplateAccountGroups->toArray(), 'ReportTemplate(s) retrieved successfully');
        }
        $ReportTemplateObj->reportTemplateAccountGroups->toCSVReport($this->ReportTemplateRepositoryObj->model() . ' Report Generated at ' . date('Y-m-d H:i:s'));
    }
}
