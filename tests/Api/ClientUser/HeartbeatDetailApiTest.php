<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Models\Role;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class HeartbeatDetailApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class HeartbeatDetailApiTest extends TestCase
{
    use ApiTestTrait;

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
    public function it_can_read_HeartbeatDetail()
    {
        $this->json(
            'GET',
            'api/v1/heartbeatDetail'
        );
        $this->assertApiSuccess();
    }

    /**
     * @param array $PropertiesSummaryFields
     * @return App\Waypoint\Models\User
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws \BadMethodCallException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function makeHeartbeatDetail($PropertiesSummaryFields = [])
    {
        /** @var [] $theme */
        $theme = $this->fakeHeartbeatDetailData($PropertiesSummaryFields);
        return $this->HeartbeatDetailRepositoryObj->create($theme);
    }

    /**
     * @param array $HeartbeatDetailFields
     * @return array
     */
    public function fakeHeartbeatDetailData($HeartbeatDetailFields = [])
    {
        return $this->fakeUserData($HeartbeatDetailFields);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
