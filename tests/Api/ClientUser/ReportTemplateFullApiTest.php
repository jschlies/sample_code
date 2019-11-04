<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App\Waypoint\Models\ReportTemplate;
use App;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakePropertyNativeCoaTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\Generated\MakeReportTemplateTrait;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class ReportTemplateApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class ReportTemplateFullApiTest extends TestCase
{
    use MakeReportTemplateTrait, ApiTestTrait;
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
    public function it_can_read_boma_client_mapping_groups_full()
    {
        /** @var  ReportTemplate $ReportTemplateObj */
        $ReportTemplateObj = $this->makeReportTemplate();
        $this->json(
            'GET',
            '/api/v1/clients/' . $ReportTemplateObj->client_id . '/reportTemplatesFull/' . $ReportTemplateObj->id
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     */
    public function it_can_read_native_coas_with_client_full_with_client()
    {
        /** @var  ReportTemplate $ReportTemplateObj */
        $ReportTemplateObj = $this->makeReportTemplate();
        $this->json('GET', '/api/v1/clients/' . $ReportTemplateObj->client_id . '/reportTemplatesFull/flat');
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
