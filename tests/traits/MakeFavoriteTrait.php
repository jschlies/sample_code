<?php

namespace App\Waypoint\Tests;

use App\Waypoint\Seeder;
use App\Waypoint\Models\Favorite;

/**
 * @codeCoverageIgnore
 */
trait MakeFavoriteTrait
{
    /**
     * Create fake instance of Favorite and save it in database
     *
     * @param array $FavoriteFields
     * @return Favorite
     */
    public function makeFavorite($FavoriteFields = [])
    {
        $theme = $this->fakeFavoriteData($FavoriteFields);
        return $this->FavoriteRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of Favorite
     *
     * @param array $FavoriteFields
     * @return Favorite
     */
    public function fakeFavorite($FavoriteFields = [])
    {
        return new Favorite($this->fakeFavoriteData($FavoriteFields));
    }

    /**
     * Get fake data of Favorite
     *
     * @param array $favorite_arr
     * @return array
     */
    public function fakeFavoriteData($favorite_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        $favorite_arr['client_id'] = $this->ClientObj->id;

        /** @var Factory $factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($favorite_arr);
        return $factory->raw(Favorite::class, $favorite_arr, $factory_name);
    }
}