<?php

namespace App\Waypoint\Repositories\Ledger;

use App\Waypoint\Models\Ledger\Variance;

/**
 * Class VarianceRepository
 */
class VarianceRepository extends LedgerRepository
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
        return Variance::class;
    }
}
