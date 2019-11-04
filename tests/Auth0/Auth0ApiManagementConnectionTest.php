<?php

namespace App\Waypoint\Tests;

use App;
use App\Waypoint\Auth0\Auth0ApiManagementConnection;
use App\Waypoint\Models\AuthenticatingEntity;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\Mocks\Auth0ApiManagementConnectionMock;

/**
 * Class UserRepositoryBaseTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class Auth0ApiManagementConnectionTest extends TestCase
{
    use MakeUserTrait, ApiTestTrait;
    use MakeFavoriteTrait;

    /** @var  Auth0ApiManagementConnection */
    protected $Auth0ApiManagementConnectionObj;

    public function setUp()
    {
        parent::setUp();
        $this->Auth0ApiManagementConnectionObj = App::make(Auth0ApiManagementConnectionMock::class);
    }

    /**
     * @test create
     *
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_get_connections()
    {
        $response = $this->Auth0ApiManagementConnectionObj->get_connections();
        $this->assertTrue(is_array($response));
        $this->assertTrue(count($response) > 1);
        $found_it = false;
        foreach ($response as $connection)
        {
            if ($connection->name = AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertTrue($found_it, 'cannot find Username-Password-Authentication connection');
    }

    /**
     * @test create
     *
     * @throws App\Waypoint\Exceptions\GeneralException
     */
    public function it_can_get_connections_with_name()
    {
        $response = $this->Auth0ApiManagementConnectionObj->get_connections_with_name(AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION);
        $this->assertEquals(AuthenticatingEntity::DEFAULT_AUTHENTICATINGENTITY_IDENTITY_CONNECTION, $response->name);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}