<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeAccessListUserTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;
use Illuminate\Support\Facades\Cache;

/**
 * Class AccessListTrimmedSummaryApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class AccessListTrimmedSummaryApiTest extends TestCase
{
    use MakeAccessListTrait, ApiTestTrait;
    use MakeUserTrait, MakeAccessListUserTrait;

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
     */
    public function it_can_read_user_access_list_summary_list()
    {
        $UserObj           = $this->getLoggedInUserObj();
        $UserRepositoryObj = App::make(App\Waypoint\Repositories\UserRepository::class);

        /**
         * let's hide some users
         */
        $this->ClientObj->users->map(
            function (User $UserObj)
            {
                if (
                    $UserObj->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE) ||
                    $UserObj->hasRole(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE)
                )
                {
                    $UserObj->is_hidden = true;
                    $UserObj->save();
                }
            }
        );
        Cache::flush();
        $this->ClientObj->refresh();

        $VisibleToOnlyWaypointUserObjArr =
            $UserRepositoryObj->findWhere(['client_id' => $this->ClientObj->id])->filter(
                function (User $UserObj)
                {
                    return $UserObj->is_hidden;
                }
            );
        $VisibleToClientsUserObjArr      =
            $UserRepositoryObj->findWhere(['client_id' => $this->ClientObj->id])->filter(
                function (User $UserObj)
                {
                    return ! $UserObj->is_hidden;
                }
            );

        $number_of_users_for_this_client = $UserRepositoryObj->findWhere(['client_id' => $UserObj->client_id])->count();
        $this->assertEquals($number_of_users_for_this_client, $VisibleToOnlyWaypointUserObjArr->count() + $VisibleToClientsUserObjArr->count());

        $this->json('GET', '/api/v1/clients/' . $UserObj->client_id . '/accessListsPerUser');
        $this->assertApiSuccess();
        $this->assertEquals($VisibleToClientsUserObjArr->count(), count($this->getDataObjectArr()));
    }

    /**
     * @test
     */
    public function it_can_read_user_access_list_summary_list_deprecated()
    {
        $UserObj           = $this->getLoggedInUserObj();
        $UserRepositoryObj = App::make(App\Waypoint\Repositories\UserRepository::class);

        /**
         * let's hide some users
         */
        $this->ClientObj->users->map(
            function (User $UserObj)
            {
                if (
                    $UserObj->hasRole(Role::WAYPOINT_ASSOCIATE_ROLE) ||
                    $UserObj->hasRole(Role::WAYPOINT_SYSTEM_ADMINISTRATOR_ROLE)
                )
                {
                    $UserObj->is_hidden = true;
                    $UserObj->save();
                }
            }
        );
        Cache::flush();
        $this->ClientObj->refresh();

        $VisibleToOnlyWaypointUserObjArr =
            $UserRepositoryObj->findWhere(['client_id' => $this->ClientObj->id])->filter(
                function (User $UserObj)
                {
                    return $UserObj->is_hidden;
                }
            );
        $VisibleToClientsUserObjArr      =
            $UserRepositoryObj->findWhere(['client_id' => $this->ClientObj->id])->filter(
                function (User $UserObj)
                {
                    return ! $UserObj->is_hidden;
                }
            );

        $number_of_users_for_this_client = $UserRepositoryObj->findWhere(['client_id' => $UserObj->client_id])->count();
        $this->assertEquals($number_of_users_for_this_client, $VisibleToOnlyWaypointUserObjArr->count() + $VisibleToClientsUserObjArr->count());

        $this->json('GET', '/api/v1/ClientAdmin/clients/' . $UserObj->client_id . '/accessListsPerUser');
        $this->assertApiSuccess();
        $this->assertEquals($VisibleToClientsUserObjArr->count(), count($this->getDataObjectArr()));
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
