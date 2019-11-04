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
use App\Waypoint\Models\EntityTag;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeEntityTagTrait
{
    /**
     * Create fake instance of EntityTag and save it in database
     *
     * @param array $entity_tags_arr
     * @return EntityTag
     */
    public function makeEntityTag($entity_tags_arr = [])
    {
        $theme = $this->fakeEntityTagData($entity_tags_arr);
        return $this->EntityTagRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of EntityTag
     *
     * @param array $entity_tags_arr
     * @return EntityTag
     */
    public function fakeEntityTag($entity_tags_arr = [])
    {
        return new EntityTag($this->fakeEntityTagData($entity_tags_arr));
    }

    /**
     * Get fake data of EntityTag
     *
     * @param array $entity_tags_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeEntityTagData($entity_tags_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($entity_tags_arr);
        return $factory->raw(EntityTag::class, $entity_tags_arr, $factory_name);
    }
}