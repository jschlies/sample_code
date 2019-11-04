<?php

namespace App\Waypoint\Repositories\Ledger;

use App\Waypoint\Collection;
use App\Waypoint\Models\Ledger\PeerAveragePropertyGroupRanking;

/**
 * Class PeerAverageRankingRepository
 */
class PeerAverageRankingRepository extends LedgerRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [];

    public function getGroupData()
    {
        $payload = new Collection();

        return $payload;
    }

    /**
     * Configure the Model
     **/
    public function model()
    {
        return PeerAveragePropertyGroupRanking::class;
    }
}
