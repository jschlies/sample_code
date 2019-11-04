<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Models\CalculatedFieldEquation;
use Exception;
use FormulaInterpreter;
use App\Waypoint\Models\ReportTemplate;
use Illuminate\Container\Container as Application;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class CalculatedFieldEquationRepository
 * @package App\Waypoint\Repositories
 */
class CalculatedFieldEquationRepository extends CalculatedFieldEquationRepositoryBase
{
    /** @var CalculatedFieldEquationRepository */
    protected $CalculatedFieldRepositoryObj;

    /** @var CalculatedFieldVariableRepository */
    protected $CalculatedFieldVariableRepositoryObj;

    /** @var CalculatedFieldVariableRepository */
    protected $CalculatedFieldEquationPropertyRepositoryObj;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->CalculatedFieldVariableRepositoryObj         = App::make(CalculatedFieldVariableRepository::class);
        $this->CalculatedFieldEquationPropertyRepositoryObj = App::make(CalculatedFieldEquationPropertyRepository::class);
    }

    /**
     * @return string
     */
    public function model()
    {
        return CalculatedFieldEquation::class;
    }

    /**
     * Save a new CalculatedFieldEquation in repository
     *
     * @param array $attributes
     * @return CalculatedFieldEquation
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        if (isset($attributes['equation_name']))
        {
            $attributes['name'] = $attributes['equation_name'];
            unset($attributes['equation_name']);
        }
        if (isset($attributes['equation_description']))
        {
            $attributes['description'] = $attributes['equation_description'];
            unset($attributes['equation_description']);
        }

        $this->CalculatedFieldRepositoryObj = App::make(CalculatedFieldRepository::class);

        /** @var CalculatedField $CalculatedFieldObj */
        if ( ! $CalculatedFieldObj = $this->CalculatedFieldRepositoryObj->findWithoutFail($attributes['calculated_field_id']))
        {
            throw new GeneralException('Invalid calculated_field_id');
        }

        /**
         * if a property_id is passed in, check that an equation with that property DOES NOT EXIST
         * if no property_id is passed in, check that an equation with that no property DOES NOT EXIST
         */
        if (isset($attributes['property_id']))
        {
            if ($CalculatedFieldObj->calculatedFieldEquations->filter(
            /** @var CalculatedFieldEquation $CalculatedFieldEquationObj */
                function ($CalculatedFieldEquationObj) use ($attributes)
                {
                    return in_array($attributes['property_id'], $CalculatedFieldEquationObj->properties->pluck('id')->toArray());
                }
            )->count())
            {
                throw new GeneralException('This property already has a custom equation');
            }
        }
        else
        {
            if ($CalculatedFieldObj->calculatedFieldEquations->filter(
            /** @var CalculatedFieldEquation $CalculatedFieldEquationObj */
                function ($CalculatedFieldEquationObj) use ($attributes)
                {
                    return $CalculatedFieldEquationObj->properties->count() == 0;
                }
            )->count())
            {
                throw new GeneralException('Client default equation already exists');
            }
        }

        $parsed_equation_string_arr = $this->parse_and_validate_equation_string($CalculatedFieldObj->report_template_id, $attributes['equation_string']);

        $attributes['equation_string_parsed']  = $parsed_equation_string_arr['formatted'];
        $attributes['display_equation_string'] = $parsed_equation_string_arr['display'];
        $CalculatedFieldEquationObj            = parent::create($attributes);

        foreach ($parsed_equation_string_arr['vars'] as $equation_var)
        {
            $native_account_id                = null;
            $report_template_account_group_id = null;
            if ($equation_var['var_type'] == 'NA')
            {
                $native_account_id = $equation_var['var_id'];
            }
            elseif ($equation_var['var_type'] == 'RTAG')
            {
                $report_template_account_group_id = $equation_var['var_id'];
            }
            else
            {
                throw new GeneralException('Invalid equation var');
            }
            $this->CalculatedFieldVariableRepositoryObj->create(
                [
                    'calculated_field_equation_id'     => $CalculatedFieldEquationObj->id,
                    'native_account_id'                => $native_account_id,
                    'report_template_account_group_id' => $report_template_account_group_id,
                ]
            );
        }
        if (isset($attributes['property_id']))
        {
            $this->CalculatedFieldEquationPropertyRepositoryObj->create(
                [
                    'calculated_field_equation_id' => $CalculatedFieldEquationObj->id,
                    'property_id'                  => $attributes['property_id'],
                ]
            );
        }
        return $CalculatedFieldEquationObj;
    }

    /**
     * Update a entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return CalculatedFieldEquation
     * @throws ValidatorException
     */
    public
    function update(
        array $attributes,
        $id
    ) {
        return parent::update($attributes, $id);
    }

    public
    function parse_and_validate_equation_string(
        $report_template_id,
        $equation_string
    ) {
        $parsed_equation_arr                 = [];
        $parsed_equation_arr['raw_equation'] = $equation_string;
        $parsed_equation_arr['formatted']    = preg_replace('/[\[\]\s]/', '', $equation_string);
        $parsed_equation_arr['display']      = preg_replace('/[\[\]\s]/', '', $equation_string);
        /**
         * due to quirks in mormat/php-formula-interpreter, '+,-,* and /' must be space padded BUT
         * parenthesis may not be. Sigh.
         */
        $parsed_equation_arr['formatted'] = preg_replace('/([\+\-\*\/])/', ' $1 ', $parsed_equation_arr['formatted']);

        $parsed_equation_arr['vars'] = [];
        /** @var ReportTemplate $ReportTemplateObj */
        $ReportTemplateRepositoryObj = App::make(ReportTemplateRepository::class);
        if ( ! $ReportTemplateObj = $ReportTemplateRepositoryObj->findWithoutFail($report_template_id))
        {
            throw new GeneralException('Invalid report_template_id');
        }
        if ( ! preg_match_all("/\[((?:NA|RTAG)_\d+)\]([\+\-\*\\\ ]*)/", $equation_string, $gleaned))
        {
            throw new GeneralException('Invalid equation_string');
        }
        $var_count = substr_count($equation_string, '_');

        if ((count($gleaned[1])) != $var_count)
        {
            throw new GeneralException('Invalid equation_string');
        }
        if ((count($gleaned[2])) != $var_count)
        {
            throw new GeneralException('Invalid equation_string');
        }

        $parsed_equation_arr['vars'] = [];
        foreach ($gleaned[1] as $item)
        {
            $var            = [];
            $var['raw_var'] = $item;
            if (isset($parsed_equation_arr['vars'][$var['raw_var']]))
            {
                continue;
            }
            $parts = explode('_', $item);
            if (count($parts) !== 2)
            {
                throw new GeneralException('Invalid equation_string bad var ' . $item);
            }
            $var['var_type'] = $parts[0];
            $var['var_id']   = $parts[1];
            if ($var['var_type'] == 'NA')
            {
                if ( ! in_array($var['var_id'], $ReportTemplateObj->getAllNativeAccounts()->getArrayOfIDs()))
                {
                    throw new GeneralException('Invalid equation_string no such native account ' . $item);
                }
                $var['object'] = $ReportTemplateObj->getAllNativeAccounts()->filter(
                    function ($item) use ($var)
                    {
                        return $item->id == $var['var_id'];
                    }
                )->first();

                $parsed_equation_arr['display'] = preg_replace('/' . $item . '/', '"' . $var['object']->native_account_name . '"', $parsed_equation_arr['display']);
            }
            elseif ($var['var_type'] == 'RTAG')
            {
                if ( ! in_array($var['var_id'], $ReportTemplateObj->reportTemplateAccountGroups->getArrayOfIDs()))
                {
                    throw new GeneralException('Invalid equation_string no such report template account group' . $item);
                }
                $var['object']                  = $ReportTemplateObj->reportTemplateAccountGroups->filter(
                    function ($item) use ($var)
                    {
                        return $item->id == $var['var_id'];
                    }
                )->first();
                $parsed_equation_arr['display'] = preg_replace('/' . $item . '/', '"' . $var['object']->report_template_account_group_name . '"', $parsed_equation_arr['display']);
            }
            $parsed_equation_arr['vars'][$var['raw_var']] = $var;
        }

        try
        {
            $name_value_pairs = [];

            $i = 1;
            foreach (array_keys($parsed_equation_arr['vars']) as $var_name)
            {
                //$var_name = preg_replace('/[\[\]\s_]/', '', $var_name);
                $name_value_pairs[$var_name] = $i++;
            }

            /** @var FormulaInterpreter\Compiler $compiler */
            $FormulaInterpreterCompilerObj   = new FormulaInterpreter\Compiler();
            $FormulaInterpreterExecutableObj = $FormulaInterpreterCompilerObj->compile($parsed_equation_arr['formatted']);
            $FormulaInterpreterExecutableObj->run($name_value_pairs);
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 500, $e);
        }
        return $parsed_equation_arr;
    }

}
