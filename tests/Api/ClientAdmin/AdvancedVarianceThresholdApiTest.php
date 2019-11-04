<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVarianceThreshold;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAdvancedVarianceThresholdTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class AdvancedVarianceThresholdApiBaseTest
 *
 * @codeCoverageIgnore
 */
class AdvancedVarianceThresholdApiTest extends TestCase
{
    use MakeAdvancedVarianceThresholdTrait, ApiTestTrait;

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
    public function it_can_create_advanced_variance_explanation_types()
    {
        /**
         * no point in running tests if queues are on, unless your running workers but even then,
         * you might get a nasty race condition
         */
        $this->assertTrue(config('queue.driver', 'sync') == 'sync');

        /** @var  array $advanced_variance_threshold_arr */
        $advanced_variance_threshold_arr = $this->fakeAdvancedVarianceThresholdData();
        $original_num_thresholds         = $this->ClientObj->advancedVarianceThresholds->count();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceThresholds',
            $advanced_variance_threshold_arr
        );
        $this->assertApiSuccess();
        $advanced_variance_explanation_type_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceThresholds'
        );
        $this->assertApiListResponse(AdvancedVarianceThreshold::class);
        $this->assertEquals($original_num_thresholds + 1, count($this->getJSONContent()['data']));

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceThresholds/' . $advanced_variance_explanation_type_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceThresholds/'
        );
        $this->assertApiListResponse(AdvancedVarianceThreshold::class);
        $this->assertEquals($original_num_thresholds, count($this->getJSONContent()['data']));

        /** @var  array $advanced_variance_threshold_arr */
        $advanced_variance_threshold_arr = $this->fakeAdvancedVarianceThresholdData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceThresholds',
            $advanced_variance_threshold_arr
        );
        $this->assertApiSuccess();
        $advanced_variance_explanation_type_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceThresholds/'
        );
        $this->assertApiSuccess();
        $this->assertEquals($original_num_thresholds + 1, count($this->getJSONContent()['data']));

        /** @var  array $advanced_variance_threshold_arr */
        $advanced_variance_threshold_arr                                            = $this->fakeAdvancedVarianceThresholdData();
        $advanced_variance_threshold_arr['native_account_overage_threshold_amount'] = 123456;
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceThresholds/' . $advanced_variance_explanation_type_id,
            $advanced_variance_threshold_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceThresholds'
        );
        $this->assertApiListResponse(AdvancedVarianceThreshold::class);
        $this->assertEquals($original_num_thresholds + 1, count($this->getJSONContent()['data']));

        $advanced_variance_threshold_arr = $this->fakeAdvancedVarianceThresholdData();

        /**
         * now re-add it
         */
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceThresholds',
            $advanced_variance_threshold_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceThresholds'
        );
        $this->assertApiListResponse(AdvancedVarianceThreshold::class);
        $this->assertEquals($original_num_thresholds + 2, count($this->getJSONContent()['data']));
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
