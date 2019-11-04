<?php

namespace App\Waypoint\Repositories\Ledger;

use App\Waypoint\Models\Ledger\PeerAverage;

/**
 * Class PeerAverageRankingRepository
 */
class PeerAverageRepository extends LedgerRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [

    ];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return PeerAverage::class;
    }
}
