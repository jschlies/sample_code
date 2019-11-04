<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Models\Role;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeNativeCoaTrait;
/**
 * remember you cannot 'use App\Waypoint\Models\Role here as it messes with Role unit tests
 */

use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\ApiTestTrait;
use App;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class NativeCoaApiBaseTest
 *
 * @codeCoverageIgnore
 */
class NativeCoaApiBaseTest extends TestCase
{
    use MakeNativeCoaTrait, ApiTestTrait;

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
    public function it_can_create_native_coas()
    {
        /** @var  array $native_coas_arr */
        $native_coas_arr = $this->fakeNativeCoaData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas',
            $native_coas_arr
        );
        $this->assertApiSuccess();
        $native_coas_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_coas_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_coas_id
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
                '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas',
                $native_coas_arr
            );
            $this->assertApiSuccess();
            $native_coas_id = $this->getFirstDataObject()['id'];
        }

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_coas_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/'
        );
        $this->assertApiSuccess();

        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_native_coas_arr */
        $edited_native_coas_arr = $this->fakeNativeCoaData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_coas_id,
            $edited_native_coas_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_coas_id
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
