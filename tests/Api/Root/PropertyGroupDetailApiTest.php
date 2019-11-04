<?php

namespace App\Waypoint\Tests\Api\Root;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroupDetail;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\PropertyGroupProperty;
use App\Waypoint\Models\User;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakePropertyGroupTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class PropertyGroupDetailApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class PropertyGroupDetailApiTest extends TestCase
{
    use MakePropertyGroupTrait, ApiTestTrait;

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
     */
    public function it_can_read_property_group_list()
    {
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertApiListResponse(PropertyGroupDetail::class);

        $i = 0;
        foreach ($this->getDataObjectArr() as $responseDataElement)
        {
            /** @var PropertyGroup $PropertyGroupObj */
            $PropertyGroupObj = PropertyGroup::find($responseDataElement['id']);
            foreach ($PropertyGroupObj->propertyGroupProperties as $PropertyGroupPropertyObj)
            {
                $this->assertInstanceOf(PropertyGroupProperty::class, $PropertyGroupPropertyObj);
                $this->assertInstanceOf(Property::class, $PropertyGroupPropertyObj->property);
            };

            $this->assertInstanceOf(Client::class, $PropertyGroupObj->user->client);
            if ($responseDataElement['user_id'])
            {
                $this->assertInstanceOf(User::class, $PropertyGroupObj->user);
            }
            if ($i++ > config('waypoint.unittest_loop'))
            {
                break;
            }
        }
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
