<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Repositories\SystemInformationRepository;

/**
 * Class SystemInfoController
 */
class SystemInfoController extends BaseApiController
{
    /**
     * @var SystemInformationRepository
     */
    protected $SystemInformationRepositoryObj;

    public function __construct(SystemInformationRepository $SystemInformationRepositoryObj)
    {
        parent::__construct($SystemInformationRepositoryObj);
        $this->SystemInformationRepositoryObj = $SystemInformationRepositoryObj;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws GeneralException
     */
    public function index()
    {
        $return_me = $this->SystemInformationRepositoryObj->generate_system_information();
        return $this->sendResponse($return_me, 'SystemInfo retrieved successfully');
    }

}
