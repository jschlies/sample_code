<?php

namespace App\Waypoint\Tests\Api\Root;

use App\Waypoint\Models\UserSummary;
use App;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class UserSummaryApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class UserSummaryApiTest extends TestCase
{
    use MakeUserTrait, ApiTestTrait;

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
    public function it_can_read_usersSummary()
    {
        /** @var  UserSummary $UserSummaryObj */
        $UserSummaryObj = $this->ThirdGenericUserObj;
        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/usersSummary/' . $UserSummaryObj->id);

        $this->assertTrue(method_exists($UserSummaryObj, 'audits'));
        $this->assertTrue(! is_null($UserSummaryObj->getMorphClass()));
        $this->assertApiSuccess();
    }

    /**
     * @test
     */
    public function it_can_read_usersSummary_list()
    {
        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/usersSummary?limit=' . config('waypoint.unittest_loop'));
        $this->assertApiSuccess();

        $this->assertApiListResponse(UserSummary::class);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
