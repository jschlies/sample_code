<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\CalculatedField;
use Illuminate\Container\Container as Application;

/**
 * Class CalculatedFieldRepository
 * @package App\Waypoint\Repositories
 */
class CalculatedFieldRepository extends CalculatedFieldRepositoryBase
{
    /** @var CalculatedFieldEquationRepository */
    protected $CalculatedFieldEquationRepositoryObj;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    /**
     * @param array $attributes
     * @return CalculatedField
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        if ( ! isset($attributes['report_template_id']) || ! $attributes['report_template_id'])
        {
            throw new GeneralException('equation_string is required');
        }
        if (isset($attributes['property_id']))
        {
            throw new GeneralException('Cannot pass property_id then creating CalculatedField');
        }

        /**
         * equation_string, equation_name and equation_description are really attrs of CalculatedFieldEquation but
         * since we want to allow swift and FULL creation of CalculatedField, we allow them here
         */
        $calculated_field_attributes = $attributes;
        unset($calculated_field_attributes['equation_string']);
        unset($calculated_field_attributes['equation_name']);
        unset($calculated_field_attributes['equation_description']);
        $CalculatedFieldObj = parent::create($calculated_field_attributes);
        if ( ! isset($attributes['equation_string']))
        {
            return $CalculatedFieldObj;
        }
        $attributes['calculated_field_id'] = $CalculatedFieldObj->id;

        $this->CalculatedFieldEquationRepositoryObj = App::make(CalculatedFieldEquationRepository::class);
        $this->CalculatedFieldEquationRepositoryObj->create($attributes);

        return $CalculatedFieldObj;
    }

    /**
     * @param array $attributes
     * @param int $id
     * @return CalculatedField
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(array $attributes, $id)
    {
        if (isset($attributes['equation_string']))
        {
            throw new GeneralException('You cannot pass a equation_string into this method' . __FILE__ . ':' . __LINE__);
        }
        return parent::update($attributes, $id);
    }

    /**
     * @return string
     */
    public function model()
    {
        return CalculatedField::class;
    }
}
