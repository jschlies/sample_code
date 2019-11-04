<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class AccessList
 * @package App\Waypoint\Models
 */
class CalculatedFieldEquationFull extends CalculatedFieldEquation
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                       => $this->id,
            'calculated_field_id'      => $this->calculated_field_id,
            "equation_string"          => $this->equation_string,
            "equation_string_parsed"   => $this->equation_string_parsed,
            "display_equation_string"  => $this->display_equation_string,
            "name"                     => $this->name,
            "description"              => $this->description,
            "calculatedFieldVariables" => $this->calculatedFieldVariables->toArray(),
            "properties"               => $this->properties->getArrayOfIDs(),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
