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
use App\Waypoint\Models\EntityTagEntity;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeEntityTagEntityTrait
{
    /**
     * Create fake instance of EntityTagEntity and save it in database
     *
     * @param array $entity_tag_entities_arr
     * @return EntityTagEntity
     */
    public function makeEntityTagEntity($entity_tag_entities_arr = [])
    {
        $theme = $this->fakeEntityTagEntityData($entity_tag_entities_arr);
        return $this->EntityTagEntityRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of EntityTagEntity
     *
     * @param array $entity_tag_entities_arr
     * @return EntityTagEntity
     */
    public function fakeEntityTagEntity($entity_tag_entities_arr = [])
    {
        return new EntityTagEntity($this->fakeEntityTagEntityData($entity_tag_entities_arr));
    }

    /**
     * Get fake data of EntityTagEntity
     *
     * @param array $entity_tag_entities_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeEntityTagEntityData($entity_tag_entities_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($entity_tag_entities_arr);
        return $factory->raw(EntityTagEntity::class, $entity_tag_entities_arr, $factory_name);
    }
}