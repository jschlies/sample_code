<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessListDetail;
use App\Waypoint\Models\AccessListSlim;
use App\Waypoint\Models\AccessListSummary;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeAccessListUserTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\TestCase;

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
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
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
                'access_list_user_id' => $this->SixthGenericUserObj->id,
            ]
        );

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/users/' . $AccessListUserObj->user_id . '/accessListDetail?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertAPIListResponse(AccessListDetail::class);

        foreach ($this->getDataObjectArr() as $responseDataElement)
        {
            $AccessListDetailObj = AccessListDetail::find($responseDataElement['id']);

            $this->assertInstanceOf(App\Waypoint\Models\Client::class, $AccessListDetailObj->client);
            foreach ($AccessListDetailObj->accessListProperties as $AccessListPropertyObj)
            {
                $this->assertInstanceOf(App\Waypoint\Models\AccessListProperty::class, $AccessListPropertyObj);
                $this->assertInstanceOf(App\Waypoint\Models\Property::class, $AccessListPropertyObj->property);

            };
            foreach ($AccessListDetailObj->accessListUsers as $AccessListUser)
            {
                $this->assertInstanceOf(App\Waypoint\Models\AccessListUser::class, $AccessListUser);
                $this->assertInstanceOf(App\Waypoint\Models\User::class, $AccessListUser->user);
            }
        }
    }

    /**
     * @test
     */
    public function it_can_read_user_access_list_summary_list()
    {
        $UserObj = $this->getLoggedInUserObj();

        $num_access_lists = $UserObj->accessLists->count();

        $this->json(
            'GET',
            '/api/v1/clients/' . $UserObj->client_id . '/users/' . $UserObj->id . '/accessListSummary'
        );
        $this->assertApiListResponse(AccessListSummary::class);
        $this->assertEquals($num_access_lists, count($this->getDataObjectArr()));

        $num_access_lists = $this->ClientObj->accessLists->count();

        $this->json(
            'GET',
            '/api/v1/clients/' . $UserObj->client_id . '/accessListSlim'
        );
        $this->assertApiListResponse(AccessListSlim::class);
        $this->assertEquals($num_access_lists, count($this->getDataObjectArr()));
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
