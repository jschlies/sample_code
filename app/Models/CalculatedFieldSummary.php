<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class AccessList
 * @package App\Waypoint\Models
 */
class CalculatedFieldSummary extends CalculatedField
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                        => $this->id,
            'name'                      => $this->name,
            "report_template_id"        => $this->report_template_id,
            "advancedVarianceLineItems" => $this->advancedVarianceLineItems->getArrayOfIDs(),
            "calculatedFieldEquations"  => $this->calculatedFieldEquations->getArrayOfIDs(),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}