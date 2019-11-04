<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App\Waypoint\Models\NativeCoaFull;
use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeNativeCoaTrait;
use App\Waypoint\Tests\Generated\MakePropertyNativeCoaTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class NativeCoaFullApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class NativeCoaFullApiTest extends TestCase
{
    use MakeNativeCoaTrait, ApiTestTrait;
    use MakePropertyNativeCoaTrait;

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
    public function it_can_read_native_coas_with_client_full()
    {
        $this->makeNativeCoa();
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/nativeCoasFull'
        );
        $this->assertApiListResponse(NativeCoaFull::class);
    }

    /**
     * @test
     */
    public function it_can_read_native_coas_with_client_full_with_client()
    {
        /** @var  NativeCoaFull $NativeCoaFullObj */
        $NativeCoaFullObj = $this->makeNativeCoa();
        $this->json('GET', '/api/v1/clients/' . $NativeCoaFullObj->client_id . '/NativeCoasFull/flat');
    }

    /**
     * @test
     */
    public function it_can_read_native_coas_with_client_full_flat()
    {
        /** @var  NativeCoaFull $NativeCoaFullObj */
        $NativeCoaFullObj = $this->makeNativeCoa();
        $this->json(
            'GET', '/api/v1/clients/' . $NativeCoaFullObj->client_id . '/nativeCoasFull' . $NativeCoaFullObj->id
        );
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
