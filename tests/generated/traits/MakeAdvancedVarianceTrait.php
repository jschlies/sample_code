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
use App\Waypoint\Models\AdvancedVariance;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeAdvancedVarianceTrait
{
    /**
     * Create fake instance of AdvancedVariance and save it in database
     *
     * @param array $advanced_variances_arr
     * @return AdvancedVariance
     */
    public function makeAdvancedVariance($advanced_variances_arr = [])
    {
        $theme = $this->fakeAdvancedVarianceData($advanced_variances_arr);
        return $this->AdvancedVarianceRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of AdvancedVariance
     *
     * @param array $advanced_variances_arr
     * @return AdvancedVariance
     */
    public function fakeAdvancedVariance($advanced_variances_arr = [])
    {
        return new AdvancedVariance($this->fakeAdvancedVarianceData($advanced_variances_arr));
    }

    /**
     * Get fake data of AdvancedVariance
     *
     * @param array $advanced_variances_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeAdvancedVarianceData($advanced_variances_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($advanced_variances_arr);
        return $factory->raw(AdvancedVariance::class, $advanced_variances_arr, $factory_name);
    }
}