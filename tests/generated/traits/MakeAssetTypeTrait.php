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
use App\Waypoint\Models\AssetType;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeAssetTypeTrait
{
    /**
     * Create fake instance of AssetType and save it in database
     *
     * @param array $asset_types_arr
     * @return AssetType
     */
    public function makeAssetType($asset_types_arr = [])
    {
        $theme = $this->fakeAssetTypeData($asset_types_arr);
        return $this->AssetTypeRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of AssetType
     *
     * @param array $asset_types_arr
     * @return AssetType
     */
    public function fakeAssetType($asset_types_arr = [])
    {
        return new AssetType($this->fakeAssetTypeData($asset_types_arr));
    }

    /**
     * Get fake data of AssetType
     *
     * @param array $asset_types_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeAssetTypeData($asset_types_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($asset_types_arr);
        return $factory->raw(AssetType::class, $asset_types_arr, $factory_name);
    }
}