<?php

namespace App\Waypoint\Repositories\Ledger;

use App\Waypoint\Models\Ledger\Compare;

/**
 * Class CompareRepository
 */
class CompareRepository extends LedgerRepository
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
        return Compare::class;
    }
}
