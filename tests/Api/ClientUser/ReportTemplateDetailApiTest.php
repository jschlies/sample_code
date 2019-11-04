<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App\Waypoint\Models\ReportTemplateDetail;
use App;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakePropertyNativeCoaTrait;
use App\Waypoint\Tests\Generated\MakeReportTemplateTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\User;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\Ledger\Ledger;
use App\Waypoint\Tests\Generated\MakeNativeAccountTypeTrait;

/**
 * Class ReportTemplateDetailApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class ReportTemplateDetailApiTest extends TestCase
{
    use MakeReportTemplateTrait, MakeNativeAccountTypeTrait, ApiTestTrait;
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
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     */
    public function it_can_read_report_templates_detail_list()
    {
        /** @var  ReportTemplateDetail $ReportTemplateDetailObj */
        $ReportTemplateDetailObj = ReportTemplateDetail::find($this->makeReportTemplate()->id);
        $this->json(
            'GET',
            '/api/v1/clients/' . $ReportTemplateDetailObj->client_id . '/reportTemplatesDetail?limit=' . config(
                'waypoint.unittest_loop'
            )
        );
        $this->assertApiListResponse(ReportTemplateDetail::class);
    }

    /**
     * @test
     */
    public function it_can_read_report_templates_detail()
    {
        /** @var  ReportTemplateDetail $ReportTemplateDetailObj */
        $ReportTemplateDetailObj = $this->makeReportTemplate();
        $this->json(
            'GET',
            '/api/v1/clients/' . $ReportTemplateDetailObj->client_id . '/reportTemplatesDetail/' . $ReportTemplateDetailObj->id
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     */
    public function it_can_read_native_coas_with_client_full_with_client()
    {
        /** @var  ReportTemplateDetail $ReportTemplateDetailObj */
        $ReportTemplateDetailObj = $this->makeReportTemplate();
        $this->json('GET', '/api/v1/clients/' . $ReportTemplateDetailObj->client_id . '/reportTemplatesFull/flat');
    }

    /**
     * @test
     * @throws \Exception
     */
    public function changed_report_template_correctly_effects_native_account_types_in_user_object()
    {
        $AllReportTemplates = $this->ClientObj->reportTemplates()->get();

        // need at least the BOMA & account type based report template, which should be seeded
        $this->assertGreaterThanOrEqual(2, $AllReportTemplates->count());

        // get the client level default report template
        $DefaultAnalyticsReportTemplateObj
            = $AllReportTemplates->first(function ($ReportTemplateObj)
        {
            /** @var App\Waypoint\Models\ReportTemplate $ReportTemplateObj */
            return $ReportTemplateObj->is_default_analytics_report_template == 1;
        });

        // get the other report template
        $DifferentReportTemplateObj
            = $AllReportTemplates->first(function ($ReportTemplateObj) use ($DefaultAnalyticsReportTemplateObj)
        {
            /** @var App\Waypoint\Models\ReportTemplate $ReportTemplateObj */
            return $ReportTemplateObj->id != $DefaultAnalyticsReportTemplateObj->id;
        });

        /** @var User $GenericUserObj */
        $GenericUserObj = $this->SeventhGenericUserObj;

        // log that user in
        $this->logInUser($GenericUserObj);

        // the client object should really already have this config, but just in case
        if ( ! isset($this->ClientObj->getConfigJSON(true)[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY]))
        {
            $ClientConfigArr
            [NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY]
            [AdvancedVariance::ADVANCED_VARIANCE_CONFIG_KEY]
                = $this->ClientObj->nativeAccountTypeSummaries;

            $this->ClientObj->config_json = json_encode($ClientConfigArr);
            $this->ClientObj->save();
        }

        // update the report template
        $this->UserRepositoryObj->updateReportTemplate($DifferentReportTemplateObj->id);
        $GenericUserObj = $GenericUserObj->refresh();

        $default_report_template_id = $GenericUserObj->getConfigValue(User::DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG);

        // the adjusted default report template comes back correctly
        $this->assertEquals($default_report_template_id, $DifferentReportTemplateObj->id);

        $generic_user_config_arr = $GenericUserObj->getConfigJSON(true);

        // the user's config has been populated with the new account types data, which informs the front end about the account type tabs
        $this->assertNotNull($generic_user_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][Ledger::ANALYTICS_CONFIG_KEY]);

        // the report template groups come back and correctly reflect the new report template, for each account type in the list
        foreach ($generic_user_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][Ledger::ANALYTICS_CONFIG_KEY] as $NativeAccountTypeConfigObj)
        {
            $report_template_id = $this->ReportTemplateAccountGroupRepositoryObj->find($NativeAccountTypeConfigObj->report_template_account_group_id)->report_template_id;

            $this->assertEquals($report_template_id, $DifferentReportTemplateObj->id);
        }
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
