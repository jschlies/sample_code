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
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeAdvancedVarianceLineItemTrait
{
    /**
     * Create fake instance of AdvancedVarianceLineItem and save it in database
     *
     * @param array $advanced_variance_line_items_arr
     * @return AdvancedVarianceLineItem
     */
    public function makeAdvancedVarianceLineItem($advanced_variance_line_items_arr = [])
    {
        $theme = $this->fakeAdvancedVarianceLineItemData($advanced_variance_line_items_arr);
        return $this->AdvancedVarianceLineItemRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of AdvancedVarianceLineItem
     *
     * @param array $advanced_variance_line_items_arr
     * @return AdvancedVarianceLineItem
     */
    public function fakeAdvancedVarianceLineItem($advanced_variance_line_items_arr = [])
    {
        return new AdvancedVarianceLineItem($this->fakeAdvancedVarianceLineItemData($advanced_variance_line_items_arr));
    }

    /**
     * Get fake data of AdvancedVarianceLineItem
     *
     * @param array $advanced_variance_line_items_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeAdvancedVarianceLineItemData($advanced_variance_line_items_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($advanced_variance_line_items_arr);
        return $factory->raw(AdvancedVarianceLineItem::class, $advanced_variance_line_items_arr, $factory_name);
    }
}