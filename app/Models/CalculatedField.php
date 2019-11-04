<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\AuditableTrait;
use App\Waypoint\Repositories\ReportTemplateAccountGroupRepository;
use Log;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;

/**
 * Class CalculatedField
 * @package App\Waypoint\Models
 */
class CalculatedField extends CalculatedFieldModelBase implements AuditableContract, UserResolver
{
    use AuditableTrait;

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                               => $this->id,
            'name'                             => $this->name,
            'description'                      => $this->description,
            'sort_order'                       => $this->sort_order,
            "is_summary"                       => $this->is_summary,
            "is_summary_tab_default_line_item" => $this->is_summary_tab_default_line_item,
            "report_template_id"               => $this->report_template_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'report_template_id',
        'name',
        'description',
    ];

    /**
     * @param integer $property_id
     * @return CalculatedFieldEquation|boolean
     */
    public function calculatedFieldEquationsForProperty($property_id)
    {
        if ($this->calculatedFieldEquations->count() == 1)
        {
            return $this->calculatedFieldEquations->first();
        }
        if ($CalculatedFieldEquationObj = $this->calculatedFieldEquations->filter(
            function ($CalculatedFieldEquationObj) use ($property_id)
            {
                $CalculatedFieldEquationObj->calculatedFieldEquationProperties->filter(
                    function ($CalculatedFieldEquationProperty) use ($property_id)
                    {
                        return $CalculatedFieldEquationProperty->property_id == $property_id;
                    }
                );
            }
        )->first()
        )
        {
            return $CalculatedFieldEquationObj;
        }
        elseif ($CalculatedFieldEquationObj = $this->calculatedFieldEquations->filter(
            function ($CalculatedFieldEquationObj) use ($property_id)
            {
                $CalculatedFieldEquationObj->calculatedFieldEquationProperties->filter(
                    function ($CalculatedFieldEquationProperty) use ($property_id)
                    {
                        return $CalculatedFieldEquationProperty->property_id == null;
                    }
                );
            }
        )->first()
        )
        {
            return $CalculatedFieldEquationObj;
        }
        elseif ($CalculatedFieldEquationObj = $this->calculatedFieldEquations->filter(
            function ($CalculatedFieldEquationObj) use ($property_id)
            {
                return $CalculatedFieldEquationObj->calculatedFieldEquationProperties->count() == 0;
            }
        )->first()
        )
        {
            return $CalculatedFieldEquationObj;
        }
        Log::error('No $CalculatedFieldEquationObj found for property_id = ' . $property_id . ' in  calculatedField_id= ' . $this->id);
        return false;
    }

    public function get_native_account_id_arr()
    {
        /** @var ReportTemplateAccountGroupRepository $ReportTemplateAccountGroupRepositoryObj */
        $ReportTemplateAccountGroupRepositoryObj = App::make(ReportTemplateAccountGroupRepository::class);

        /**
         * populate $native_account_id_arr with all $native_account_id needed for $CalculatedFieldObj
         */
        $native_account_id_arr = [];
        foreach ($this->calculatedFieldEquations as $CalculatedFieldEquationObj)
        {
            foreach ($CalculatedFieldEquationObj->calculatedFieldVariables as $CalculatedFieldVariableObj)
            {
                if ($CalculatedFieldVariableObj->native_account_id)
                {
                    $native_account_id_arr[] = $CalculatedFieldVariableObj->native_account_id;
                }
                elseif ($CalculatedFieldVariableObj->report_template_account_group_id)
                {
                    /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
                    $ReportTemplateAccountGroupObj = $ReportTemplateAccountGroupRepositoryObj->find($CalculatedFieldVariableObj->report_template_account_group_id);
                    $native_account_id_arr         = array_merge($native_account_id_arr, $ReportTemplateAccountGroupObj->get_native_account_id_arr());
                }
            }
        }

        return array_unique($native_account_id_arr);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function calculatedFieldEquationsSummary()
    {
        return $this->hasMany(
            CalculatedFieldEquationSummary::class,
            'calculated_field_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function calculatedFieldEquationsFull()
    {
        return $this->hasMany(
            CalculatedFieldEquationFull::class,
            'calculated_field_id',
            'id'
        );
    }
}
