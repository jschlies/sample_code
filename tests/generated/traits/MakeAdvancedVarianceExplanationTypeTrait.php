<?php

namespace App\Waypoint\Tests\Generated;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Seeder;
use App\Waypoint\Models\AdvancedVarianceExplanationType;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeAdvancedVarianceExplanationTypeTrait
{
    /**
     * Create fake instance of AdvancedVarianceExplanationType and save it in database
     *
     * @param array $advanced_variance_explanation_types_arr
     * @return AdvancedVarianceExplanationType
     */
    public function makeAdvancedVarianceExplanationType($advanced_variance_explanation_types_arr = [])
    {
        $theme = $this->fakeAdvancedVarianceExplanationTypeData($advanced_variance_explanation_types_arr);
        return $this->AdvancedVarianceExplanationTypeRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of AdvancedVarianceExplanationType
     *
     * @param array $advanced_variance_explanation_types_arr
     * @return AdvancedVarianceExplanationType
     */
    public function fakeAdvancedVarianceExplanationType($advanced_variance_explanation_types_arr = [])
    {
        return new AdvancedVarianceExplanationType($this->fakeAdvancedVarianceExplanationTypeData($advanced_variance_explanation_types_arr));
    }

    /**
     * Get fake data of AdvancedVarianceExplanationType
     *
     * @param array $advanced_variance_explanation_types_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeAdvancedVarianceExplanationTypeData($advanced_variance_explanation_types_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($advanced_variance_explanation_types_arr);
        return $factory->raw(AdvancedVarianceExplanationType::class, $advanced_variance_explanation_types_arr, $factory_name);
    }
}