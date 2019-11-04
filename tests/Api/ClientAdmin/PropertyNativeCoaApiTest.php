<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App\Waypoint\Models\Role;
use App\Waypoint\Tests\Generated\MakePropertyNativeCoaTrait;
/**
 * remember you cannot 'use App\Waypoint\Models\Role here as it messes with Role unit tests
 */

use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\ApiTestTrait;
use App;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class PropertyNativeCoaApiBaseTest
 *
 * @codeCoverageIgnore
 */
class PropertyNativeCoaApiTest extends TestCase
{
    use MakePropertyNativeCoaTrait, ApiTestTrait;

    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_property_native_coas()
    {
        /** @var  array $property_native_coas_arr */
        $property_native_coas_arr = $this->fakePropertyNativeCoaData();
        $property_id              = $property_native_coas_arr['property_id'];

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $property_id . '/propertyNativeCoas',
            $property_native_coas_arr
        );
        $this->assertApiSuccess();
        $property_native_coas_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $property_id . '/propertyNativeCoas/' . $property_native_coas_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $property_id . '/propertyNativeCoas/' . $property_native_coas_id
        );

        /**
         * since users are never deleted, just made inactive......
         */
        if (get_class($this) == 'App\Waypoint\Tests\Generated\UserApiBaseTest')
        {
            $this->assertApiSuccess();
        }
        else
        {
            $this->assertAPIFailure([400]);

            /**
             * now re-add it
             */
            $this->json(
                'POST',
                '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $property_id . '/propertyNativeCoas',
                $property_native_coas_arr
            );
            $this->assertApiSuccess();
            $property_native_coas_id = $this->getFirstDataObject()['id'];
        }

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $property_id . '/propertyNativeCoas/' . $property_native_coas_id
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $property_id . '/propertyNativeCoas/' . $property_native_coas_id
        );
        $this->assertApiSuccess();
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
