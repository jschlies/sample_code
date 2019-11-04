<?php

namespace App\Waypoint\Http\Controllers\Api\Report;

use App\Waypoint\Collection;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Waypoint\Repositories\NativeCoaRepository;
use App\Waypoint\Http\ApiController as BaseApiController;

/**
 * @codeCoverageIgnore
 */
class NativeCoaReportDeprecatedController extends BaseApiController
{
    /**
     * @todo - either merge this (and other Report controllers) into app/Http/Controllers/ApiRequest or
     *       come up w/ a naming system for all controllers
     */
    /** @var  NativeCoaRepository */
    private $NativeCoaRepositoryObj;

    /**
     * NativeCoaReportController constructor.
     * @param NativeCoaRepository $NativeCoaRepositoryObj
     */
    public function __construct(NativeCoaRepository $NativeCoaRepositoryObj)
    {
        $this->NativeCoaRepositoryObj = $NativeCoaRepositoryObj;

        parent::__construct($NativeCoaRepositoryObj);
    }

    /**
     * Display a report of the nativeAccounts.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    public function show($native_coa_id, Request $request)
    {
        /**
         * @todo use a custom model to eliminate id from report
         */
        $this->NativeCoaRepositoryObj->pushCriteria(new RequestCriteria($request));
        $this->NativeCoaRepositoryObj->pushCriteria(new LimitOffsetCriteria($request));

        /** @var Collection $NativeCoas */
        $NativeCoaObj = $this->NativeCoaRepositoryObj->find($native_coa_id);

        if ('application/json' == $request->header('Content-Type'))
        {
            return $this->sendResponse($NativeCoaObj->nativeAccounts, 'NativeCoa(s) retrieved successfully');
        }
        $NativeCoaObj->nativeAccounts->toCSVReport($this->NativeCoaRepositoryObj->model() . ' Report Generated at ' . date('Y-m-d H:i:s'));
    }
}
