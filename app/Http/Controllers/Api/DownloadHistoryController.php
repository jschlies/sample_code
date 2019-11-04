<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Http\Requests\Generated\Api\CreateDownloadHistoryRequest;
use App\Waypoint\Repositories\DownloadHistoryRepository;
use App\Waypoint\Http\ApiController as BaseApiController;

class DownloadHistoryController extends BaseApiController
{
    /** @var  DownloadHistoryRepository */
    private $DownloadHistoryRepositoryObj;

    /**
     * DownloadHistoryController constructor.
     * @param DownloadHistoryRepository $DownloadHistoryRepositoryObj
     */
    public function __construct(DownloadHistoryRepository $DownloadHistoryRepositoryObj)
    {
        $this->DownloadHistoryRepositoryObj = $DownloadHistoryRepositoryObj;
        parent::__construct($DownloadHistoryRepositoryObj);
    }

    /**
     * @param CreateDownloadHistoryRequest $DownloadHistoryRequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function store(CreateDownloadHistoryRequest $DownloadHistoryRequestObj)
    {
        $input = $DownloadHistoryRequestObj->all();

        $DownloadHistoryObj = $this->DownloadHistoryRepositoryObj->create($input);

        return $this->sendResponse($DownloadHistoryObj->toArray(), 'DownloadHistory saved successfully');
    }
}
