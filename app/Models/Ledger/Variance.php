<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Collection;

/**
 * Class Variance
 */
class Variance extends Ledger
{
    /** @var  $fillable array */
    public $guarded = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'              => 'string',
        'name'            => 'string',
        'actual'          => 'double',
        'budget'          => 'double',
        'targetYear'      => 'int',
        'entityOccupancy' => 'double',
        'childCount'      => 'int',
        'rentable_area'   => 'float',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [];

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
            'actual'                           => is_null($this->actual) ? 0 : $this->actual,
            'budget'                           => is_null($this->budget) ? 0 : $this->budget,
            'units'                            => $this->LedgerController->units,
            'unitsDisplayText'                 => $this->LedgerController->unitsDisplayText,
            'entityName'                       => $this->LedgerController->entityName,
            'targetYear'                       => (int) $this->LedgerController->year,
            'totalBarUnits'                    => 'change',
            'entityOccupancy'                  => $this->entityOccupancy,
            'childCount'                       => isset($this->childCount) ? $this->childCount : 1,
            'budgetGrossAmount'                => $this->budget_gross_amount,
            'actualGrossAmount'                => $this->actual_gross_amount,
            'area'                             => $this->area,
            'rentable_area'                    => $this->rentable_area,
            'report_template_account_group_id' => $this->report_template_account_group_id,
            'native_account_type_id'           => $this->native_account_type_id,
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
