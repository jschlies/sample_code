<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Collection;
use App\Waypoint\Models\Property;

/**
 * Class YearOverYearRanking
 * @package App\Waypoint\Models\Ledger
 */
class YearOverYearRanking extends Ledger
{
    protected $guarded = [];

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {

        // check for null values and for incomplete data flag the amount with 'Incomplete' for spreadsheets
        if ($this->rank == 0 && is_null($this->amount))
        {
            $this->amount = 'Incomplete';
        }
        return [
            'id'                               => $this->id,
            'property_id'                      => Property::where(['property_id_old' => $this->property_id, 'client_id' => $this->client_id])->value('id'),
            'name'                             => Property::where(['property_id_old' => $this->property_id, 'client_id' => $this->client_id])->value('display_name'),
            'code'                             => $this->code,
            'targetYear'                       => $this->targetYear,
            'previousYear'                     => $this->previousYear,
            'targetYearAmount'                 => $this->targetYearAmount,
            'previousYearAmount'               => $this->previousYearAmount,
            'amount'                           => $this->amount,
            'rank'                             => $this->rank,
            'unitsDisplayText'                 => $this->LedgerController->unitsDisplayText,
            'units'                            => $this->LedgerController->units,
            'entityName'                       => $this->LedgerController->entityName,
            'targetYearOccupancy'              => $this->targetYearOccupancy,
            'previousYearOccupancy'            => $this->previousYearOccupancy,
            'grossAmountPreviousYear'          => $this->gross_amount_previous_year,
            'grossAmountTargetYear'            => $this->gross_amount_target_year,
            'squareFootageTargetYear'          => $this->squareFootageTargetYear,
            'squareFootagePreviousYear'        => $this->squareFootagePreviousYear,
            'report_template_account_group_id' => $this->report_template_account_group_id,
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
