<?php

namespace App\Waypoint\Tests\Api\Root;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AccessListSummary;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeAccessListUserTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class AccessListSummaryApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class AccessListSummaryApiTest extends TestCase
{
    use MakeAccessListTrait, ApiTestTrait;
    use MakeUserTrait, MakeAccessListUserTrait;

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
    public function it_can_read_access_list_summary()
    {
        /** @var  AccessList $AccessListObj */
        $AccessListObj = $this->makeAccessList();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/accessListsSummary/' . $AccessListObj->id
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     */
    public function it_can_read_access_list_summary_list()
    {
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/accessListsSummary?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertApiListResponse(AccessListSummary::class);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
