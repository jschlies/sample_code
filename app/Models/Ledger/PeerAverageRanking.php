<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Collection;
use App\Waypoint\Models\Property;

/**
 * Class PeerAverageRanking
 */
class PeerAverageRanking extends Ledger
{
    /** @var  $fillable array */
    public $fillable = [
        'LedgerController',
        'id',
        'client_id',
        'property_id',
        'amount',
        'rank',
        'targetAmount',
        'peerAvgAmount',
        'entityOccupancy',
        'peerAvgOccupancy',
        'target_gross_amount',
        'rentable_target_area',
        'peerAvgArea',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'               => 'string',
        'client_id'        => 'int',
        'property_id'      => 'int',
        'rank'             => 'int',
        'targetAmount'     => 'double',
        'peerAvgAmount'    => 'double',
        'entityOccupancy'  => 'double',
        'peerAvgOccupancy' => 'double',
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
            'id'                 => $this->id,
            'property_id'        => Property::where(['property_id_old' => $this->property_id, 'client_id' => $this->client_id])->value('id'),
            'name'               => Property::where(['property_id_old' => $this->property_id, 'client_id' => $this->client_id])->value('display_name'),
            'amount'             => $this->isComplete() ? $this->amount : 'Incomplete', // this is used for spreadsheets
            'targetYearAmount'   => $this->isComplete() ? $this->amount : 'Incomplete', // this is used for spreadsheets
            'targetAmount'       => $this->targetAmount ? $this->targetAmount : 0,
            'peerAvgAmount'      => $this->peerAvgAmount ? $this->peerAvgAmount : 0,
            'rank'               => $this->rank,
            'entityName'         => $this->LedgerController->entityName,
            'targetYear'         => (int) $this->LedgerController->year,
            'units'              => $this->LedgerController->units,
            'entityOccupancy'    => $this->entityOccupancy ? $this->entityOccupancy : 0,
            'peerAvgOccupancy'   => $this->peerAvgOccupancy,
            'entityGrossAmount'  => $this->target_gross_amount,
            'peerAvgGrossAmount' => $this->peerAvgAmount * $this->peerAvgArea,
            'targetArea'         => (double) $this->rentable_target_area,
            'peerAvgArea'        => (double) $this->peerAvgArea,
        ];
    }

    /**
     * @return bool
     * check for null values and for incomplete data
     */
    private function isComplete(): bool
    {
        return $this->rank != 0 && ! is_null($this->amount);
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
