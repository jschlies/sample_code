<?php

namespace App\Waypoint\Tests\Repository;

use App;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\Generated\MakeLeaseTrait;

/**
 * Class LeaseRepositoryBaseTest
 * @codeCoverageIgnore
 */
class LeaseRepositoryTest extends TestCase
{
    use MakeLeaseTrait, ApiTestTrait;

    /**
     * @throws App\Waypoint\Exceptions\GeneralException
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function it_can_create_read_update_delete_leases()
    {
        /** @var  array $properties_arr */
        $PropertyObj                = $this->ClientObj->properties->first();
        $PropertyObj->property_code = 'abc123';
        $this->LeaseRepositoryObj->upload_leases_for_property($PropertyObj->id);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        unset($this->LeaseRepositoryObj);
        parent::tearDown();
    }
}