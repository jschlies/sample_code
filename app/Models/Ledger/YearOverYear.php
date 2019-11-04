<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Collection;

/**
 * Class YearOverYear
 */
class YearOverYear extends Ledger
{
    /** @var  $fillable array */
    public $guarded = [];

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            'id'                               => $this->id,
            'name'                             => $this->formatName(strtolower($this->name)),
            'code'                             => $this->code,
            'targetYear'                       => $this->targetYear,
            'previousYear'                     => $this->previousYear,
            'amount'                           => $this->amount,
            'monthly_data'                     => $this->monthly_data,
            'targetYearAmount'                 => $this->targetYearAmount,
            'previousYearAmount'               => $this->previousYearAmount,
            'units'                            => $this->LedgerController->units,
            'unitsDisplayText'                 => $this->LedgerController->unitsDisplayText,
            'entityName'                       => $this->LedgerController->entityName,
            'totalBarUnits'                    => 'change',
            'targetYearOccupancy'              => $this->targetYearOccupancy,
            'previousYearOccupancy'            => $this->previousYearOccupancy,
            'grossAmountTargetYear'            => $this->gross_amount_target_year,
            'grossAmountPreviousYear'          => $this->gross_amount_previous_year,
            'squareFootageTargetYear'          => $this->squareFootageTargetYear,
            'squareFootagePreviousYear'        => $this->squareFootagePreviousYear,
            'report_template_account_group_id' => $this->report_template_account_group_id,
            'native_account_type_coefficient'  => $this->native_account_type_coefficient,
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
