<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App\Waypoint\Models\PropertyDetail;
use App;
use App\Waypoint\Models\Role;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class PropertyDetailApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class PropertyDetailApiTest extends TestCase
{
    use ApiTestTrait;
    use MakePropertyTrait;

    /**
     * @var PropertyRepository
     * this is needed in MakePropertyTrait
     */
    protected $PropertyRepositoryObj;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_property_detail_list()
    {
        $this->json('GET', '/api/v1/clients/' . $this->FirstPropertyObj->client_id . '/propertyDetails');
        $this->assertApiListResponse(PropertyDetail::class);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
