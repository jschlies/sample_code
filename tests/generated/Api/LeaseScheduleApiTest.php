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
use App\Waypoint\Models\LeaseSchedule;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeLeaseScheduleTrait;

/**
 * Class LeaseScheduleApiBaseTest
 *
 * @codeCoverageIgnore
 */
class LeaseScheduleApiBaseTest extends TestCase
{
    use MakeLeaseScheduleTrait, ApiTestTrait;

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
    public function it_can_create_lease_schedules()
    {
        /** @var  array $lease_schedules_arr */
        $lease_schedules_arr = $this->fakeLeaseScheduleData();
        $this->json(
            'POST',
            '/api/v1/' . substr('leaseSchedules', 0, 32),
            $lease_schedules_arr
        );
        $this->assertApiSuccess();
        $leaseSchedules_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('leaseSchedules', 0, 32) . '/' . $leaseSchedules_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('leaseSchedules', 0, 32) . '/' . $leaseSchedules_id
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
                '/api/v1/' . substr('leaseSchedules', 0, 32),
                $lease_schedules_arr
            );
            $this->assertApiSuccess();
        }

        $leaseSchedules_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/' . substr('leaseSchedules', 0, 32) . '/' . $this->getFirstDataObject()['id']
        );
        $this->assertApiSuccess();

        /** @var  LeaseSchedule $leaseScheduleObj */
        $leaseScheduleObj = $this->makeLeaseSchedule();
        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_lease_schedules_arr */
        $edited_lease_schedules_arr = $this->fakeLeaseScheduleData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/' . substr('leaseSchedules', 0, 32) . '/' . $leaseScheduleObj->id,
            $edited_lease_schedules_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('leaseSchedules', 0, 32) . '/' . $leaseSchedules_id
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
    public function it_can_read_lease_schedules_list()
    {
        /** @var  array $lease_schedules_arr */
        $lease_schedules_arr = $this->fakeLeaseScheduleData();
        $this->json(
            'POST',
            '/api/v1/' . substr('leaseSchedules', 0, 32),
            $lease_schedules_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('leaseSchedules', 0, 32) . '?limit=' . config('waypoint.unittest_loop')
        );

        $this->assertAPIListResponse(LeaseSchedule::class);

    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_lease_schedules()
    {
        $this->json(
            'GET',
            '/api/v1/' . substr('leaseSchedules', 0, 32) . '/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_update_non_existing_lease_schedules()
    {
        /** @var  array $editedLeaseSchedule_arr */
        $editedLeaseSchedule_arr = $this->fakeLeaseScheduleData([], Seeder::DEFAULT_FACTORY_NAME);
        /** @var  LeaseSchedule $leaseScheduleObj */
        $this->json(
            'PUT',
            '/api/v1/' . substr('leaseSchedules', 0, 32) . '/' . '1000000' . mt_rand(), $editedLeaseSchedule_arr
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_delete_non_existing_lease_schedules()
    {
        /** @var  LeaseSchedule $leaseScheduleObj */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('leaseSchedules', 0, 32) . '/1000' . mt_rand()
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
