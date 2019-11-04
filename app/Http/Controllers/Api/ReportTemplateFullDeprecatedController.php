<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\NativeAccountDetail;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\ReportTemplateAccountGroupFull;
use App\Waypoint\Models\ReportTemplateMappingFull;
use function collect_waypoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Waypoint\Models\ReportTemplateFull;
use App\Waypoint\Repositories\ReportTemplateFullRepository;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\ResponseUtil;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Response;
use View;

/**
 * Class ReportTemplateFullDeprecatedController
 * @codeCoverageIgnore
 */
class ReportTemplateFullDeprecatedController extends BaseApiController
{
    /** @var  ReportTemplateFullRepository */
    private $ReportTemplateFullRepositoryObj;

    public function __construct(ReportTemplateFullRepository $ReportTemplateFullRepository)
    {
        $this->ReportTemplateFullRepositoryObj = $ReportTemplateFullRepository;
        parent::__construct($this->ReportTemplateFullRepositoryObj);
    }

    /**
     * @param Request $Request
     * @return \Illuminate\Http\JsonResponse|null
     * @throws \App\Waypoint\Exceptions\GeneralException
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function index(Request $Request, $client_id)
    {
        ReportTemplateFull::setSuspendValidation(true);
        ReportTemplateMappingFull::setSuspendValidation(true);
        ReportTemplateAccountGroup::setSuspendValidation(true);
        NativeAccountDetail::setSuspendValidation(true);
        $this->ReportTemplateFullRepositoryObj->pushCriteria(new RequestCriteria($Request));
        $this->ReportTemplateFullRepositoryObj->pushCriteria(new LimitOffsetCriteria($Request));
        $ReportTemplateFullObjArr = $this->ReportTemplateFullRepositoryObj
            ->with('reportTemplateAccountGroupsChildrenFull.nativeAccountType')
            ->with('reportTemplateAccountGroupsChildrenFull.reportTemplateMappingsFull.nativeAccountDetail')
            ->with('reportTemplateAccountGroupsChildrenFull.reportTemplateAccountGroupChildrenFull.nativeAccountType')
            ->with('reportTemplateAccountGroupsChildrenFull.reportTemplateAccountGroupChildrenFull.reportTemplateMappingsFull.nativeAccountDetail')
            ->with('reportTemplateAccountGroupsChildrenFull.reportTemplateAccountGroupChildrenFull.reportTemplateAccountGroupChildrenFull.nativeAccountType')
            ->with('reportTemplateAccountGroupsChildrenFull.reportTemplateAccountGroupChildrenFull.reportTemplateAccountGroupChildrenFull.reportTemplateMappingsFull.nativeAccountDetail')
            ->findWhere(['client_id' => $client_id]);

        return $this->sendResponse($ReportTemplateFullObjArr, 'ReportTemplateFull(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @param integer $report_template_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function show($client_id, $report_template_id)
    {
        ReportTemplateFull::setSuspendValidation(true);
        ReportTemplateMappingFull::setSuspendValidation(true);
        ReportTemplateAccountGroup::setSuspendValidation(true);
        ReportTemplateAccountGroupFull::setSuspendValidation(true);
        NativeAccountDetail::setSuspendValidation(true);
        /** @var ReportTemplateFull $ReportTemplateFullObj */

        $ClientObj = Client::find($client_id);

        $key                      = 'report_template_full_arr_client_' . $ClientObj->id . '_report_template_' . $report_template_id;
        $report_template_full_arr = $ClientObj->getPreCalcValue($key);
        if ($report_template_full_arr === null)
        {
            $ReportTemplateFullObj = $this->ReportTemplateFullRepositoryObj
                ->with('reportTemplateAccountGroupsChildrenFull.reportTemplateMappingsFull.nativeAccountDetail.nativeAccountTypeDetail.nativeAccountTypeTrailers')
                ->find($report_template_id);
            if (empty($ReportTemplateFullObj))
            {
                return Response::json(ResponseUtil::makeError('ReportTemplateFull not found'), 404);
            }
            $report_template_full_arr = collect_waypoint([$ReportTemplateFullObj->toArray()])->toArray();

            $ClientObj->updatePreCalcValue(
                $key,
                $report_template_full_arr
            );
        }

        return $this->sendResponse($report_template_full_arr, 'ReportTemplateFull retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @return JsonResponse|null
     * @throws GeneralException
     */
    public function showForClient($client_id)
    {
        ReportTemplateFull::setSuspendValidation(true);
        ReportTemplateMappingFull::setSuspendValidation(true);
        ReportTemplateAccountGroup::setSuspendValidation(true);
        NativeAccountDetail::setSuspendValidation(true);
        /** @var ReportTemplateFull $ReportTemplateFullObj */
        $ReportTemplateFullObjArr = $this->ReportTemplateFullRepositoryObj
            ->with('reportTemplateAccountGroupsFull.reportTemplateAccountGroupChildrenFull')
            ->with('reportTemplateAccountGroupsFull.reportTemplateMappingsFull.nativeAccountDetail')
            ->findWhere(['client_id' => $client_id]);

        return $this->sendResponse($ReportTemplateFullObjArr->toArray(), 'ReportTemplateFull(s) retrieved successfully');
    }

    /**
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws \App\Waypoint\Exceptions\GeneralException
     */
    public function generateAccountTypeBasedReportTemplate($client_id)
    {
        $ReportTemplateObj = $this->ReportTemplateFullRepositoryObj->generateAccountTypeBasedReportTemplate(
            $client_id
        );

        return $this->sendResponse($ReportTemplateObj->toArray(), 'ReportTemplate created');
    }

    /**
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws \App\Waypoint\Exceptions\GeneralException
     */
    public function generateBomaBasedReportTemplate($client_id)
    {
        $ReportTemplateObj = $this->ReportTemplateFullRepositoryObj->generateBomaBasedReportTemplate(
            $client_id
        );

        return $this->sendResponse($ReportTemplateObj->toArray(), 'ReportTemplate created');
    }

    /**
     * Remove the specified ReportTemplate from storage.
     * DELETE /reportTemplates/{id}
     *
     * @param integer $client_id
     * @param integer $report_template_id
     * @return JsonResponse
     * @throws GeneralException
     * @throws \Exception
     */
    public function destroy($client_id, $report_template_id)
    {
        /** @var ReportTemplate $ReportTemplateObj */
        $this->ReportTemplateFullRepositoryObj->delete($report_template_id);

        return $this->sendResponse($report_template_id, 'ReportTemplate deleted successfully');
    }

    /**
     * @param Request $request
     * @param integer $client_id
     * @param integer $report_template_id
     * @return \Illuminate\Contracts\View\View
     */
    public function renderReportTemplate(Request $request, $client_id, $report_template_id)
    {
        ReportTemplateMappingFull::setSuspendValidation(true);
        ReportTemplateAccountGroup::setSuspendValidation(true);
        ReportTemplateAccountGroupFull::setSuspendValidation(true);
        NativeAccountDetail::setSuspendValidation(true);
        /** @var ReportTemplateFull $ReportTemplateFullObj */
        $ReportTemplateFullObj = $this->ReportTemplateFullRepositoryObj
            ->find($report_template_id);

        return View::make(
            'pages.client_report_template',
            [
                'client_id' => $client_id,
                // Note the wrapper array to make one and many identical
                'templates' => json_encode([$ReportTemplateFullObj]),
            ]
        );
    }

    public function renderReportTemplatesForClient(Request $request, $client_id)
    {
        ReportTemplateFull::setSuspendValidation(true);
        ReportTemplateMappingFull::setSuspendValidation(true);
        ReportTemplateAccountGroup::setSuspendValidation(true);
        NativeAccountDetail::setSuspendValidation(true);
        /** @var ReportTemplateFull $ReportTemplateFullObj */
        $ReportTemplateFullObjArr = $this->ReportTemplateFullRepositoryObj
            ->with('reportTemplateAccountGroupsFull.reportTemplateAccountGroupChildrenFull')
            ->with('reportTemplateAccountGroupsFull.reportTemplateMappingsFull.nativeAccountDetail')
            ->findWhere(['client_id' => $client_id]);

        return View::make(
            'pages.client_report_template',
            [
                'client_id' => $client_id,
                'templates' => json_encode($ReportTemplateFullObjArr),
            ]
        );
    }
}
