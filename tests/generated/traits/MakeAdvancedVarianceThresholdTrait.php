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
use App\Waypoint\Models\AdvancedVarianceThreshold;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeAdvancedVarianceThresholdTrait
{
    /**
     * Create fake instance of AdvancedVarianceThreshold and save it in database
     *
     * @param array $advanced_variance_thresholds_arr
     * @return AdvancedVarianceThreshold
     */
    public function makeAdvancedVarianceThreshold($advanced_variance_thresholds_arr = [])
    {
        $theme = $this->fakeAdvancedVarianceThresholdData($advanced_variance_thresholds_arr);
        return $this->AdvancedVarianceThresholdRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of AdvancedVarianceThreshold
     *
     * @param array $advanced_variance_thresholds_arr
     * @return AdvancedVarianceThreshold
     */
    public function fakeAdvancedVarianceThreshold($advanced_variance_thresholds_arr = [])
    {
        return new AdvancedVarianceThreshold($this->fakeAdvancedVarianceThresholdData($advanced_variance_thresholds_arr));
    }

    /**
     * Get fake data of AdvancedVarianceThreshold
     *
     * @param array $advanced_variance_thresholds_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeAdvancedVarianceThresholdData($advanced_variance_thresholds_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($advanced_variance_thresholds_arr);
        return $factory->raw(AdvancedVarianceThreshold::class, $advanced_variance_thresholds_arr, $factory_name);
    }
}