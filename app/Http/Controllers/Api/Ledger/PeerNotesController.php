<?php

namespace App\Waypoint\Http\Controllers\Api\Ledger;

use App\Waypoint\Repositories\Ledger\LedgerRepository;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Property;
use Illuminate\Support\Facades\Response;
use App\Waypoint\ResponseUtil;

class PeerNotesController extends LedgerController
{
    protected $RepositoryObj = null;

    public function __construct(LedgerRepository $RepoObj)
    {
        $this->RepositoryObj = $RepoObj;
        parent::__construct($RepoObj);
    }

    /**
     * @param $client_id
     * @param $property_id
     * @param $year
     * @param $area
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function index($client_id, $property_id, $year, $area)
    {
        try
        {
            $this->ClientObj = $this->RepositoryObj->ClientObj = $this->getClientObject();

            /** @var Property $Property */
            if ( ! $this->RepositoryObj->PropertyObj = Property::find($property_id))
            {
                throw new GeneralException('property_id id invalid', self::HTTP_ERROR_RESPONSE_CODE);
            }

            $this->RepositoryObj->year = $year;
            $this->RepositoryObj->area = $area;

            if ( ! $payload = $this->RepositoryObj->getPeerAverageNote())
            {
                return Response::json(ResponseUtil::makeError('Could not find peer data for this property and year'), 400);
            }

            return $this->sendResponse($payload, 'property peer details produced successfully');
        }
        catch (GeneralException $e)
        {
            return $this->sendResponse([], $e->getMessage());
        }
    }
}
