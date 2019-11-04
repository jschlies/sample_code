<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Role;
use App\Waypoint\Repositories\AdvancedVarianceExplanationTypeRepository;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAdvancedVarianceExplanationTypeTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class AdvancedVarianceExplanationTypeApiBaseTest
 *
 * @codeCoverageIgnore
 */
class AdvancedVarianceExplanationTypeApiTest extends TestCase
{
    use MakeAdvancedVarianceExplanationTypeTrait, ApiTestTrait;

    /**
     * @var AdvancedVarianceExplanationTypeRepository
     * this is needed in MakeAdvancedVarianceExplanationTypeTrait
     */
    protected $advancedVarianceExplanationTypeRepositoryObj;

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

        /** @var  array $advanced_variance_explanation_types_arr */
        $advanced_variance_explanation_types_arr = $this->fakeAdvancedVarianceExplanationTypeData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceExplanationTypes',
            $advanced_variance_explanation_types_arr
        );
        $this->assertApiSuccess();
        $advanced_variance_explanation_type_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceExplanationTypes/' . $advanced_variance_explanation_type_id
        );
        $this->assertApiSuccess();

        /** @var  array $advanced_variance_explanation_types_arr */
        $advanced_variance_explanation_types_arr = $this->fakeAdvancedVarianceExplanationTypeData();
        unset($advanced_variance_explanation_types_arr['color']);
        unset($advanced_variance_explanation_types_arr['sort_order']);
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceExplanationTypes',
            $advanced_variance_explanation_types_arr
        );
        $this->assertApiSuccess();
        $advanced_variance_explanation_type_id = $this->getFirstDataObject()['id'];

        /** @var  array $advanced_variance_explanation_types_arr */
        $advanced_variance_explanation_types_arr          = $this->fakeAdvancedVarianceExplanationTypeData();
        $advanced_variance_explanation_types_arr['color'] = 'blue';
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceExplanationTypes',
            $advanced_variance_explanation_types_arr
        );
        $this->assertApiFailure();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceExplanationTypes/' . $advanced_variance_explanation_type_id
        );
        $this->assertApiSuccess();
        $this->assertEquals(1, count($this->getJSONContent()['data']));

        $advanced_variance_explanation_types_arr = $this->fakeAdvancedVarianceExplanationTypeData();
        /**
         * since users are never deleted, just made inactive......
         */
        if (get_class($this) == 'App\Waypoint\Tests\Generated\UserApiBaseTest')
        {
            $this->assertApiSuccess();
        }
        else
        {
            /**
             * now re-add it
             */
            $this->json(
                'POST',
                '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceExplanationTypes',
                $advanced_variance_explanation_types_arr
            );
            $this->assertApiSuccess();
        }

        $advanced_variance_explanation_type_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceExplanationTypes/' . $advanced_variance_explanation_type_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceExplanationTypes'
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
