<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Collection;
use App\Waypoint\Http\Controllers\Api\Ledger\LedgerController;
use Carbon\Carbon;

/**
 * Class Ledger
 */
class Metadata extends Ledger
{

    /** @var  $fillable array */
    public $guarded = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray($isNativeChartOfAccounts = false): array
    {
        if ($isNativeChartOfAccounts)
        {
            $return = [
                'apiTitle'       => $this->LedgerController->apiTitle,
                'as_of_date'     => $this->as_of_date,
                'run_date'       => Carbon::now()->format(LedgerController::SPREADSHEET_DATE_FORMAT),
                'apiDisplayName' => $this->formatTitleCase($this->LedgerController->apiDisplayName),
            ];
        }
        else
        {
            $return = [
                'apiTitle'                    => $this->LedgerController->apiTitle,
                'apiDisplayName'              => $this->formatTitleCase($this->LedgerController->apiDisplayName),
                'unitsDisplayText'            => $this->LedgerController->unitsDisplayText,
                'report'                      => $this->formatTitleCase($this->LedgerController->report),
                'period'                      => $this->formatPeriodTypeString($this->LedgerController->period),
                'targetYear'                  => isset($this->LedgerController->targetYear) ? $this->LedgerController->targetYear : $this->LedgerController->year,
                'previousYear'                => $this->LedgerController->previousYear ? (int) $this->LedgerController->previousYear : null,
                'area'                        => $this->formatTitleCase($this->LedgerController->area),
                'count'                       => (int) $this->count,
                'target'                      => $this->target,
                'expenseCodeName'             => $this->formatTitleCase($this->ReportTemplateAccountGroupObj->display_name),
                'lineage'                     => $this->LedgerController->getLineage(),
                'currentData'                 => (bool) $this->status == 'COMPLETED',
                'incompleteDataPropertyCount' => (int) $this->incompleteDataPropertyCount,
                'units'                       => $this->LedgerController->units,
            ];
        }

        if ($this->Property || $this->PropertyGroup)
        {
            if ($this->PropertyGroup && $this->PropertyGroup->is_all_property_group)
            {
                $return['entityName'] = $return['name'] = 'My Portfolio';
            }
            else
            {
                // TODO: remove duplicate entry - duplicate only a near term bridge
                $return['entityName'] = $return['name'] = is_null($this->Property) ? $this->PropertyGroup->name : $this->Property->display_name;
            }
        }
        else
        {
            // TODO: remove duplicate entry - duplicate only a near term bridge
            $return['entityName'] = $return['name'] = $this->LedgerController->entityName;
        }

        return $return;
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
