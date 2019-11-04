<?php

namespace App\Waypoint\Tests\Repository;

use App\Waypoint\Models\AccessListSummary;
use App;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class AccessListSummaryRepositoryTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class AccessListSummaryRepositoryTest extends TestCase
{
    use MakeAccessListTrait, ApiTestTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_reads_access_list_summary()
    {
        /** @var  AccessListSummary $AccessListSummaryObj */
        $AccessListSummaryObj = $this->SecondAccessListObj;

        /** @var  AccessListSummary $dbAccessListSummaryObj */
        $dbAccessListSummaryObj = $this->AccessListSummaryRepositoryObj->find($AccessListSummaryObj->id);

        $this->assertTrue($dbAccessListSummaryObj->validate());

        $this->assertTrue(is_array($dbAccessListSummaryObj->toArray()['access_list_users']));
        $this->assertTrue(is_array($dbAccessListSummaryObj->toArray()['access_list_properties']));

    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        unset($this->AccessListSummaryRepositoryObj);
        parent::tearDown();
    }
}