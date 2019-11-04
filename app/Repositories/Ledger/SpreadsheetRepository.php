<?php
/**
 * Created by PhpStorm.
 * User: ALEX
 * Date: 1/24/17
 * Time: 3:36 PM
 */

namespace App\Waypoint\Repositories\Ledger;

use App\Waypoint\Models\Spreadsheet;

class SpreadsheetRepository extends LedgerRepository
{

    /**
     * @var array
     */
    protected $fieldSearchable = [

    ];

    /**
     * @return mixed
     **/
    public function model()
    {
        return Spreadsheet::class;
    }
}