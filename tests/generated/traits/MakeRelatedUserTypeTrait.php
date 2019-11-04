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
use App\Waypoint\Models\RelatedUserType;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeRelatedUserTypeTrait
{
    /**
     * Create fake instance of RelatedUserType and save it in database
     *
     * @param array $related_user_types_arr
     * @return RelatedUserType
     */
    public function makeRelatedUserType($related_user_types_arr = [])
    {
        $theme = $this->fakeRelatedUserTypeData($related_user_types_arr);
        return $this->RelatedUserTypeRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of RelatedUserType
     *
     * @param array $related_user_types_arr
     * @return RelatedUserType
     */
    public function fakeRelatedUserType($related_user_types_arr = [])
    {
        return new RelatedUserType($this->fakeRelatedUserTypeData($related_user_types_arr));
    }

    /**
     * Get fake data of RelatedUserType
     *
     * @param array $related_user_types_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeRelatedUserTypeData($related_user_types_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($related_user_types_arr);
        return $factory->raw(RelatedUserType::class, $related_user_types_arr, $factory_name);
    }
}