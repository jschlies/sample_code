<?php

namespace App\Waypoint\Tests;

use Illuminate\Database\Eloquent\FactoryBuilder as FactoryBuilderBase;
use \Faker\Generator as Faker_Generator;

class FactoryBuilder extends FactoryBuilderBase
{
    /**
     * Create an new builder instance.
     *
     * @param string $class
     * @param string $name
     * @param array $definitions
     * @param array $states
     * @param \Faker\Generator $faker
     * @return void
     */
    public function __construct($class, $name, array $definitions, array $states, Faker_Generator $faker = null)
    {
        $this->name        = $name;
        $this->class       = $class;
        $this->faker       = $faker;
        $this->states      = $states;
        $this->definitions = $definitions;
    }

    /**
     * Get a raw attributes array for the model.
     *
     * @param array $attributes
     * @return mixed
     */
    protected function getRawAttributes(array $attributes = [])
    {
        $definition = call_user_func(
            $this->definitions[$this->class][$this->name],
            $attributes
        );

        return $this->expandAttributes(
            array_merge($this->applyStates($definition, $attributes), $attributes)
        );
    }
}
