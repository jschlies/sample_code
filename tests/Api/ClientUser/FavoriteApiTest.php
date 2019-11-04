<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Favorite;
use App\Waypoint\Models\Role;
use App\Waypoint\Repositories\FavoriteRepository;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\MakeFavoriteTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class FavoriteApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class FavoriteApiTest extends TestCase
{
    use MakeFavoriteTrait, ApiTestTrait;
    use MakeUserTrait;

    /**
     * @var FavoriteRepository
     * this is needed in MakeAccessListTrait
     */
    protected $FavoriteRepositoryObj;
    /** @var App\Waypoint\Repositories\UserRepository */
    protected $UserRepositoryObj;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_can_create_Favorites()
    {
        /** @var  array $Favorite_arr */
        $Favorite_arr = $this->fakeFavoriteData();
        $this->json('POST', '/api/v1/clients/' . $this->ClientObj->id . '/favorites', $Favorite_arr);

        $this->assertApiSuccess();
    }

    /**
     * @todo Fix Me - works locally, fails on codeship
     * xxxxxxxxtest
     *
     */
    public function it_can_read_favorites_lists()
    {
        /** @var  Favorite $FavoriteObj */
        $FavoriteObj = $this->makeFavorite(['user_id' => $this->getLoggedInUserObj()->id]);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/favorites/' . $FavoriteObj->id);
        $this->assertApiSuccess();
    }

    /**
     * @test
     */
    public function it_can_delete_favorites()
    {
        /** @var  Favorite $FavoriteObj */
        $FavoriteObj = $this->makeFavorite(
            [
                'user_id' => $this->getLoggedInUserObj()->id,
            ]
        );
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/favorites/' . $FavoriteObj->id);
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/favorites/' . $FavoriteObj->id);
        $this->assertAPIFailure([404]);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
