<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Collection;
use App\Waypoint\Models\Property;

/**
 * Class VarianceRanking
 */
class VarianceRanking extends Ledger
{
    protected $guarded = [];

    public function toArray(): array
    {
        // check for null values and for incomplete data flag the amount with 'Incomplete' for spreadsheets
        if ($this->rank == 0 && is_null($this->amount))
        {
            $this->amount = 'Incomplete';
        }
        return [
            'property_id'                     => Property::where(['property_id_old' => $this->property_id, 'client_id' => $this->client_id])->value('id'),
            'name'                            => Property::where(['property_id_old' => $this->property_id, 'client_id' => $this->client_id])->value('display_name'),
            'code'                            => $this->code,
            'amount'                          => $this->varianceAmount,
            'targetYearAmount'                => $this->targetYearAmount,
            'actualAmount'                    => $this->actualAmount,
            'budgetAmount'                    => $this->budgetAmount,
            'rank'                            => $this->rank,
            'units'                           => $this->LedgerController->units,
            'unitsDisplayText'                => $this->LedgerController->unitsDisplayText,
            'entityName'                      => $this->LedgerController->entityName,
            'targetYear'                      => (int) $this->LedgerController->year,
            'entityOccupancy'                 => $this->entityOccupancy,
            'actualGrossAmount'               => $this->actual_gross_amount,
            'budgetGrossAmount'               => $this->budget_gross_amount,
            'rentable_area'                   => (float) $this->rentable_area,
            'report_template_account_type_id' => $this->report_template_account_group_id,
        ];
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array $models
     * @return \App\Waypoint\Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }
}
