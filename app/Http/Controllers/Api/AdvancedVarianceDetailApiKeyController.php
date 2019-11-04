<?php

namespace App\Waypoint\Http\Controllers\Api;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiGuardController;
use App\Waypoint\Http\Requests\Generated\Api\CreateAdvancedVarianceRequest;
use App\Waypoint\Repositories\AdvancedVarianceApprovalRepository;
use App\Waypoint\Repositories\AdvancedVarianceLineItemRepository;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\RelatedUserRepository;
use App;
use Illuminate\Http\JsonResponse;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class AdvancedVarianceDetailController
 */
class AdvancedVarianceDetailApiKeyController extends ApiGuardController
{
    /** @var  AdvancedVarianceRepository */
    private $AdvancedVarianceRepositoryObj;
    /** @var  AdvancedVarianceRepository */
    private $AdvancedVarianceLineItemRepositoryObj;
    /** @var  RelatedUserRepository */
    private $RelatedUserRepositoryObj;

    public function __construct(AdvancedVarianceRepository $AdvancedVarianceRepositoryObj)
    {
        parent::__construct();
        $this->AdvancedVarianceRepositoryObj         = $AdvancedVarianceRepositoryObj;
        $this->AdvancedVarianceLineItemRepositoryObj = App::make(AdvancedVarianceLineItemRepository::class);
        $this->AdvancedVarianceApprovalRepositoryObj = App::make(AdvancedVarianceApprovalRepository::class);
        $this->RelatedUserRepositoryObj              = App::make(RelatedUserRepository::class);
    }

    /**
     * Store a newly created AdvancedVariance in storage.
     *
     * @param CreateAdvancedVarianceRequest $AdvancedVarianceRequestObj
     * @return JsonResponse
     * @throws GeneralException
     * @throws ValidatorException
     * @throws \Exception
     */
    public function store($client_id, $property_id, CreateAdvancedVarianceRequest $AdvancedVarianceRequestObj)
    {
        $input                = $AdvancedVarianceRequestObj->all();
        $input['property_id'] = $property_id;

        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->create($input);

        $this->post_job_to_queue(
            [
                'advanced_variance_id' => $AdvancedVarianceObj->id,
            ],
            App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
            config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
        );

        return $this->sendResponse($AdvancedVarianceObj->toArray(), 'AdvancedVariance saved successfully');
    }
}
