<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App\Waypoint\Models\Role;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeReportTemplateTrait;
/**
 * remember you cannot 'use App\Waypoint\Models\Role here as it messes with Role unit tests
 */

use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\ApiTestTrait;
use App;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class ReportTemplateApiBaseTest
 *
 * @codeCoverageIgnore
 */
class ReportTemplateApiTest extends TestCase
{
    use MakeReportTemplateTrait, ApiTestTrait;

    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_report_templates()
    {
        /** @var  array $report_templates_arr */
        $report_templates_arr = $this->fakeReportTemplateData();

        $this->setLoggedInUserRole(Role::WAYPOINT_ROOT_ROLE);
        $this->logInUser();

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates',
            $report_templates_arr
        );
        $this->assertApiSuccess();
        $reportTemplates_id = $this->getFirstDataObject()['id'];

        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        $this->logInUser();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplatesFull/' . $reportTemplates_id
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->setLoggedInUserRole(Role::WAYPOINT_ROOT_ROLE);
        $this->logInUser();

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $reportTemplates_id
        );
        $this->assertApiSuccess();

        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        $this->logInUser();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplatesFull/' . $reportTemplates_id
        );
        $this->assertApiFailure();

        $this->setLoggedInUserRole(Role::WAYPOINT_ROOT_ROLE);
        $this->logInUser();

        /**
         * now re-add it
         */

        $this->setLoggedInUserRole(Role::WAYPOINT_ROOT_ROLE);
        $this->logInUser();

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates',
            $report_templates_arr
        );
        $this->assertApiSuccess();

        $reportTemplates_id = $this->getFirstDataObject()['id'];

        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        $this->logInUser();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplatesFull/' . $reportTemplates_id
        );

        $this->assertApiSuccess();

        $this->setLoggedInUserRole(Role::WAYPOINT_ROOT_ROLE);
        $this->logInUser();

        /** @var  array $edited_report_templates_arr */
        $edited_report_templates_arr = $this->fakeReportTemplateData([], Seeder::DEFAULT_FACTORY_NAME);

        $this->setLoggedInUserRole(Role::WAYPOINT_ROOT_ROLE);
        $this->logInUser();

        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $reportTemplates_id,
            $edited_report_templates_arr
        );
        $this->assertApiSuccess();

        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        $this->logInUser();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplatesFull/' . $reportTemplates_id
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
    public function it_can_read_report_templates_list()
    {
        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_GENERIC_USER_ROLE . '/clients/' . $this->ClientObj->id . '/reportTemplatesDetail'
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_GENERIC_USER_ROLE . '/clients/' . $this->ClientObj->id . '/reportTemplatesFull'
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_report_templates()
    {
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplatesDetail/1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_update_non_existing_report_templates()
    {
        /** @var  array $editedReportTemplate_arr */
        $editedReportTemplate_arr = $this->fakeReportTemplateData([], Seeder::DEFAULT_FACTORY_NAME);
        /** @var  ReportTemplate $reportTemplateObj */
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/updateReportTemplateForUser/reportTemplates/1000000' . mt_rand(),
            $editedReportTemplate_arr
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
