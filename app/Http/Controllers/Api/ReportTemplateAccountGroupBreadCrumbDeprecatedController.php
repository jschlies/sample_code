<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\ReportTemplateAccountGroupBreadCrumb;
use App\Waypoint\Repositories\ReportTemplateAccountGroupBreadCrumbRepository;
use App\Waypoint\Repositories\ReportTemplateRepository;
use App\Waypoint\ResponseUtil;
use Response;
use App\Waypoint\Http\ApiController as BaseApiController;

/**
 * Class ReportTemplateAccountGroupBreadCrumbController
 * @codeCoverageIgnore
 */
class ReportTemplateAccountGroupBreadCrumbDeprecatedController extends BaseApiController
{
    /** @var  ReportTemplateAccountGroupBreadCrumbRepository */
    private $ReportTemplateAccountGroupBreadCrumbRepositoryObj;

    public function __construct(ReportTemplateAccountGroupBreadCrumbRepository $ReportTemplateAccountGroupBreadCrumbRepositoryRepositoryObj)
    {
        $this->ReportTemplateAccountGroupBreadCrumbRepositoryObj = $ReportTemplateAccountGroupBreadCrumbRepositoryRepositoryObj;
        parent::__construct($ReportTemplateAccountGroupBreadCrumbRepositoryRepositoryObj);
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function show($id)
    {
        /** @var ReportTemplateAccountGroupBreadCrumb $ReportTemplateAccountGroupBreadCrumbObj */
        $ReportTemplateAccountGroupBreadCrumbObj = $this->ReportTemplateAccountGroupBreadCrumbRepositoryObj->find($id);
        if (empty($ReportTemplateAccountGroupBreadCrumbObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateAccountGroupBreadCrumb not found'), 404);
        }
        return $this->sendResponse($ReportTemplateAccountGroupBreadCrumbObj, 'ReportTemplateAccountGroupBreadCrumb retrieved successfully');
    }

    /**
     * @param $rt_account_group_code
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function showWithCode($rt_account_group_code)
    {
        $ReportTemplateRepositoryObj = App::make(ReportTemplateRepository::class);
        if ( ! $ReportTemplateObj = $ReportTemplateRepositoryObj->findWhere(
            [
                'report_template_name' => ReportTemplate::BOMA_REPORT_TEMPLATE_NAME,
            ]
        )->first())
        {
            throw new GeneralException('No BOMA_REPORT_TEMPLATE found');
        }

        /** @var ReportTemplateAccountGroupBreadCrumb $ReportTemplateAccountGroupBreadCrumbObj */
        $ReportTemplateAccountGroupBreadCrumbObj = $this->ReportTemplateAccountGroupBreadCrumbRepositoryObj->findWhere(
            [
                ['report_template_id', '=', $ReportTemplateObj->id],
                ['report_template_account_group_code', '=', $rt_account_group_code],
            ]
        );
        if (empty($ReportTemplateAccountGroupBreadCrumbObj))
        {
            return Response::json(ResponseUtil::makeError('ReportTemplateAccountGroupBreadCrumb not found'), 404);
        }
        return $this->sendResponse($ReportTemplateAccountGroupBreadCrumbObj, 'ReportTemplateAccountGroupBreadCrumb retrieved successfully');
    }
}
