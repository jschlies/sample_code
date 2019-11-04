<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\ReportTemplateDetail;
use App\Waypoint\Repositories\ReportTemplateDetailRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\ResponseUtil;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;

/**
 * Class ReportTemplateDetailController
 */
class ReportTemplateDetailController extends BaseApiController
{
    /** @var  ReportTemplateDetailRepository */
    private $ReportTemplateDetailRepositoryObj;

    public function __construct(ReportTemplateDetailRepository $ReportTemplateDetailRepository)
    {
        $this->ReportTemplateDetailRepositoryObj = $ReportTemplateDetailRepository;
        parent::__construct($this->ReportTemplateDetailRepositoryObj);
    }

    /**
     * @param Request $Request
     * @return \Illuminate\Http\JsonResponse|null
     * @throws \App\Waypoint\Exceptions\GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $Request, $client_id)
    {
        $this->ReportTemplateDetailRepositoryObj->pushCriteria(new RequestCriteria($Request));
        $this->ReportTemplateDetailRepositoryObj->pushCriteria(new LimitOffsetCriteria($Request));
        $ReportTemplateDetailObjArr = $this->ReportTemplateDetailRepositoryObj
            ->findWhere(['client_id' => $client_id]);

        return $this->sendResponse($ReportTemplateDetailObjArr, 'ReportTemplate(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $report_template_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $report_template_id)
    {
        /** @var ReportTemplateDetail $ReportTemplateDetailObj */
        $ReportTemplateDetailObj = $this->ReportTemplateDetailRepositoryObj
            ->find($report_template_id);
        if (empty($ReportTemplateDetailObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplate not found'), 404);
        }

        return $this->sendResponse($ReportTemplateDetailObj, 'ReportTemplateDetail retrieved successfully');
    }
}
