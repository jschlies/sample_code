<?php

namespace App\Waypoint\Tests\Api\ApiKey;

use App;
use App\Waypoint\CurlServiceTrait;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\NotificationLogRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Mocks\NativeCoaLedgerMockRepository;
use App\Waypoint\Tests\Generated\MakeAdvancedVarianceThresholdTrait;
use App\Waypoint\Tests\Generated\MakeAdvancedVarianceTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Models\User;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\ApiTestTrait;

/**
 * Class BridgeApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 *
 */
class AdvancedVarianceApiTest extends TestCase
{
    use ApiTestTrait;
    use MakeUserTrait;
    use MakePropertyTrait;
    use CurlServiceTrait;
    use MakeAdvancedVarianceTrait;
    use MakeAdvancedVarianceThresholdTrait;

    /** @var ApiKey */
    protected $ApiKeyObj;
    /** @var User */
    protected $UserObj;
    /** @var NotificationLogRepository */
    protected $NotificationLogRepositoryObj;
    /** @var Client */
    protected $ClientObj;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        parent::setUp();

        /**
         * @todo I know, I know, there is no 'logged in user' for apiKey routes
         */
        /** @var User $UserObj */
        $this->UserObj = $this->getLoggedInUserObj();
        if ( ! $this->UserObj->apiKey)
        {
            ApiKey::make($this->UserObj->id);
        }
        /** @var User $User2Obj */
        $this->ApiKeyObj = $this->getLoggedInUserObj()->apiKey;

        $this->getLoggedInUserObj()->setAllAdvancedVarianceNotificationConfigs(true);
        $this->ClientObj->updateConfig('ADVANCED_VARIANCE_REVIEWERS', $this->ClientObj->users->getArrayOfGivenFieldValues('email'));
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \Exception
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_advanced_variance()
    {
        /**
         * no point in running tests if queues are on, unless your running workers but even then,
         * you might get a nasty race condition
         */
        $this->assertTrue(config('queue.driver', 'sync') == 'sync');

        /** @var NativeCoaLedgerMockRepository $NativeCoaLedgerMockRepositoryObj */
        $NativeCoaLedgerMockRepositoryObj = App::make(NativeCoaLedgerMockRepository::class);
        AdvancedVarianceRepository::setNativeCoaLedgerRepositoryObj($NativeCoaLedgerMockRepositoryObj);

        /** @var Property $PropertyObj */
        $PropertyObj = $this->ClientObj->properties->first();
        $PropertyObj->updateConfig('ADVANCED_VARIANCE_REVIEWERS', $this->ClientObj->users->pluck('email')->toArray());

        $advanced_variance_data_arr = $this->fakeAdvancedVarianceData(
            [
                'client_id'   => $this->ClientObj->id,
                'property_id' => $PropertyObj->id,
            ],
            Seeder::PHPUNIT_FACTORY_NAME
        );

        $advanced_variance_data_arr['period_type'] = 'monthly';
        /**
         * nasty constraint issue
         */
        while (AdvancedVariance::where('property_id', $advanced_variance_data_arr['property_id'])
                               ->where('as_of_month', $advanced_variance_data_arr['as_of_month'])
                               ->where('as_of_year', $advanced_variance_data_arr['as_of_year'])
                               ->get()->count()
        )
        {
            $advanced_variance_data_arr['as_of_month'] = Seeder::getFakerObj()->randomElement([1, 2, 4, 5, 7, 8, 10, 11]);
            $advanced_variance_data_arr['as_of_year']  = Seeder::getFakerObj()->randomElement([2014, 2015, 2016, 2017]);
        }

        /** @var ReportTemplate $ReportTemplateObj */
        $ReportTemplateObj                               = $this->ClientObj->defaultAdvancedVarianceReportTemplate;
        $ultimate_parentAdvanced_variance_thresholds_arr = $this->fakeAdvancedVarianceThresholdData();

        /** @var App\Waypoint\Models\ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        foreach ($ReportTemplateObj->reportTemplateAccountGroupsChildren as $UltimateParentReportTemplateAccountGroupObj)
        {
            $ultimate_parentAdvanced_variance_thresholds_arr['client_id']                        = $this->ClientObj->id;
            $ultimate_parentAdvanced_variance_thresholds_arr['property_id']                      = $advanced_variance_data_arr['property_id'];
            $ultimate_parentAdvanced_variance_thresholds_arr['report_template_account_group_id'] = $UltimateParentReportTemplateAccountGroupObj->id;
            $this->json(
                'POST',
                '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceThresholds',
                $ultimate_parentAdvanced_variance_thresholds_arr
            );
            $this->assertApiSuccess();
        }
        $non_parent_advanced_variance_thresholds_arr = $this->fakeAdvancedVarianceThresholdData();
        foreach ($ReportTemplateObj->reportTemplateAccountGroups
                     ->filter(
                         function ($ReportTemplateAccountGroupObj)
                         {
                             return $ReportTemplateAccountGroupObj->parent_report_template_account_group_id;
                         })
                     ->all() as $ReportTemplateAccountGroupObj
        )
        {
            $non_parent_advanced_variance_thresholds_arr['client_id']                        = $this->ClientObj->id;
            $non_parent_advanced_variance_thresholds_arr['property_id']                      = $advanced_variance_data_arr['property_id'];
            $non_parent_advanced_variance_thresholds_arr['report_template_account_group_id'] = $ReportTemplateAccountGroupObj->id;
            $this->json(
                'POST',
                '/api/v1/clients/' . $this->ClientObj->id . '/' . substr('advancedVarianceThresholds', 0, 32),
                $non_parent_advanced_variance_thresholds_arr
            );
            $this->assertApiSuccess();
        }
        $native_account_advanced_variance_thresholds_arr = $this->fakeAdvancedVarianceThresholdData();
        foreach ($ReportTemplateObj->getAllNativeAccounts() as $NativeAccountObj)
        {
            $native_account_advanced_variance_thresholds_arr['client_id']         = $this->ClientObj->id;
            $native_account_advanced_variance_thresholds_arr['property_id']       = $advanced_variance_data_arr['property_id'];
            $native_account_advanced_variance_thresholds_arr['native_account_id'] = $NativeAccountObj->id;
            $this->json(
                'POST',
                '/api/v1/clients/' . $this->ClientObj->id . '/' . substr('advancedVarianceThresholds', 0, 32),
                $native_account_advanced_variance_thresholds_arr
            );
            $this->assertApiSuccess();
        }

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceThresholds'
        );
        $this->assertApiSuccess();

        $this->turn_on_fake_notifications();

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id .
            '/properties/' . $advanced_variance_data_arr['property_id'] . '/advancedVariances',
            $advanced_variance_data_arr,
            [
                'Content-Type' => 'application/jsonn',
            ]
        );

        $this->assertApiSuccess();
        $advanced_variance_id = $this->getFirstDataObject()['id'];
        $AdvancedVarianceObj  = App::make(AdvancedVarianceRepository::class)->find($advanced_variance_id);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id .
            '/properties/' . $advanced_variance_data_arr['property_id'] . '/advancedVariances',
            [],
            [
                'Content-Type' => 'application/json',
            ]
        );
        $this->assertApiSuccess();
        $this->asserttrue($this->getJSONContent()['success']);
        $this->assertTrue(count($this->getDataObjectArr()) > 0);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id .
            '/properties/' . $advanced_variance_data_arr['property_id'] . '/advancedVariances/' . $AdvancedVarianceObj->id,
            [],
            [
                'Content-Type' => 'application/json',
            ]
        );
        $this->assertApiSuccess();
        $this->asserttrue($this->getJSONContent()['success']);
        $this->assertTrue(count($this->getDataObjectArr()) > 0);

        $testCount1 = 0;
        $testCount2 = 0;
        $testCount3 = 0;

        /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
        foreach ($AdvancedVarianceObj->advancedVarianceLineItems as $AdvancedVarianceLineItemObj)
        {
            if ($AdvancedVarianceLineItemObj->native_account_id)
            {
                if ($testCount1++ > 3)
                {
                    continue;
                }
                $this->assertNull($AdvancedVarianceLineItemObj->report_template_account_group_id);
                $this->assertNull($AdvancedVarianceLineItemObj->report_template_account_group_overage_threshold_amount);
                $this->assertNull($AdvancedVarianceLineItemObj->report_template_account_group_overage_threshold_percent);
                $this->assertNull($AdvancedVarianceLineItemObj->report_template_account_group_overage_threshold_operator);
                $this->assertEquals(
                    $native_account_advanced_variance_thresholds_arr['native_account_overage_threshold_amount'],
                    $AdvancedVarianceLineItemObj->native_account_overage_threshold_amount
                );
                $this->assertEquals(
                    $native_account_advanced_variance_thresholds_arr['native_account_overage_threshold_percent'],
                    $AdvancedVarianceLineItemObj->native_account_overage_threshold_percent
                );
                $this->assertEquals(
                    $native_account_advanced_variance_thresholds_arr['native_account_overage_threshold_operator'],
                    $AdvancedVarianceLineItemObj->native_account_overage_threshold_operator
                );

                /**
                 * now test advancedVarianceLineItems history routes
                 */
                $this->json(
                    'GET',
                    '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $advanced_variance_data_arr['property_id'] . '/nativeAccount/' . $AdvancedVarianceLineItemObj->native_account_id . '/advancedVarianceLineItems?startDate=2010-01-01&endDate=2022-04-01'
                );
                $this->assertApiSuccess();
            }
            elseif ($AdvancedVarianceLineItemObj->report_template_account_group_id)
            {
                if ($testCount2++ > 3)
                {
                    continue;
                }
                $this->assertNull($AdvancedVarianceLineItemObj->native_account_id);
                $this->assertNull($AdvancedVarianceLineItemObj->native_account_overage_threshold_amount);
                $this->assertNull($AdvancedVarianceLineItemObj->native_account_overage_threshold_percent);
                $this->assertNull($AdvancedVarianceLineItemObj->native_account_overage_threshold_operator);
                if ($AdvancedVarianceLineItemObj->reportTemplateAccountGroup->parent_report_template_account_group_id)
                {
                    $this->assertEquals(
                        $non_parent_advanced_variance_thresholds_arr['report_template_account_group_overage_threshold_amount'],
                        $AdvancedVarianceLineItemObj->report_template_account_group_overage_threshold_amount
                    );
                    $this->assertEquals(
                        $non_parent_advanced_variance_thresholds_arr['report_template_account_group_overage_threshold_percent'],
                        $AdvancedVarianceLineItemObj->report_template_account_group_overage_threshold_percent
                    );
                    $this->assertEquals(
                        $non_parent_advanced_variance_thresholds_arr['report_template_account_group_overage_threshold_operator'],
                        $AdvancedVarianceLineItemObj->report_template_account_group_overage_threshold_operator
                    );
                }
                else
                {
                    $this->assertEquals(
                        $ultimate_parentAdvanced_variance_thresholds_arr['report_template_account_group_overage_threshold_amount'],
                        $AdvancedVarianceLineItemObj->report_template_account_group_overage_threshold_amount
                    );
                    $this->assertEquals(
                        $ultimate_parentAdvanced_variance_thresholds_arr['report_template_account_group_overage_threshold_percent'],
                        $AdvancedVarianceLineItemObj->report_template_account_group_overage_threshold_percent
                    );
                    $this->assertEquals(
                        $ultimate_parentAdvanced_variance_thresholds_arr['report_template_account_group_overage_threshold_operator'],
                        $AdvancedVarianceLineItemObj->report_template_account_group_overage_threshold_operator
                    );
                }

                /**
                 * now test advancedVarianceLineItems history routes
                 */
                $this->json(
                    'GET',
                    '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $advanced_variance_data_arr['property_id'] . '/reportTemplateAccountGroup/' . $AdvancedVarianceLineItemObj->report_template_account_group_id . '/advancedVarianceLineItems?startDate=2010-01-01&endDate=2022-04-01'
                );
                $this->assertApiSuccess();
            }
            elseif ($AdvancedVarianceLineItemObj->calculated_field_id)
            {
                if ($testCount3++ > 3)
                {
                    continue;
                }
                /**
                 * now test advancedVarianceLineItems history routes
                 */
                $this->json(
                    'GET',
                    '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $advanced_variance_data_arr['property_id'] . '/calculatedField/' . $AdvancedVarianceLineItemObj->calculated_field_id . '/advancedVarianceLineItems?startDate=2010-01-01&endDate=2022-04-01'
                );
                $this->assertApiSuccess();
            }
            else
            {
                $this->assertTrue(false);
            }

        }
        $this->assertGreaterThan(0, $testCount1);
        $this->assertGreaterThan(0, $testCount2);

        $this->json(
            'GET',
            '/api/v1/report/clients/' . $this->ClientObj->id .
            '/properties/' . $AdvancedVarianceObj->property_id . '/advancedVariance/' . $AdvancedVarianceObj->id . '/report',
            [],
            [
                'Content-Type' => 'application/json',
            ]
        );
        $this->assertApiSuccess();
        $this->asserttrue($this->getJSONContent()['success']);
        $this->assertTrue(count($this->getDataObjectArr()) > 0);

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id .
            '/properties/' . $AdvancedVarianceObj->property_id . '/advancedVariances/' . $AdvancedVarianceObj->id,
            [],
            [
                'Content-Type' => 'application/json',
            ]
        );
        $this->assertApiSuccess();
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        unset($this->ApiKeyObj);
        unset($this->UserObj);
        parent::tearDown();
    }
}
