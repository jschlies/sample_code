<?php

namespace App\Waypoint\Tests\Api\Root;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\PropertySummary;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class PropertySummaryApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class PropertySummaryApiTest extends TestCase
{
    use MakePropertyTrait, ApiTestTrait;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_can_read_property_summary()
    {
        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/propertiesSummary/' . $this->FirstPropertyObj->id);
        $this->assertApiSuccess();

        $this->assertTrue(isset($this->getFirstDataObject()['city']));
        $this->assertTrue(isset($this->getFirstDataObject()['name']));
        $this->assertApiSuccess();
    }

    /**
     * @test
     */
    public function it_can_read_property_summary_list()
    {
        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/propertiesSummary?limit=' . config('waypoint.unittest_loop'));
        $this->assertApiSuccess();

        $this->assertApiListResponse(PropertySummary::class);
    }

    /**
     * @param array $PropertiesSummaryFields
     * @return \App\Waypoint\Models\Property
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function makePropertySummary($PropertiesSummaryFields = [])
    {
        $theme = $this->fakePropertySummaryData($PropertiesSummaryFields);
        return $this->PropertySummaryRepositoryObj->create($theme);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
