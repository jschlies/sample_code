<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AccessListDetail;
use App\Waypoint\Models\AccessListSummary;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeAccessListUserTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;
use function implode;

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
    use MakeUserTrait;

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
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     */
    public function it_can_read_accessible_properties_for_user_test_audits()
    {
        /**
         * Create an access List
         */
        $access_list_data_arr = $this->fakeAccessListData();
        $this->json(
            'POST', 'api/v1/clients/' . $this->ClientObj->id . '/accessList',
            $access_list_data_arr
        );
        $this->assertApiSuccess();
        $access_list_id = $this->getFirstDataObject()['id'];

        /** @var App\Waypoint\Models\AccessList $AccessListObj */
        $AccessListObj = $this->AccessListDetailRepositoryObj->find($access_list_id);
        $this->json(
            'GET',
            'api/v1/clients/' . $this->ClientObj->id . '/accessListDetail/',
            $access_list_data_arr
        );
        $this->assertApiListResponse(AccessListDetail::class);
        $this->json(
            'GET',
            'api/v1/clients/' . $this->ClientObj->id . '/accessListDetail/' . $access_list_id,
            $access_list_data_arr
        );
        $this->assertApiSuccess();
        /**
         * add property to an access List
         */
        $PropertyObj              = $this->FirstPropertyObj;
        $access_list_property_arr = [
            'access_list_id' => $AccessListObj->id,
            'property_id'    => $PropertyObj->id,
        ];
        $this->json(
            'POST',
            'api/v1/clients/' . $AccessListObj->client_id . '/accessList/' . $AccessListObj->id . '/accessListProperty',
            $access_list_property_arr
        );
        $this->assertApiSuccess();
        /**
         * re-add property - should work
         */
        $this->json(
            'POST',
            'api/v1/clients/' . $AccessListObj->client_id . '/accessList/' . $AccessListObj->id . '/accessListProperty',
            $access_list_property_arr
        );
        $this->assertApiSuccess();
        $this->json(
            'GET',
            'api/v1/clients/' . $AccessListObj->client_id . '/properties/' . $PropertyObj->id . '/accessListDetails',
            $access_list_data_arr
        );
        $this->assertApiListResponse(AccessListDetail::class);

        /**
         * add user to an access List
         */
        $UserObj              = $this->FirstGenericUserObj;
        $access_list_user_arr = [
            'access_list_id' => $AccessListObj->id,
            'user_id'        => $UserObj->id,
        ];
        $this->json(
            'POST',
            'api/v1/clients/' . $AccessListObj->client_id . '/accessList/' . $AccessListObj->id . '/accessListUser',
            $access_list_user_arr
        );
        $this->assertApiSuccess();
        /**
         * re-add user - should work
         */
        $this->json(
            'POST',
            'api/v1/clients/' . $AccessListObj->client_id . '/accessList/' . $AccessListObj->id . '/accessListUser',
            $access_list_user_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            'api/v1/clients/' . $AccessListObj->client_id . '/properties/' . $PropertyObj->id . '/accessListUsers',
            $access_list_data_arr
        );
        $this->assertApiListResponse(User::class);

        $this->json(
            'GET',
            'api/v1/clients/' . $AccessListObj->client_id . '/accessLists',
            $access_list_data_arr
        );
        $this->assertApiListResponse(AccessListDetail::class);

        $this->json(
            'GET',
            'api/v1/clients/' . $AccessListObj->client_id . '/accessLists',
            $access_list_data_arr
        );
        $this->assertApiListResponse(AccessListDetail::class);

        if (config('waypoint.enable_audits', false))
        {
            $this->json(
                'GET',
                '/api/v1/clients/' . $AccessListObj->client_id . '/accessLists/' . $AccessListObj->id . '/audits'
            );
            $this->assertApiSuccess();
        }
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $AccessListObj->client_id . '/users/' . $UserObj->id . '/accessListSummary?limit=' . config(
                'waypoint.unittest_loop'
            )
        );
        $this->assertApiSuccess();

        $this->assertAPIListResponse(App\Waypoint\Models\AccessListSummary::class);

        $this->assertTrue(is_array($this->getDataObjectArr()), 'Response element is not an array');

        $this->json(
            'GET',
            '/api/v1/clients/' . $AccessListObj->client_id . '/userDetails/' . $UserObj->id . '/accessListSummary?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertAPIListResponse(AccessListSummary::class);

        $this->assertTrue(is_array($this->getDataObjectArr()), 'Response element is not an array');

        foreach ($this->getDataObjectArr() as $responseDataElement)
        {
            $AccessListSummaryObj = AccessListSummary::find($responseDataElement['id']);

            $this->assertInstanceOf(Client::class, $AccessListSummaryObj->client);
            foreach ($AccessListSummaryObj->accessListProperties as $AccessListPropertyObj)
            {
                $this->assertInstanceOf(App\Waypoint\Models\AccessListProperty::class, $AccessListPropertyObj);
                $this->assertInstanceOf(App\Waypoint\Models\Property::class, $AccessListPropertyObj->property);
                foreach ($AccessListPropertyObj->accessList->accessListUsers as $AccessListUser)
                {
                    $this->assertInstanceOf(App\Waypoint\Models\AccessListUser::class, $AccessListUser);
                    $this->assertInstanceOf(App\Waypoint\Models\User::class, $AccessListUser->user);
                }
            };
        }

        /**
         *  data to test audits
         */
        $UserObj = $this->SecondGenericUserObj;
        //$PropertyObj = $this->makeProperty(['client_id' => $AccessListObj->client_id]);
        $PropertyObj = $this->SecondPropertyObj;

        $access_list_property_arr = [
            'access_list_id' => $AccessListObj->id,
            'property_id'    => $PropertyObj->id,
        ];
        $this->json(
            'POST',
            'api/v1/clients/' . $AccessListObj->client_id . '/accessList/' . $AccessListObj->id . '/accessListProperty',
            $access_list_property_arr
        );

        $access_list_user_arr = [
            'access_list_id' => $AccessListObj->id,
            'user_id'        => $UserObj->id,
        ];
        $this->json(
            'POST',
            'api/v1/clients/' . $AccessListObj->client_id . '/accessList/' . $AccessListObj->id . '/accessListUser',
            $access_list_user_arr
        );
        $this->assertApiSuccess();

        if (config('waypoint.enable_audits', false))
        {
            $this->json(
                'GET',
                '/api/v1/clients/' . $AccessListObj->client_id . '/accessLists/' . $AccessListObj->id . '/audits'
            );
            $this->assertApiSuccess();
            $this->assertTrue(is_array($this->getJSONContent()));
            $this->assertTrue(count($this->getDataObjectArr()) > 0);
            $this->assertAuditIsValid($this->getDataObjectArr(), 'created');

            foreach ($AccessListObj->accessListProperties as $AccessListPropertyObj)
            {
                $this->json(
                    'GET',
                    '/api/v1/clients/' . $AccessListObj->client_id . '/accessListProperties/' . $AccessListPropertyObj->id . '/audits'
                );
                $this->assertApiSuccess();
                $this->assertTrue(is_array($this->getJSONContent()));
                $this->assertTrue(count($this->getDataObjectArr()) > 0);
                $this->assertAuditIsValid($this->getDataObjectArr(), 'created');
            }

            $this->json(
                'GET',
                '/api/v1/clients/' . $AccessListObj->client_id . '/accessLists/' . $AccessListObj->id . '/audits'
            );
            $this->assertApiSuccess();
            foreach ($AccessListObj->accessListUsers as $AccessListUserObj)
            {
                $this->json(
                    'GET',
                    '/api/v1/clients/' . $AccessListObj->client_id . '/accessListUsers/' . $AccessListUserObj->id . '/audits'
                );
                $this->assertApiSuccess();
                $this->assertTrue(is_array($this->getJSONContent()));
                $this->assertTrue(count($this->getDataObjectArr()) > 0);
                $this->assertAuditIsValid($this->getDataObjectArr(), 'created');

            }

            $this->json(
                'GET',
                '/api/v1/clients/' . $AccessListObj->client_id . '/accessLists/' . $AccessListObj->id . '/audits'
            );
            $this->assertApiSuccess();

            /**
             * now unwind
             */

            foreach ($AccessListObj->accessListProperties as $AccessListPropertyObj)
            {
                $this->json(
                    'DELETE',
                    '/api/v1/clients/' . $AccessListObj->client_id . '/accessList/' . $AccessListObj->id . '/accessListProperty/' . $AccessListPropertyObj->id
                );
                $this->assertApiSuccess();

                $AccessListObj->refresh();

                if (config('waypoint.enable_audits', false))
                {
                    $this->json(
                        'GET',
                        '/api/v1/clients/' . $AccessListObj->client_id . '/accessListProperties/' . $AccessListPropertyObj->id . '/audits'
                    );
                    $this->assertApiFailure();
                }

            }

            $this->json(
                'GET',
                '/api/v1/clients/' . $AccessListObj->client_id . '/accessLists/' . $AccessListObj->id . '/audits'
            );
            $this->assertApiSuccess();
            /**
             * now unwind
             */
            $i = 0;
            foreach ($AccessListObj->accessListUsers as $AccessListUserObj)
            {
                $this->json(
                    'DELETE',
                    '/api/v1/clients/' . $AccessListObj->client_id . '/accessList/' . $AccessListObj->id . '/accessListUser/' . $AccessListUserObj->id
                );
                $this->assertApiSuccess();

                $AccessListObj->refresh();
                if (config('waypoint.enable_audits', false))
                {
                    $this->json(
                        'GET',
                        '/api/v1/clients/' . $AccessListObj->client_id . '/accessListUsers/' . $AccessListUserObj->id . '/audits'
                    );
                    $this->assertApiFailure();
                    $this->json(
                        'GET',
                        '/api/v1/clients/' . $AccessListObj->client_id . '/accessLists/' . $AccessListObj->id . '/audits'
                    );
                    $this->assertApiSuccess();
                }

                if ($i >= config('waypoint.unittest_loop'))
                {
                    break;
                }
                if ($i >= config('waypoint.unittest_loop'))
                {
                    break;
                }
            }
        }

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $AccessListObj->client_id . '/accessList/' . $AccessListObj->id
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     */
    public function it_can_multiple_accessible_users_test()
    {
        $FirstAccessListDetailObj  = $this->FirstAccessListObj;
        $SecondAccessListDetailObj = $this->SecondAccessListObj;

        $FirstUserObj  = $this->FirstGenericUserObj;
        $SecondUserObj = $this->SecondGenericUserObj;
        $ThirdUserObj  = $this->ThirdGenericUserObj;

        /** @noinspection PhpUndefinedFieldInspection */
        $access_list_user_arr = [
            'access_list_id' => implode(',', [$FirstAccessListDetailObj->id, $SecondAccessListDetailObj->id]),
            'user_id'        => implode(',', [$FirstUserObj->id, $SecondUserObj->id, $ThirdUserObj->id]),
        ];
        $this->json(
            'POST',
            'api/v1/clients/' . $FirstAccessListDetailObj->client_id . '/accessList/' . $FirstAccessListDetailObj->id . ',' . $SecondAccessListDetailObj->id . '/accessListUser',
            $access_list_user_arr
        );
        $this->assertApiSuccess();

        $this->assertEquals(count($this->getDataObjectArr()), 6);
    }

    /**
     * @test
     */
    public function it_can_multiple_accessible_users_by_access_list_id_arr_test()
    {
        /** @var AccessList $FirstAccessListDetailObj */
        $FirstAccessListDetailObj = $this->FirstAccessListObj;
        /** @var AccessList $SecondAccessListDetailObj */
        $SecondAccessListDetailObj = $this->SecondAccessListObj;
        /** @var AccessList $ThirdAccessListDetailObj */
        $ThirdAccessListDetailObj = $this->ThirdAccessListObj;
        /** @var User $FirstUserObj */
        $FirstUserObj = $this->ThirdGenericUserObj;

        $access_list_user_arr = [
            'access_list_id' => implode(',', [$FirstAccessListDetailObj->id, $SecondAccessListDetailObj->id, $ThirdAccessListDetailObj->id]),
            'user_id'        => implode(',', [$FirstUserObj->id]),
        ];
        $this->json(
            'POST',
            'api/v1/clients/' . $FirstAccessListDetailObj->client_id . '/accessList/' . $FirstAccessListDetailObj->id . ',' . $SecondAccessListDetailObj->id . ',' . $ThirdAccessListDetailObj->id . '/accessListUser',
            $access_list_user_arr
        );
        $this->assertApiSuccess();

        $this->assertEquals(count($this->getDataObjectArr()), 3);

        /** @noinspection PhpUndefinedFieldInspection */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $FirstAccessListDetailObj->client_id . '/accessLists/' . $FirstAccessListDetailObj->id . '/users/' . $FirstUserObj->id . '/multi'
        );
        $this->assertApiSuccess();

        /** @noinspection PhpUndefinedFieldInspection */
        $this->json(
            'GET',
            '/api/v1/clients/' . $FirstAccessListDetailObj->client_id . '/users/' . $FirstUserObj->id . '/accessListSummary?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertApiSuccess();
        $this->assertEquals(count($this->getDataObjectArr()), 2);

        /** @noinspection PhpUndefinedFieldInspection */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $FirstAccessListDetailObj->client_id . '/accessLists/' . implode(',', [
                $ThirdAccessListDetailObj->id,
                $SecondAccessListDetailObj->id,
            ]) . '/users/' . $FirstUserObj->id . '/multi'
        );
        $this->assertApiSuccess();

        /** @noinspection PhpUndefinedFieldInspection */
        $this->json(
            'GET',
            '/api/v1/clients/' . $FirstAccessListDetailObj->client_id . '/users/' . $FirstUserObj->id . '/accessListSummary?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertApiSuccess();
        $this->assertEquals(count($this->getDataObjectArr()), 0);
    }

    /**
     * @test
     *
     */
    public function it_cannot_multiple_accessible_users_test()
    {

        /** @var AccessList $FirstAccessListDetailObj */
        $FirstAccessListDetailObj = $this->FirstAccessListObj;
        /** @var AccessList $SecondAccessListDetailObj */
        $SecondAccessListDetailObj = $this->SecondAccessListObj;

        /** @var User $FirstUserObj */
        $FirstUserObj = $this->FifthGenericUserObj;
        /** @var User $SecondUserObj */
        $SecondUserObj = $this->SixthGenericUserObj;
        /** @var User $ThirdUserObj */
        $ThirdUserObj = $this->SeventhGenericUserObj;

        $access_list_user_arr = [
            'access_list_id' => [$FirstAccessListDetailObj->id, $SecondAccessListDetailObj->id],
            'user_id'        => [$FirstUserObj->id, $SecondUserObj->id, $ThirdUserObj->id],
        ];
        $this->json(
            'POST',
            'api/v1/clients/' . $FirstAccessListDetailObj->client_id . '/accessList/' . $FirstAccessListDetailObj->id . ',' . $SecondAccessListDetailObj->id . ',9999999/accessListUser',
            $access_list_user_arr
        );
        $this->assertApiFailure();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id .
            '/users/' . $FirstUserObj->id . '/accessListDetail?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertAPIListResponse(AccessListDetail::class, 0, 0);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
