<?php

namespace App\Waypoint\Models;

use App\Waypoint\AuditableTrait;
use App\Waypoint\CommentableTrait;
use App\Waypoint\Exceptions\GeneralException;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Contracts\UserResolver;

/**
 * Class EcmProject
 * @package App\Waypoint\Models
 */
class EcmProject extends EcmProjectModelBase implements AuditableContract, UserResolver
{
    use AuditableTrait;

    use CommentableTrait;

    /** @var array */
    public static $rules = [
        'property_id'                     => 'required|integer|unique_with:ecm_projects,name,object_id',
        'name'                            => 'required|string|min:2|max:255',
        'description'                     => 'sometimes|nullable|string|max:255',
        'project_summary'                 => 'required|string|min:2|max:65535',
        'estimated_start_date'            => 'sometimes',
        'estimated_completion_date'       => 'sometimes',
        'costs'                           => 'sometimes|numeric',
        'estimated_incentive'             => 'sometimes|numeric',
        'estimated_annual_savings'        => 'sometimes|numeric',
        'estimated_annual_energy_savings' => 'sometimes|numeric',
    ];

    const PROJECT_CATEGORY_APPLIANCES                = 'Appliances';
    const PROJECT_CATEGORY_BOILER_AND_STEAM_SYSTEMS  = 'Boiler and Steam Systems';
    const PROJECT_CATEGORY_BUILDING_SHELL            = 'Building Shell';
    const PROJECT_CATEGORY_CROSS_PORTFOLIO           = 'Cross Portfolio';
    const PROJECT_CATEGORY_ELECTRONICS_AND_IT        = 'Electronics and IT';
    const PROJECT_CATEGORY_ENERGY_MANAGEMENT_SYSTEMS = 'Energy Management Systems';
    const PROJECT_CATEGORY_FOOD_SERVICE_TECHNOLOGY   = 'Food Service Technology';
    const PROJECT_CATEGORY_HVAC                      = 'HVAC';
    const PROJECT_CATEGORY_INDUSTRIAL_SYSTEMS        = 'Industrial Systems';
    const PROJECT_CATEGORY_LIGHTING                  = 'Lighting';
    const PROJECT_CATEGORY_MOTORS                    = 'Motors';
    const PROJECT_CATEGORY_PUMPS_AND_FANS            = 'Pumps and Fans';
    const PROJECT_CATEGORY_REFRIGERATION             = 'Refrigeration';
    const PROJECT_CATEGORY_OTHER                     = 'Other';

    public static $project_category_arr = [
        self::PROJECT_CATEGORY_APPLIANCES,
        self::PROJECT_CATEGORY_BOILER_AND_STEAM_SYSTEMS,
        self::PROJECT_CATEGORY_BUILDING_SHELL,
        self::PROJECT_CATEGORY_CROSS_PORTFOLIO,
        self::PROJECT_CATEGORY_ELECTRONICS_AND_IT,
        self::PROJECT_CATEGORY_ENERGY_MANAGEMENT_SYSTEMS,
        self::PROJECT_CATEGORY_FOOD_SERVICE_TECHNOLOGY,
        self::PROJECT_CATEGORY_HVAC,
        self::PROJECT_CATEGORY_INDUSTRIAL_SYSTEMS,
        self::PROJECT_CATEGORY_LIGHTING,
        self::PROJECT_CATEGORY_MOTORS,
        self::PROJECT_CATEGORY_PUMPS_AND_FANS,
        self::PROJECT_CATEGORY_REFRIGERATION,
        self::PROJECT_CATEGORY_OTHER,
    ];

    const PROJECT_STATUS_NEW                       = 'New';
    const PROJECT_STATUS_IN_REVIEW                 = 'In Review';
    const PROJECT_STATUS_QUEUED_FOR_IMPLEMENTATION = 'Queued for Implementation';
    const PROJECT_STATUS_IN_PROGRESS               = 'In Progress';
    const PROJECT_STATUS_ON_HOLD                   = 'On Hold';
    const PROJECT_STATUS_COMPLETE                  = 'Complete';
    const PROJECT_STATUS_NOT_DOING                 = 'Not Doing';
    public static $project_status_arr = [
        self::PROJECT_STATUS_NEW,
        self::PROJECT_STATUS_IN_REVIEW,
        self::PROJECT_STATUS_QUEUED_FOR_IMPLEMENTATION,
        self::PROJECT_STATUS_IN_PROGRESS,
        self::PROJECT_STATUS_ON_HOLD,
        self::PROJECT_STATUS_COMPLETE,
        self::PROJECT_STATUS_NOT_DOING,
    ];

    const ENERGY_UNITS_KWH    = 'kWh';
    const ENERGY_UNITS_THERMS = 'Therms';
    const ENERGY_UNITS_KBTU   = 'kBtu';
    public static $energy_units_arr = [
        self::ENERGY_UNITS_KWH,
        self::ENERGY_UNITS_THERMS,
        self::ENERGY_UNITS_KBTU,
    ];

    /**
     * @var array
     * See http://www.laravel-auditing.com/docs/4.0/behavior-settings
     */
    protected $auditInclude = [
        'name',
        'property_id',
        'description',
        'project_category',
        'project_status',
        'costs',
        'estimated_incentive',
        'estimated_annual_savings',
        'estimated_annual_energy_savings',
        'energy_units',
        'project_summary',
        'estimated_start_date',
        'estimated_completion_date',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @return bool
     */
    public function validate()
    {
        return parent::validate();
    }

    /**
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(self::$baseRules, self::$rules);
        }
        $rules                     = parent::get_model_rules($rules, $object_id);
        $rules['project_category'] = 'required|string|in:' . implode(',', EcmProject::$project_category_arr);
        $rules['project_status']   = 'required|string|in:' . implode(',', EcmProject::$project_status_arr);
        $rules['energy_units']     = 'sometimes|string|in:' . implode(',', array_merge(EcmProject::$energy_units_arr, ['']));
        return $rules;
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id"                              => $this->id,
            "property_id"                     => $this->property_id,
            "property_name"                   => Property::where('id', $this->property_id)->value('display_name'),
            "name"                            => $this->name,
            "description"                     => $this->description,
            "project_category"                => $this->project_category,
            "project_status"                  => $this->project_status,
            "costs"                           => $this->costs,
            "estimated_incentive"             => $this->estimated_incentive,
            "estimated_annual_savings"        => $this->estimated_annual_savings,
            "estimated_annual_energy_savings" => $this->estimated_annual_energy_savings,
            "energy_units"                    => $this->energy_units,
            "project_summary"                 => $this->project_summary,
            "estimated_start_date"            => $this->perhaps_format_date($this->estimated_start_date),
            "estimated_completion_date"       => $this->perhaps_format_date($this->estimated_completion_date),
            "comments"                        => $this->getComments()->toArray(),

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
