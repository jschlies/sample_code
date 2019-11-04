<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\Generated\MakeReportTemplateMappingTrait;
/**
 * remember you cannot 'use App\Waypoint\Models\Role here as it messes with Role unit tests
 */

use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\ApiTestTrait;
use App;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class ReportTemplateMappingApiBaseTest
 *
 * @codeCoverageIgnore
 */
class ReportTemplateMappingApiBaseTest extends TestCase
{
    use MakeReportTemplateMappingTrait, ApiTestTrait;

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
    public function it_can_create_report_template_mappings()
    {
        /** @var  array $report_template_mappings_arr */
        $report_template_mappings_arr = $this->fakeReportTemplateMappingData();
        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupRepositoryObj->find($report_template_mappings_arr['report_template_account_group_id']);
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' .
            $ReportTemplateAccountGroupObj->report_template_id . '/reportTemplateAccountGroups/' .
            $ReportTemplateAccountGroupObj->id . '/reportTemplateMappings',
            $report_template_mappings_arr
        );
        $this->assertApiSuccess();
        $report_template_mapping_id = $this->getJSONContent()['data']['id'];
        /**
         * test some boutique route
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' .
            $ReportTemplateAccountGroupObj->report_template_id . '/reportTemplateAccountGroups/' .
            $ReportTemplateAccountGroupObj->id . '/reportTemplateMappings/' . $report_template_mapping_id
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' .
            $ReportTemplateAccountGroupObj->report_template_id . '/reportTemplateAccountGroups/' .
            $ReportTemplateAccountGroupObj->id . '/reportTemplateMappings/' . $report_template_mapping_id
        );
        $this->assertApiSuccess();

        /**
         * try to get it and fail
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' .
            $ReportTemplateAccountGroupObj->report_template_id . '/reportTemplateAccountGroups/' .
            $ReportTemplateAccountGroupObj->id . '/reportTemplateMappings/' . $report_template_mapping_id
        );
        $this->assertApiFailure();

        /**
         * re-create it
         */
        /** @var  array $report_template_mappings_arr */
        $report_template_mappings_arr = $this->fakeReportTemplateMappingData();
        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj = $this->ReportTemplateAccountGroupRepositoryObj->find($report_template_mappings_arr['report_template_account_group_id']);
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' .
            $ReportTemplateAccountGroupObj->report_template_id . '/reportTemplateAccountGroups/' .
            $ReportTemplateAccountGroupObj->id . '/reportTemplateMappings',
            $report_template_mappings_arr
        );
        $this->assertApiSuccess();
        $report_template_mapping_id = $this->getJSONContent()['data']['id'];

        /**
         * get the new one
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' .
            $ReportTemplateAccountGroupObj->report_template_id . '/reportTemplateAccountGroups/' .
            $ReportTemplateAccountGroupObj->id . '/reportTemplateMappings/' . $report_template_mapping_id
        );
        $this->assertApiSuccess();

        /**
         * clean up
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' .
            $ReportTemplateAccountGroupObj->report_template_id . '/reportTemplateAccountGroups/' .
            $ReportTemplateAccountGroupObj->id . '/reportTemplateMappings/' . $report_template_mapping_id
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
