<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Collection;
use App\Waypoint\Models\Property;

/**
 * Class OperatingExpensesRanking
 * @package App\Waypoint\Models\Ledger
 */
class OperatingExpensesRanking extends Ledger
{
    /** @var  $fillable array */
    public $fillable = [
        'LedgerController',
        'PropertyGroup',
        'id',
        'client_id',
        'property_id',
        'amount',
        'rank',
        'occupancy',
        'year',
        'targetYear',
        'gross_amount',
        'area',
        'areaType',
        'rentable_area',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'            => 'string',
        'client_id'     => 'int',
        'property_id'   => 'int',
        'rank'          => 'int',
        'occupancy'     => 'float',
        'year'          => 'int',
        'targetYear'    => 'int',
        'rentable_area' => 'float',
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
        // check for null values and for incomplete data flag the amount with 'Incomplete' for spreadsheets
        if ($this->rank == 0 && is_null($this->amount))
        {
            $this->amount = 'Incomplete';
        }

        return [
            'id'               => $this->id,
            'property_id'      => Property::where(['property_id_old' => $this->property_id, 'client_id' => $this->client_id])->value('id'),
            'name'             => Property::where(['property_id_old' => $this->property_id, 'client_id' => $this->client_id])->value('display_name'),
            'amount'           => $this->amount,
            'targetYearAmount' => $this->amount,
            'rank'             => $this->rank,
            'targetYear'       => isset($this->targetYear) ? $this->targetYear : $this->year,
            'entityName'       => isset($this->PropertyGroup->is_all_property_group) && (bool) $this->PropertyGroup->is_all_property_group ? 'My Portfolio' : $this->LedgerController->entityName,
            'units'            => $this->LedgerController->units,
            'occupancy'        => $this->occupancy,
            'grossAmount'      => $this->gross_amount,
            'area'             => (float) $this->area,
            'areaType'         => $this->areaType,
            'rentable_area'    => $this->rentable_area,
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
