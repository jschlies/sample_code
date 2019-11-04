<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Collection;

/**
 * Class Occupancy
 */
class Occupancy extends Ledger
{

    /** @var  $fillable array */
    public $fillable = [
        'RENTABLE_AREA',
        'OCCUPIED_AREA',
        'PERCENT_OCC',
        'asOfDate',
        'AMT',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'RENTABLE_AREA' => 'float',
        'OCCUPIED_AREA' => 'float',
        'PERCENT_OCC'   => 'float',
        'AMT'           => 'float',
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
            'rentableArea'        => $this->RENTABLE_AREA,
            'occupiedArea'        => $this->OCCUPIED_AREA,
            'occupancyPercentage' => $this->PERCENT_OCC,
            'asOfDate'            => $this->asOfDate,
            'asOfDateFormatted'   => $this->asOfDate->format('F j, Y'),
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
