<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Heartbeat;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class HeartbeatApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class HeartbeatApiTest extends TestCase
{
    use MakeUserTrait, ApiTestTrait;

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
    public function it_can_read_Heartbeat()
    {
        $this->json('GET', '/api/v1/heartbeat');
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * @param array $PropertiesSummaryFields
     * @return App\Waypoint\Models\User|Heartbeat
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function makeHeartbeat($PropertiesSummaryFields = [])
    {
        $theme = $this->fakeUserData($PropertiesSummaryFields);
        return $this->HeartbeatRepositoryObj->create($theme);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
