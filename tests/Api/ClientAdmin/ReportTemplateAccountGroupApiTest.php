<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\Role;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeReportTemplateAccountGroupTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class ReportTemplateAccountGroupApiBaseTest
 *
 * @codeCoverageIgnore
 */
class ReportTemplateAccountGroupApiTest extends TestCase
{
    use MakeReportTemplateAccountGroupTrait, ApiTestTrait;

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
    public function it_can_create_report_template_account_groups()
    {
        /** @var  array $report_template_account_groups_arr */

        $report_template_account_groups_arr = $this->fakeReportTemplateAccountGroupData();

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_account_groups_arr['report_template_id'] .
            '/reportTemplateAccountGroups',
            $report_template_account_groups_arr
        );
        $this->assertApiSuccess();
        $report_template_account_groups_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_account_groups_arr['report_template_id'] .
            '/reportTemplateAccountGroups/' . $report_template_account_groups_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_account_groups_arr['report_template_id'] .
            '/reportTemplateAccountGroups/' . $report_template_account_groups_id
        );
        $this->assertApiFailure();
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
                '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_account_groups_arr['report_template_id'] .
                '/reportTemplateAccountGroups',
                $report_template_account_groups_arr
            );
            $this->assertApiSuccess();
        }

        $report_template_account_groups_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_account_groups_arr['report_template_id'] .
            '/reportTemplateAccountGroups/' . $this->getFirstDataObject()['id']
        );
        $this->assertApiSuccess();

        /** @var  ReportTemplateAccountGroup $reportTemplateAccountGroupObj */
        $reportTemplateAccountGroupObj = $this->makeReportTemplateAccountGroup();
        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_report_template_account_groups_arr */
        $edited_report_template_account_groups_arr = $this->fakeReportTemplateAccountGroupData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_account_groups_arr['report_template_id'] .
            '/reportTemplateAccountGroups' . '/' . $reportTemplateAccountGroupObj->id,
            $edited_report_template_account_groups_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_account_groups_arr['report_template_id'] .
            '/reportTemplateAccountGroups/' . $report_template_account_groups_id
        );
        $this->assertApiSuccess();

        /**
         * get a list with nothin in it
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_account_groups_arr['report_template_id'] .
            '/reportTemplateAccountGroups'
        );
        $this->assertAPIListResponse(ReportTemplateAccountGroup::class, 0);

        /**
         * try to get one that is not there
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_account_groups_arr['report_template_id'] .
            '/reportTemplateAccountGroups/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);

        /**
         * try to update one that is not there
         */
        /** @var  array $editedReportTemplateAccountGroup_arr */
        $editedReportTemplateAccountGroup_arr = $this->fakeReportTemplateAccountGroupData([], Seeder::DEFAULT_FACTORY_NAME);
        /** @var  ReportTemplateAccountGroup $reportTemplateAccountGroupObj */
        $this->json('PUT',
                    '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_account_groups_arr['report_template_id'] .
                    '/reportTemplateAccountGroups/' . '1000000' . mt_rand(),
                    $editedReportTemplateAccountGroup_arr
        );
        $this->assertAPIFailure([400]);

        /**
         * try to delete one that is not there
         */
        /** @var  ReportTemplateAccountGroup $reportTemplateAccountGroupObj */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_account_groups_arr['report_template_id'] .
            '/reportTemplateAccountGroups/' . '1000000' . mt_rand()
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
