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
use App\Waypoint\Models\EcmProject;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeEcmProjectTrait
{
    /**
     * Create fake instance of EcmProject and save it in database
     *
     * @param array $ecm_projects_arr
     * @return EcmProject
     */
    public function makeEcmProject($ecm_projects_arr = [])
    {
        $theme = $this->fakeEcmProjectData($ecm_projects_arr);
        return $this->EcmProjectRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of EcmProject
     *
     * @param array $ecm_projects_arr
     * @return EcmProject
     */
    public function fakeEcmProject($ecm_projects_arr = [])
    {
        return new EcmProject($this->fakeEcmProjectData($ecm_projects_arr));
    }

    /**
     * Get fake data of EcmProject
     *
     * @param array $ecm_projects_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeEcmProjectData($ecm_projects_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($ecm_projects_arr);
        return $factory->raw(EcmProject::class, $ecm_projects_arr, $factory_name);
    }
}