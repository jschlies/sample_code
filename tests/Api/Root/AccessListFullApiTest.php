<?php

namespace App\Waypoint\Tests\Api\Root;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessListFull;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class AccessListFullApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class AccessListFullApiTest extends TestCase
{
    use MakeAccessListTrait, ApiTestTrait;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function it_can_read_access_lists_full()
    {
        /** @var  AccessListFull $AccessListFullObj */
        $AccessListFullObj = $this->makeAccessList();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/accessListsFull/' . $AccessListFullObj->id
        );
        $this->assertApiSuccess();

        $this->assertTrue(is_array($this->getFirstDataObject()['accessListPropertiesFull']));
        $this->assertTrue(is_array($this->getFirstDataObject()['accessListUsersFull']));
    }

    /**
     * @test
     */
    public function it_can_read_access_lists_full_list()
    {
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/accessListsFull?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertApiListResponse(AccessListFull::class);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
