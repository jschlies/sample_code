<?php

namespace App\Waypoint\Tests;

use \Illuminate\Database\Eloquent\Factory AS FactoryBase;

class Factory extends FactoryBase
{
    private $provided_values_arr;

    /**
     * @return mixed
     */
    public function getProvidedValuesArr()
    {
        return $this->provided_values_arr;
    }

    /**
     * @param mixed $provided_values_arr
     */
    public function setProvidedValuesArr($provided_values_arr)
    {
        $this->provided_values_arr = $provided_values_arr;
    }

    /**
     * Get the raw attribute array for a given model.
     *
     * This overrides parent:: so we can avoid constantly passing $FakerObj
     *
     * @param string $class
     * @param array $attributes
     * @param string $name
     * @return array
     */
    public function raw($class, array $attributes = [], $name = 'default')
    {
        return array_merge(
            call_user_func($this->definitions[$class][$name], $attributes), $attributes
        );
    }

    /**
     * Create a builder for the given model.
     *
     * This overrides parent:: so we can use App\Waypoint\Tests\FactoryBuilder
     *
     * @param string $class
     * @param string $name
     * @return \Illuminate\Database\Eloquent\FactoryBuilder
     */
    public function of($class, $name = 'default')
    {
        return new FactoryBuilder($class, $name, $this->definitions, $this->states, null);
    }
}
