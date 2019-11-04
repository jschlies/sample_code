<?php

namespace App\Waypoint\Tests;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\SuiteTenant;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeSuiteTenantTrait;

/**
 * Class SuiteTenantApiBaseTest
 *
 * @codeCoverageIgnore
 */
class SuiteTenantApiBaseTest extends TestCase
{
    use MakeSuiteTenantTrait, ApiTestTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_suite_tenants()
    {
        /** @var  array $suite_tenants_arr */
        $suite_tenants_arr = $this->fakeSuiteTenantData();
        $this->json(
            'POST',
            '/api/v1/' . substr('suiteTenants', 0, 32),
            $suite_tenants_arr
        );
        $this->assertApiSuccess();
        $suiteTenants_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('suiteTenants', 0, 32) . '/' . $suiteTenants_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('suiteTenants', 0, 32) . '/' . $suiteTenants_id
        );

        /**
         * since users are never deleted, just made inactive......
         */
        if (get_class($this) == UserApiBaseTest::class)
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
                '/api/v1/' . substr('suiteTenants', 0, 32),
                $suite_tenants_arr
            );
            $this->assertApiSuccess();
        }

        $suiteTenants_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/' . substr('suiteTenants', 0, 32) . '/' . $this->getFirstDataObject()['id']
        );
        $this->assertApiSuccess();

        /** @var  SuiteTenant $suiteTenantObj */
        $suiteTenantObj = $this->makeSuiteTenant();
        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_suite_tenants_arr */
        $edited_suite_tenants_arr = $this->fakeSuiteTenantData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/' . substr('suiteTenants', 0, 32) . '/' . $suiteTenantObj->id,
            $edited_suite_tenants_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('suiteTenants', 0, 32) . '/' . $suiteTenants_id
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     */
    public function it_can_read_suite_tenants_list()
    {
        /** @var  array $suite_tenants_arr */
        $suite_tenants_arr = $this->fakeSuiteTenantData();
        $this->json(
            'POST',
            '/api/v1/' . substr('suiteTenants', 0, 32),
            $suite_tenants_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('suiteTenants', 0, 32) . '?limit=' . config('waypoint.unittest_loop')
        );

        $this->assertAPIListResponse(SuiteTenant::class);

    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_suite_tenants()
    {
        $this->json(
            'GET',
            '/api/v1/' . substr('suiteTenants', 0, 32) . '/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_update_non_existing_suite_tenants()
    {
        /** @var  array $editedSuiteTenant_arr */
        $editedSuiteTenant_arr = $this->fakeSuiteTenantData([], Seeder::DEFAULT_FACTORY_NAME);
        /** @var  SuiteTenant $suiteTenantObj */
        $this->json(
            'PUT',
            '/api/v1/' . substr('suiteTenants', 0, 32) . '/' . '1000000' . mt_rand(), $editedSuiteTenant_arr
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_delete_non_existing_suite_tenants()
    {
        /** @var  SuiteTenant $suiteTenantObj */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('suiteTenants', 0, 32) . '/1000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
