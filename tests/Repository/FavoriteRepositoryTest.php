<?php

namespace App\Waypoint\Tests\Repository;

use App\Waypoint\Models\Favorite;
use App;
use App\Waypoint\Tests\MakeFavoriteTrait;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class FavoriteRepositoryTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class FavoriteRepositoryTest extends TestCase
{
    use MakeFavoriteTrait, ApiTestTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_creates_favorite()
    {
        /** @var  array $favorite_arr */
        $favorite_arr = $this->fakeFavoriteData();
        unset($favorite_arr['client_id']);
        $FavoriteObj = $this->FavoriteRepositoryObj->create($favorite_arr);

        /** @var  array $createdFavorite_arr */
        $createdFavorite_arr = $FavoriteObj->toArray();
        $this->assertArrayHasKey('id', $FavoriteObj);
        $this->assertNotNull($createdFavorite_arr['id'], 'Created Favorite must have id specified');
        $this->assertNotNull(Favorite::find($createdFavorite_arr['id']), 'Favorite with given id must be in DB');

        /** @var  Favorite $dbFavoriteObj */
        $FavoriteObj = $this->FavoriteRepositoryObj->find($FavoriteObj->id);
        $this->assertTrue($FavoriteObj->validate());

        $fakeFavorite_arr = $this->fakeFavoriteData();
        $this->FavoriteRepositoryObj->update($fakeFavorite_arr, $FavoriteObj->id);

        /** @var  Favorite $dbFavoriteObj */
        $this->FavoriteRepositoryObj->find($FavoriteObj->id);

        $resp = $this->FavoriteRepositoryObj->delete($FavoriteObj->id);
        $this->assertTrue($resp);
        $this->assertNull(Favorite::find($FavoriteObj->id), 'Favorite should not exist in DB');
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        unset($this->FavoriteRepositoryObj);
        parent::tearDown();
    }
}