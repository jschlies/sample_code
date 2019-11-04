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
use App\Waypoint\Tests\Generated\MakeCustomReportTypeTrait;
/**
 * remember you cannot 'use App\Waypoint\Models\Role here as it messes with Role unit tests
 */

use App\Waypoint\Models\CustomReportType;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\ApiTestTrait;
use App;
use App\Waypoint\Exceptions\GeneralException;

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
        $this->setLoggedInUserRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
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
            '/api/v1/clients/' . $this->ClientObj->id . '/customReportTypes',
            $custom_report_types_arr
        );
        $this->assertApiSuccess();
        $custom_report_types_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/customReportTypes/' . $custom_report_types_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/customReportTypes/' . $custom_report_types_id
        );
        $this->assertAPIFailure([400]);

        /**
         * now re-add it
         */
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/customReportTypes',
            $custom_report_types_arr
        );
        $this->assertApiSuccess();

        $custom_report_types_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/customReportTypes/' . $this->getFirstDataObject()['id']
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/customReportTypes/'
        );
        $this->assertApiListResponse(CustomReportType::class);

        /** @var  CustomReportType $customReportTypeObj */
        $customReportTypeObj = $this->makeCustomReportType();
        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_custom_report_types_arr */
        $edited_custom_report_types_arr = $this->fakeCustomReportTypeData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/customReportTypes/' . $customReportTypeObj->id,
            $edited_custom_report_types_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/customReportTypes/' . $custom_report_types_id
        );
        $this->assertApiSuccess();
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/customReportTypes/' . $custom_report_types_id
        );
        $this->assertApiFailure();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_custom_report_types()
    {
        $this->json(
            'GET', '/api/v1/clients/' . $this->ClientObj->id . '/customReportTypes/' . '1000000' . mt_rand()
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
        $this->json('PUT', '/api/v1/clients/' . $this->ClientObj->id . '/customReportTypes/' . '1000000' . mt_rand(),
                    $editedCustomReportType_arr
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
            '/api/v1/clients/' . $this->ClientObj->id . '/customReportTypes/1000' . mt_rand()
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
