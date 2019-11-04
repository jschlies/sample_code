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
use App\Waypoint\Models\AdvancedVarianceApproval;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeAdvancedVarianceApprovalTrait
{
    /**
     * Create fake instance of AdvancedVarianceApproval and save it in database
     *
     * @param array $advanced_variance_approvals_arr
     * @return AdvancedVarianceApproval
     */
    public function makeAdvancedVarianceApproval($advanced_variance_approvals_arr = [])
    {
        $theme = $this->fakeAdvancedVarianceApprovalData($advanced_variance_approvals_arr);
        return $this->AdvancedVarianceApprovalRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of AdvancedVarianceApproval
     *
     * @param array $advanced_variance_approvals_arr
     * @return AdvancedVarianceApproval
     */
    public function fakeAdvancedVarianceApproval($advanced_variance_approvals_arr = [])
    {
        return new AdvancedVarianceApproval($this->fakeAdvancedVarianceApprovalData($advanced_variance_approvals_arr));
    }

    /**
     * Get fake data of AdvancedVarianceApproval
     *
     * @param array $advanced_variance_approvals_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeAdvancedVarianceApprovalData($advanced_variance_approvals_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($advanced_variance_approvals_arr);
        return $factory->raw(AdvancedVarianceApproval::class, $advanced_variance_approvals_arr, $factory_name);
    }
}