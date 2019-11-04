<?php

namespace App\Waypoint\Tests\Api\Root;

use App\Waypoint\Models\AccessListDetail;
use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Models\AccessListUser;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\User;
use App;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeAccessListUserTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class AccessListDetailAPITest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class AccessListDetailAPITest extends TestCase
{
    use MakeAccessListTrait, ApiTestTrait;
    use MakeAccessListUserTrait;
    use MakePropertyTrait;

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
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     */
    public function it_can_read_access_list_details_list_for_user()
    {
        $AccessListUserObj = $this->makeAccessListUser(
            [
                'access_list_id'      => $this->FirstAccessListObj->id,
                'access_list_user_id' => $this->FifthGenericUserObj->id,
            ]
        );

        $this->json(
            'GET', '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $AccessListUserObj->user_id . '/accessListDetail?limit=' . config(
                     'waypoint.unittest_loop'
                 )
        );
        $this->assertAPIListResponse(AccessListDetail::class);

        $this->assertTrue(count($this->getDataObjectArr()) <= config('waypoint.unittest_loop'), 'Response element issue');
        foreach ($this->getDataObjectArr() as $responseDataElement)
        {
            $AccessListDetailObj = AccessListDetail::find($responseDataElement['id']);

            $this->assertInstanceOf(Client::class, $AccessListDetailObj->client);
            foreach ($AccessListDetailObj->accessListProperties as $AccessListPropertyObj)
            {
                $this->assertInstanceOf(AccessListProperty::class, $AccessListPropertyObj);
                $this->assertInstanceOf(Property::class, $AccessListPropertyObj->property);

            };
            foreach ($AccessListDetailObj->accessListUsers as $AccessListUser)
            {
                $this->assertInstanceOf(AccessListUser::class, $AccessListUser);
                $this->assertInstanceOf(User::class, $AccessListUser->user);
            }
        }
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function it_can_read_accessible_properties_for_user()
    {
        $AccessListUserObj = $this->makeAccessListUser(
            [
                'access_list_id'      => $this->FirstAccessListObj->id,
                'access_list_user_id' => $this->FifthGenericUserObj->id,
            ]
        );

        $Property = $this->FirstPropertyObj;
        $this->AccessListPropertyRepositoryObj->create(
            [
                'access_list_id' => $AccessListUserObj->access_list_id,
                'property_id'    => $Property->id,
            ]
        );

        $this->json(
            'GET', '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $AccessListUserObj->user_id . '/accessibleProperties?limit=' . config(
                     'waypoint.unittest_loop'
                 )
        );
        $this->assertAPIListResponse(Property::class);

        $this->assertTrue(is_array($this->getDataObjectArr()), 'Response element is not an array');

        foreach ($this->getDataObjectArr() as $responseDataElement)
        {
            $PropertyObj = Property::find($responseDataElement['id']);

            $this->assertInstanceOf(Client::class, $PropertyObj->client);
            foreach ($PropertyObj->accessListProperties as $AccessListPropertyObj)
            {
                $this->assertInstanceOf(AccessListProperty::class, $AccessListPropertyObj);
                $this->assertInstanceOf(Property::class, $AccessListPropertyObj->property);
                foreach ($AccessListPropertyObj->accessList->accessListUsers as $AccessListUser)
                {
                    $this->assertInstanceOf(AccessListUser::class, $AccessListUser);
                    $this->assertInstanceOf(User::class, $AccessListUser->user);
                }
            };
        }

        /**
         * v2
         */
        $this->json(
            'GET', '/api/v2/clients/' . $this->ClientObj->id . '/users/' . $AccessListUserObj->user_id . '/accessibleProperties?limit=' . config(
                     'waypoint.unittest_loop'
                 )
        );
        $this->assertAPIListResponse(Property::class);

        $this->assertTrue(is_array($this->getDataObjectArr()), 'Response element is not an array');

        foreach ($this->getDataObjectArr() as $responseDataElement)
        {
            $PropertyObj = Property::find($responseDataElement['id']);

            $this->assertInstanceOf(Client::class, $PropertyObj->client);
            foreach ($PropertyObj->accessListProperties as $AccessListPropertyObj)
            {
                $this->assertInstanceOf(AccessListProperty::class, $AccessListPropertyObj);
                $this->assertInstanceOf(Property::class, $AccessListPropertyObj->property);
                foreach ($AccessListPropertyObj->accessList->accessListUsers as $AccessListUser)
                {
                    $this->assertInstanceOf(AccessListUser::class, $AccessListUser);
                    $this->assertInstanceOf(User::class, $AccessListUser->user);
                }
            };
        }
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
