<?php

namespace App\Waypoint\Tests\Api\Root;

use App;
use App\Waypoint\Models\FavoriteGroup;
use App\Waypoint\Models\Favorite;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\MakeFavoriteTrait;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class FavoriteApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class FavoriteApiTest extends TestCase
{
    use MakeFavoriteTrait, ApiTestTrait;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_can_create_Favorites()
    {
        /** @var  array $Favorite_arr */
        $Favorite_arr = $this->fakeFavoriteData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/favorites', $Favorite_arr
        );

        $this->assertApiSuccess();
    }

    /**
     * @test
     */
    public function it_can_read_favorites_lists()
    {
        /** @var  Favorite $FavoriteObj */
        $FavoriteObj = $this->makeFavorite();

        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/favorites/' . $FavoriteObj->id);
        $this->assertApiSuccess();

        $this->assertApiSuccess();
    }

    /**
     * @todo fix me
     * xxxxxxtest
     *
     * @todo fix this - need a special ->all() method in repos
     */
    public function it_can_read_favorites_list()
    {
        /** @var  array $Favorite_arr */
        $Favorite_arr = $this->fakeFavoriteData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/favorites', $Favorite_arr
        );

        $this->assertApiSuccess();

        $this->json('GET', '/api/v1/favorites?limit=' . config('waypoint.unittest_loop'));
        $this->assertApiListResponse(Favorite::class);
    }

    /**
     * @test
     *
     * @todo fix this - need a special ->all() method in repos
     */
    public function it_can_read_favoriteGroups_list()
    {
        $this->json('GET', '/api/v1/favoriteGroups');
        $this->assertApiListResponse(FavoriteGroup::class);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
