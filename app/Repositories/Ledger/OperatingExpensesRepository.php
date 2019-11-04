<?php

namespace App\Waypoint\Repositories\Ledger;

use App\Waypoint\Models\Ledger\OperatingExpenses;

/**
 * Class OperatingExpensesRankingRepository
 */
class OperatingExpensesRepository extends LedgerRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [];

    /**
     * Configure the Model
     **/
    public function model()
    {
        return OperatingExpenses::class;
    }
}
