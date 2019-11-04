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
use App\Waypoint\Models\CustomReportType;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeCustomReportTypeTrait;

/**
 * Class CustomReportTypeApiBaseTest
 *
 * @codeCoverageIgnore
 */
class CustomReportTypeApiBaseTest extends TestCase
{
    use MakeCustomReportTypeTrait, ApiTestTrait;

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
    public function it_can_create_custom_report_types()
    {
        /** @var  array $custom_report_types_arr */
        $custom_report_types_arr = $this->fakeCustomReportTypeData();
        $this->json(
            'POST',
            '/api/v1/' . substr('customReportTypes', 0, 32),
            $custom_report_types_arr
        );
        $this->assertApiSuccess();
        $customReportTypes_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('customReportTypes', 0, 32) . '/' . $customReportTypes_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('customReportTypes', 0, 32) . '/' . $customReportTypes_id
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
                '/api/v1/' . substr('customReportTypes', 0, 32),
                $custom_report_types_arr
            );
            $this->assertApiSuccess();
        }

        $customReportTypes_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/' . substr('customReportTypes', 0, 32) . '/' . $this->getFirstDataObject()['id']
        );
        $this->assertApiSuccess();

        /** @var  CustomReportType $customReportTypeObj */
        $customReportTypeObj = $this->makeCustomReportType();
        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_custom_report_types_arr */
        $edited_custom_report_types_arr = $this->fakeCustomReportTypeData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/' . substr('customReportTypes', 0, 32) . '/' . $customReportTypeObj->id,
            $edited_custom_report_types_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('customReportTypes', 0, 32) . '/' . $customReportTypes_id
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
    public function it_can_read_custom_report_types_list()
    {
        /** @var  array $custom_report_types_arr */
        $custom_report_types_arr = $this->fakeCustomReportTypeData();
        $this->json(
            'POST',
            '/api/v1/' . substr('customReportTypes', 0, 32),
            $custom_report_types_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('customReportTypes', 0, 32) . '?limit=' . config('waypoint.unittest_loop')
        );

        $this->assertAPIListResponse(CustomReportType::class);

    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_custom_report_types()
    {
        $this->json(
            'GET',
            '/api/v1/' . substr('customReportTypes', 0, 32) . '/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_update_non_existing_custom_report_types()
    {
        /** @var  array $editedCustomReportType_arr */
        $editedCustomReportType_arr = $this->fakeCustomReportTypeData([], Seeder::DEFAULT_FACTORY_NAME);
        /** @var  CustomReportType $customReportTypeObj */
        $this->json(
            'PUT',
            '/api/v1/' . substr('customReportTypes', 0, 32) . '/' . '1000000' . mt_rand(), $editedCustomReportType_arr
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_delete_non_existing_custom_report_types()
    {
        /** @var  CustomReportType $customReportTypeObj */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('customReportTypes', 0, 32) . '/1000' . mt_rand()
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
