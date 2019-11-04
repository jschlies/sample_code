<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Collection;

/**
 * Class PeerAverage
 */
class PeerAverage extends Ledger
{
    public $guarded = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'               => 'string',
        'code'             => 'string',
        'amount'           => 'double',
        'name'             => 'string',
        'childCount'       => 'int',
        'targetAmount'     => 'double',
        'peerAvgAmount'    => 'double',
        'peerAvgOccupancy' => 'double',
        'entityOccupancy'  => 'double',
        'targetArea'       => 'double',
        'peerAvgArea'      => 'double',
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
            'amount'                           => $this->amount,
            'childCount'                       => $this->childCount,
            'targetYear'                       => (int) $this->LedgerController->year,
            'entityName'                       => $this->LedgerController->entityName,
            'entityType'                       => $this->LedgerController->entityType,
            'units'                            => $this->LedgerController->units,
            'totalBarUnits'                    => 'expense',
            'targetAmount'                     => $this->targetAmount,
            'peerAvgAmount'                    => $this->peerAvgAmount,
            'peerAvgOccupancy'                 => $this->peerAvgOccupancy,
            'entityOccupancy'                  => $this->entityOccupancy,
            'entityGrossAmount'                => $this->target_gross_amount,
            'peerAvgGrossAmount'               => $this->peerAvgAmount * $this->peerAvgArea,
            'targetArea'                       => $this->targetArea,
            'peerAvgArea'                      => $this->peerAvgArea,
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
