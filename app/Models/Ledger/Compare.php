<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Collection;

/**
 * Class Compare
 */
class Compare extends Ledger
{
    public $guarded = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'             => 'int',
        'size'           => 'float',
        'amount'         => 'float',
        'rentable_area'  => 'double',
        'occupied_area'  => 'double',
        'totalGroupSqFt' => 'double',
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
        $payload = [
            'id'                               => md5($this->Entity->id . $this->year . $this->amount),
            'name'                             => $this->Entity->name,
            'amount'                           => $this->amount ?? 0,
            'code'                             => $this->ReportTemplateAccountGroup->report_template_account_group_code,
            'accountName'                      => $this->ReportTemplateAccountGroup->display_name,
            'report_template_account_group_id' => $this->ReportTemplateAccountGroup->id,
            'occupancy'                        => $this->calculateOccupancyPercentage(),
            'size'                             => $this->rentable_area,
            'isGroup'                          => $this->isGroup(),
            'totalGroupSqFt'                   => $this->totalGroupSqFt,
            'grossAmount'                      => $this->getGrossAmount(),
        ];
        $payload += $this->isGroup()
            ?
            ['property_group_id' => $this->Entity->id]
            :
            ['property_id' => $this->Entity->id];
        return $payload;
    }

    /**
     * @return float|int
     */
    protected function getGrossAmount()
    {
        return $this->isGroup()
            ?
            ($this->totalGroupSqFt * $this->amount)
            :
            ($this->rentable_area * $this->amount);
    }

    /**
     * @return bool
     */
    protected function isGroup()
    {
        return strpos(get_class($this->Entity), 'Group') !== false;
    }

    public function calculateOccupancyPercentage(): float
    {
        // only non-null and non-zero areas may be used
        if ( ! empty($this->occupied_area) && ! empty($this->rentable_area))
        {
            return ($this->occupied_area / $this->rentable_area) * 100;
        }
        return 0;
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
