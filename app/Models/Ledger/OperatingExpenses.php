<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Collection;
use App\Waypoint\Repositories\ReportTemplateAccountGroupRepository;

/**
 * Class OperatingExpenses
 */
class OperatingExpenses extends Ledger
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
        'code'            => 'string',
        'amount'          => 'float',
        'name'            => 'string',
        'childCount'      => 'int',
        'entityOccupancy' => 'float',
        'grossAmount'     => 'float',
        'area'            => 'float',
        'targetYear'      => 'int',
        'year'            => 'int',
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
            'name'                             => $this->formatName($this->name),
            'code'                             => $this->code,
            'report_template_account_group_id' => $this->report_template_account_group_id,
            'amount'                           => $this->amount,
            'targetYearAmount'                 => $this->amount,
            'childCount'                       => $this->childCount,
            'targetYear'                       => isset($this->LedgerController->targetYear) ? $this->LedgerController->targetYear : $this->LedgerController->year,
            'entityName'                       => $this->isAllPropertyGroup() ? 'My Portfolio' : $this->LedgerController->entityName,
            'units'                            => $this->LedgerController->units,
            'totalBarUnits'                    => 'expense',
            'entityOccupancy'                  => $this->entityOccupancy,
            'area'                             => (float) $this->area,
            'rentable_area'                    => $this->rentable_area,
            'areaType'                         => $this->areaType,
            'grossAmount'                      => $this->grossAmount,
        ];
    }

    /**
     * @return bool
     */
    private function isAllPropertyGroup()
    {
        return isset($this->PropertyGroup->is_all_property_group) && (bool) $this->PropertyGroup->is_all_property_group;
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
