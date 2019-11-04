<?php

namespace App\Waypoint\Tests\Repository;

use App\Waypoint\Models\PropertySummary;
use App;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class PropertySummaryRepositoryTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class PropertySummaryRepositoryTest extends TestCase
{
    use MakePropertyTrait, ApiTestTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_creates_property_summary()
    {
        /** @var  array $PropertySummary_arr */
        $PropertySummary_arr       = $this->fakePropertyData();
        $createdPropertySummaryObj = $this->PropertySummaryRepositoryObj->create($PropertySummary_arr);

        /** @var  array $createdPropertySummary_arr */
        $createdPropertySummary_arr = $createdPropertySummaryObj->toArray();
        $this->assertArrayHasKey('id', $createdPropertySummaryObj);
        $this->assertNotNull($createdPropertySummary_arr['id'], 'Created PropertySummary must have id specified');
        $this->assertNotNull(PropertySummary::find($createdPropertySummary_arr['id']), 'PropertySummary with given id must be in DB');
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_reads_property_summary()
    {
        /** @var  PropertySummary $PropertySummaryObj */
        $PropertySummaryObj = $this->PropertySummaryRepositoryObj->find($this->FirstPropertyObj->id);

        /** @var  PropertySummary $dbPropertySummaryObj */
        $dbPropertySummaryObj = $this->PropertySummaryRepositoryObj->find($PropertySummaryObj->id);
        $this->assertTrue($dbPropertySummaryObj->validate());
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_updates_property_summary()
    {
        /** @var  PropertySummary $PropertySummaryObj */
        $PropertySummaryObj      = $this->PropertySummaryRepositoryObj->find($this->FirstPropertyObj->id);
        $fakePropertySummary_arr = $this->fakePropertyData();
        $this->PropertySummaryRepositoryObj->update($fakePropertySummary_arr, $PropertySummaryObj->id);

        /** @var  PropertySummary $dbPropertySummaryObj */
        $this->PropertySummaryRepositoryObj->find($PropertySummaryObj->id);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_deletes_property_summary()
    {
        /** @var  PropertySummary $PropertySummaryObj */
        $PropertySummaryObj = $this->PropertySummaryRepositoryObj->find($this->FirstPropertyObj->id);
        $resp               = $this->PropertySummaryRepositoryObj->delete($PropertySummaryObj->id);
        $this->assertTrue($resp);
        $this->assertNull(PropertySummary::find($PropertySummaryObj->id), 'PropertySummary should not exist in DB');
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        unset($this->PropertySummaryRepositoryObj);
        parent::tearDown();
    }
}