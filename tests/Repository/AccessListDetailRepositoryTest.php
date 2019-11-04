<?php

namespace App\Waypoint\Tests\Repository;

use App\Waypoint\Models\AccessListDetail;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App;
use App\Waypoint\Tests\Generated\MakeAccessListPropertyTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeAccessListUserTrait;
use App\Waypoint\Tests\Generated\MakeClientTrait;
use App\Waypoint\Tests\Generated\MakePropertyGroupPropertyTrait;
use App\Waypoint\Tests\Generated\MakePropertyGroupTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use Carbon\Carbon;

/**
 * Class AccessListDetailRepositoryTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class AccessListDetailRepositoryTest extends TestCase
{
    use MakeAccessListTrait, ApiTestTrait;
    use MakePropertyTrait;
    use MakeUserTrait;
    use MakeAccessListUserTrait;
    use MakeAccessListPropertyTrait;
    use MakePropertyGroupTrait;
    use MakePropertyGroupPropertyTrait;
    use MakeClientTrait;

    /**
     * @var PropertyGroup
     */
    protected $NewPropertyGroupObj;

    /**
     * @var PropertyGroup
     */
    protected $FirstEmptyPropertyGroupObj;
    /**
     * @var PropertyGroup
     */
    protected $SecondEmptyPropertyGroupObj;

    protected $original_num_properties = null;
    protected $original_num_all_access_users = null;

    public function setUp()
    {
        parent::setUp();

        $this->original_num_properties       = $this->ClientObj->properties->count();
        $this->original_num_all_access_users = $this->ClientObj->users->filter(
            function (User $value)
            {
                return $value->isUserInAllAccessGroup();
            }
        )->count();

        /**
         * reset property_group_calc_status
         */
        if ($this->ClientObj)
        {
            $this->ClientObj = $this->ClientRepositoryObj->update(
                [
                    'property_group_calc_status' => Client::PROPERTY_GROUP_CALC_STATUS_IDLE,
                ],
                $this->ClientObj->id
            );
        }
        /**
         * in this test, from here forward we want to trigger events
         */
        $this->loadAllRepositories(false);
    }

    /**
     * @test
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_reads_all_access_lists()
    {
        $this->assertEquals(
            0, config('cache.ttl'),
            'For unit tests, you need to set cache.ttl to zero by either setting CACHE_TTL_TIER_4=0 in your .ENV OR update phpunit.*.xml as the config of your test'
        );

        /**
         * sanity - should have been set in the setup()
         */
        $this->assertEquals(Client::PROPERTY_GROUP_CALC_STATUS_IDLE, $this->ClientObj->property_group_calc_status);

        /****************************************************
         * create 4 users
         * FirstGenericUserObj
         * SecondGenericUserObj
         * FirstAdminUserObj
         * SecondAdminUserObj
         * // ****************************************************/
        ///** @var User $this ->FirstGenericUserObj */
        ///** @noinspection PhpUndefinedMethodInspection */
        //$this->FirstGenericUserObj = $this->makeUser(['client_id' => $this->ClientObj->id]);
        /** @var User $this ->SecondGenericUserObj */
        /** @noinspection PhpUndefinedMethodInspection */
        //$this->SecondGenericUserObj = $this->makeUser(['client_id' => $this->ClientObj->id]);
        /** @var User $this ->FirstAdminUserObj */
        /** @noinspection PhpUndefinedMethodInspection */
        //$this->FirstAdminUserObj = $this->makeUser(['client_id' => $this->ClientObj->id]);
        //$this->FirstAdminUserObj->attachRole(Role::where('name', Role::CLIENT_ADMINISTRATIVE_USER_ROLE)->first());
        /** @var User $this ->SecondAdminUserObj */
        /** @noinspection PhpUndefinedMethodInspection */
        //$this->SecondAdminUserObj = $this->makeUser(['client_id' => $this->ClientObj->id]);
        //$this->SecondAdminUserObj->attachRole(Role::where('name', Role::CLIENT_ADMINISTRATIVE_USER_ROLE)->first());

        /**
         * check that SOMETHING has occured tot trip $property_group_calc_status since last call to checkAndResetPropertyGroupCalcStatus
         */
        $this->CalculateVariousPropertyListsRepositoryObj->ClientObj = $this->ClientObj;

        $this->CalculateVariousPropertyListsRepositoryObj->need_to_trigger_property_group_calc_status = true;
        $this->CalculateVariousPropertyListsRepositoryObj->trigger_property_group_calc_status();

        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_WAITING);
        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_IDLE);
        $this->clear_the_optimistic_cache_and_reset_some_values();

        $this->SeventhGenericUserObj->refresh();
        $this->FirstAdminUserObj->refresh();

        $this->assertEquals(null, $this->SeventhGenericUserObj->allPropertyGroup->property_id_md5);
        $this->assertEquals(null, $this->SecondGenericUserObj->allPropertyGroup->property_id_md5);
        $this->assertNotEquals(null, $this->FirstAdminUserObj->allPropertyGroup->property_id_md5);
        $this->assertNotEquals(null, $this->SecondAdminUserObj->allPropertyGroup->property_id_md5);

        $this->assertEquals($this->ClientObj->allAccessList->properties->count(), $this->ClientObj->properties->count());

        $this->assertEquals(count($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), 0);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->count(), 0);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), 1);
        $this->assertEquals($this->SeventhGenericUserObj->allPropertyGroup->properties->count(), 0);

        $this->assertEquals(count($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), 0);
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->count(), 0);
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), 1);
        $this->assertEquals($this->SecondGenericUserObj->allPropertyGroup->properties->count(), 0);

        $this->assertEquals(count($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        $this->assertEquals(count($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->SecondAdminUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        $this->assertEquals(null, $this->SeventhGenericUserObj->allPropertyGroup->property_id_md5);
        $this->assertEquals(null, $this->SecondGenericUserObj->allPropertyGroup->property_id_md5);
        $this->assertNotEquals(null, $this->FirstAdminUserObj->allPropertyGroup->property_id_md5);
        $this->assertNotEquals(null, $this->SecondAdminUserObj->allPropertyGroup->property_id_md5);

        if (config('queue.driver', 'sync') == 'sync')
        {
            $this->assertEquals(null, $this->SeventhGenericUserObj->allPropertyGroup->property_id_md5);
            $this->assertEquals(null, $this->SecondGenericUserObj->allPropertyGroup->property_id_md5);
            $this->assertNotEquals(null, $this->FirstAdminUserObj->allPropertyGroup->property_id_md5);
            $this->assertNotEquals(null, $this->SecondAdminUserObj->allPropertyGroup->property_id_md5);
            $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->property_id_md5, $this->SecondAdminUserObj->allPropertyGroup->property_id_md5);
        }

        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_IDLE);
        /****************************************************
         * create an access list
         * FirstAccessListObj
         * SecondAccessListObj
         ****************************************************/
        $this->FirstAccessListObj  = $this->makeAccessList(['client_id' => $this->ClientObj->id]);
        $this->SecondAccessListObj = $this->makeAccessList(['client_id' => $this->ClientObj->id]);

        /**
         * check that NOTHING has occured tot trip $property_group_calc_status since last call to checkAndResetPropertyGroupCalcStatus
         */
        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_IDLE);
        $this->clear_the_optimistic_cache_and_reset_some_values();

        $seventhGenericUser_prev_md5 = null;
        $FirstAdminUser_prev_md5     = null;
        if (config('queue.driver', 'sync') == 'sync')
        {
            /**
             * simply creating access lists should not trigger group calc
             */
            $this->assertEquals(null, $this->SeventhGenericUserObj->allPropertyGroup->property_id_md5);
            $this->assertEquals(null, $this->SecondGenericUserObj->allPropertyGroup->property_id_md5);
            $this->assertNotEquals(null, $this->FirstAdminUserObj->allPropertyGroup->property_id_md5);
            $this->assertNotEquals(null, $this->SecondAdminUserObj->allPropertyGroup->property_id_md5);
            $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->property_id_md5, $this->SecondAdminUserObj->allPropertyGroup->property_id_md5);

            $seventhGenericUser_prev_md5 = $this->SeventhGenericUserObj->allPropertyGroup->property_id_md5;
            $FirstAdminUser_prev_md5     = $this->FirstAdminUserObj->allPropertyGroup->property_id_md5;
        }

        /****************************************************
         * add everyone to $this->FirstAccessList, even admins
         ****************************************************/
        $this->FirstAccessListObj->addUser($this->SeventhGenericUserObj);
        $this->FirstAccessListObj->addUser($this->SecondGenericUserObj);
        $this->FirstAccessListObj->addUser($this->FirstAdminUserObj);
        $this->FirstAccessListObj->addUser($this->SecondAdminUserObj);

        /**
         * check that SONETHING has occured tot trip $property_group_calc_status since last call to checkAndResetPropertyGroupCalcStatus
         */
        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_IDLE);
        $this->clear_the_optimistic_cache_and_reset_some_values();

        if (config('queue.driver', 'sync') == 'sync')
        {
            $this->assertEquals(null, $this->SeventhGenericUserObj->allPropertyGroup->property_id_md5);
            $this->assertEquals(null, $this->SecondGenericUserObj->allPropertyGroup->property_id_md5);
            $this->assertNotEquals(null, $this->FirstAdminUserObj->allPropertyGroup->property_id_md5);
            $this->assertNotEquals(null, $this->SecondAdminUserObj->allPropertyGroup->property_id_md5);
            $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->property_id_md5, $this->SecondAdminUserObj->allPropertyGroup->property_id_md5);

            $this->assertEquals($this->SeventhGenericUserObj->allPropertyGroup->property_id_md5, $seventhGenericUser_prev_md5);
            $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->property_id_md5, $FirstAdminUser_prev_md5);
            $seventhGenericUser_prev_md5 = $this->SeventhGenericUserObj->allPropertyGroup->property_id_md5;
        }

        $this->SeventhGenericUserObj->refresh();
        /*********************************************************************************/
        /*********************************************************************************/
        /**
         * now add properties to $this->FirstAccessList
         */
        $this->FirstAccessListObj->addProperty($this->FirstPropertyObj);
        $this->FirstAccessListObj->addProperty($this->SecondPropertyObj);
        $this->FirstAccessListObj->addProperty($this->ThirdPropertyObj);
        $this->FirstAccessListObj->addProperty($this->FourthPropertyObj);

        /**
         * check that NOTHING has occured tot trip $property_group_calc_status since last call to checkAndResetPropertyGroupCalcStatus
         */

        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_WAITING);
        $this->clear_the_optimistic_cache_and_reset_some_values();

        $this->SeventhGenericUserObj->refresh();
        $this->SecondGenericUserObj->refresh();
        if (config('queue.driver', 'sync') == 'sync')
        {
            $this->assertNotEquals(null, $this->SeventhGenericUserObj->allPropertyGroup->property_id_md5);
            $this->assertNotEquals(null, $this->SecondGenericUserObj->allPropertyGroup->property_id_md5);
            $this->assertNotEquals(null, $this->FirstAdminUserObj->allPropertyGroup->property_id_md5);
            $this->assertNotEquals(null, $this->SecondAdminUserObj->allPropertyGroup->property_id_md5);

            $this->assertEquals($this->SeventhGenericUserObj->allPropertyGroup->property_id_md5, $this->SecondGenericUserObj->allPropertyGroup->property_id_md5);
            $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->property_id_md5, $this->SecondAdminUserObj->allPropertyGroup->property_id_md5);

            $this->assertNotEquals($this->SeventhGenericUserObj->allPropertyGroup->property_id_md5, $seventhGenericUser_prev_md5);
            $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->property_id_md5, $FirstAdminUser_prev_md5);
        }

        /*********************************************************************************
         * now create a property group and add our properties
         */
        $this->NewPropertyGroupObj = $this->makePropertyGroup(
            [
                'user_id'               => $this->SecondGenericUserObj->id,
                'is_all_property_group' => false,
            ]
        );

        /**
         * check that NOTHING has occured tot trip $property_group_calc_status since last call to checkAndResetPropertyGroupCalcStatus
         */
        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_IDLE);
        $this->clear_the_optimistic_cache_and_reset_some_values();

        $this->assertEquals(null, $this->NewPropertyGroupObj->property_id_md5);

        /*********************************************************************************
         * add our properties to our new property group
         */
        $this->NewPropertyGroupObj->addProperty($this->FirstPropertyObj);
        $this->NewPropertyGroupObj->addProperty($this->SecondPropertyObj);
        $this->NewPropertyGroupObj->addProperty($this->ThirdPropertyObj);
        $this->NewPropertyGroupObj->addProperty($this->FourthPropertyObj);
        /**
         * check that SOMETHING has occured tot trip $property_group_calc_status since last call to checkAndResetPropertyGroupCalcStatus
         */
        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_WAITING);
        $this->clear_the_optimistic_cache_and_reset_some_values();

        /**
         * some checks
         */
        $this->FirstAccessListObj->refresh();
        $this->SecondGenericUserObj->refresh();
        $this->SeventhGenericUserObj->refresh();

        $this->assertEquals(count($this->NewPropertyGroupObj->properties), 4);
        $this->assertEquals($this->FirstAccessListObj->properties->count(), 4);
        $this->assertEquals($this->SecondAccessListObj->properties->count(), 0);
        $this->assertEquals($this->FirstAccessListObj->users->count(), 4);
        $this->assertEquals($this->SecondAccessListObj->users->count(), 0);

        $this->assertEquals(count($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), 4);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->count(), 4);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), 3);
        $this->assertEquals($this->SeventhGenericUserObj->allPropertyGroup->properties->count(), 4);

        $this->assertEquals(count($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), 4);
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->count(), 4);
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), 3);
        $this->assertEquals($this->SecondGenericUserObj->allPropertyGroup->properties->count(), 4);

        $this->assertEquals(count($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->SecondAdminUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        /**
         * remove a property from $this->FirstAccessList
         */
        $this->AccessListPropertyRepositoryObj->delete(
            $this->AccessListPropertyRepositoryObj->findWhere(
                [
                    'access_list_id' => $this->FirstAccessListObj->id,
                    'property_id'    => $this->FourthPropertyObj->id,
                ]
            )->first()->id
        );

        /**
         * check that SOMETHING has occured tot trip $property_group_calc_status since last call to checkAndResetPropertyGroupCalcStatus
         */
        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_WAITING);
        $this->clear_the_optimistic_cache_and_reset_some_values();

        $this->assertEquals(count($this->NewPropertyGroupObj->properties), 4);
        $this->assertEquals($this->FirstAccessListObj->properties->count(), 3);
        $this->assertEquals($this->SecondAccessListObj->properties->count(), 0);
        $this->assertEquals($this->FirstAccessListObj->users->count(), 4);
        $this->assertEquals($this->SecondAccessListObj->users->count(), 0);

        /**
         * some checks
         */
        $this->assertEquals(count($this->NewPropertyGroupObj->properties), 4);
        $this->assertEquals($this->FirstAccessListObj->properties->count(), 3);
        $this->assertEquals($this->SecondAccessListObj->properties->count(), 0);
        $this->assertEquals($this->FirstAccessListObj->users->count(), 4);
        $this->assertEquals($this->SecondAccessListObj->users->count(), 0);

        $this->assertEquals(count($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), 3);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->count(), 3);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), 2);
        $this->assertEquals($this->SeventhGenericUserObj->allPropertyGroup->properties->count(), 3);

        $this->assertEquals(count($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), 3);
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->count(), 3);
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), 2);
        $this->assertEquals($this->SecondGenericUserObj->allPropertyGroup->properties->count(), 3);

        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->SecondAdminUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        /**
         * now lets remove $this->SeventhGenericUserObj from $this->FirstAccessList
         */
        $this->AccessListUserRepositoryObj->delete(
            $this->AccessListUserRepositoryObj->findWhere(
                [
                    'access_list_id' => $this->FirstAccessListObj->id,
                    'user_id'        => $this->SeventhGenericUserObj->id,
                ]
            )->first()->id
        );

        /**
         * you may think that this should be PROPERTY_GROUP_CALC_STATUS_WAITING
         * but sinse $this->SeventhGenericUserObj got all his property access via
         * $this->FirstAccessListObj, no recalc was triggered because there.there are no changed
         * PropertyGroups with properties. Remember we do not process empty property groups
         */
        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_WAITING);
        $this->clear_the_optimistic_cache_and_reset_some_values();

        /**
         * some checks
         */
        $this->assertEquals($this->NewPropertyGroupObj->properties->count(), 4);
        $this->assertEquals($this->FirstAccessListObj->properties->count(), 3);
        $this->assertEquals($this->SecondAccessListObj->properties->count(), 0);
        $this->assertEquals($this->FirstAccessListObj->users->count(), 3);
        $this->assertEquals($this->SecondAccessListObj->users->count(), 0);

        $this->assertEquals(count($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), 0);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->count(), 0);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), 1);
        $this->assertEquals($this->SeventhGenericUserObj->allPropertyGroup->properties->count(), 0);

        $this->assertEquals(count($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), 3);
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->count(), 3);
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), 1);
        $this->assertEquals($this->SecondGenericUserObj->allPropertyGroup->properties->count(), 3);

        $this->assertEquals(count($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        $this->assertEquals(count($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->SecondAdminUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        /**
         * now lets add $this->SecondGenericUserObj to the $this->ClientObj all access list
         */
        //$this->makeAccessListUser(['access_list_id' => $this->ClientObj->allAccessList->id, 'user_id' => $this->SecondGenericUserObj->id]);
        $this->ClientObj->allAccessList->addUser($this->SecondGenericUserObj);
        $this->ClientObj->refresh();
        $this->SecondAdminUserObj->refresh();

        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_WAITING);
        $this->clear_the_optimistic_cache_and_reset_some_values();

        /**
         * some checks
         */
        $this->assertEquals($this->NewPropertyGroupObj->properties->count(), 4);
        $this->assertEquals($this->FirstAccessListObj->properties->count(), 3);
        $this->assertEquals($this->SecondAccessListObj->properties->count(), 0);
        $this->assertEquals($this->FirstAccessListObj->users->count(), 3);
        $this->assertEquals($this->SecondAccessListObj->users->count(), 0);

        $this->assertEquals(count($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), 0);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->count(), 0);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), 1);
        $this->assertEquals($this->SeventhGenericUserObj->allPropertyGroup->properties->count(), 0);

        $this->assertEquals(count($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->SecondGenericUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        $this->assertEquals(count($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        $this->assertEquals(count($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->SecondAdminUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        /**
         * now lets make $this->SecondAdminUserObj a generic user
         */
        $this->SecondAdminUserObj->detachRole(Role::where('name', Role::CLIENT_ADMINISTRATIVE_USER_ROLE)->first());
        $this->SecondAdminUserObj->refresh();
        $this->ClientObj->refresh();

        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_WAITING);
        $this->clear_the_optimistic_cache_and_reset_some_values();

        /**
         * some checks
         */
        $this->assertEquals($this->NewPropertyGroupObj->properties->count(), 4);
        $this->assertEquals($this->FirstAccessListObj->properties->count(), 3);
        $this->assertEquals($this->SecondAccessListObj->properties->count(), 0);
        $this->assertEquals($this->FirstAccessListObj->users->count(), 3);
        $this->assertEquals($this->SecondAccessListObj->users->count(), 0);

        $this->assertEquals(count($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), 0);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->count(), 0);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), 1);
        $this->assertEquals($this->SeventhGenericUserObj->allPropertyGroup->properties->count(), 0);

        $this->assertEquals(count($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->SecondGenericUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        $this->assertEquals(count($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        /**
         * remember that SecondAdminUserObj is now a generic user and is on
         * FirstAccessListObj
         */
        $this->assertEquals(count($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->pluck('id')), 3);
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->count(), 3);
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), 1);
        $this->assertEquals($this->SecondAdminUserObj->allPropertyGroup->properties->count(), 3);

        /**
         * now lets give users an empty property group
         */
        $this->FirstEmptyPropertyGroupObj  = $this->makePropertyGroup(
            [
                'client_id'             => $this->ClientObj->id,
                'user_id'               => $this->SeventhGenericUserObj->id,
                'is_all_property_group' => false,
            ]
        );
        $this->SecondEmptyPropertyGroupObj = $this->makePropertyGroup(
            [
                'client_id'             => $this->ClientObj->id,
                'user_id'               => $this->SecondAdminUserObj->id,
                'is_all_property_group' => false,
            ]
        );

        $this->SecondAdminUserObj->refresh();
        $this->ClientObj->refresh();
        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_IDLE);
        $this->clear_the_optimistic_cache_and_reset_some_values();
        $this->reset_some_values_properties();

        /**
         * some checks
         */
        if (config('queue.driver', 'sync') == 'sync')
        {
            $this->assertEquals($this->FirstEmptyPropertyGroupObj->total_square_footage, 0);
            $this->assertEquals($this->SecondEmptyPropertyGroupObj->total_square_footage, 0);
            $this->assertEquals($this->SeventhGenericUserObj->allPropertyGroup->total_square_footage, 0);
            /**
             * @todo fix this HER-1585
             */
            #$this->assertEquals($this->SecondGenericUserObj->allPropertyGroup->total_square_footage, $this->ClientObj->properties->count() * 10000);
            #$this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->total_square_footage, $this->ClientObj->properties->count() * 10000);
            #$this->assertEquals($this->SecondAdminUserObj->allPropertyGroup->total_square_footage, 30000);
        }

        $this->assertEquals(count($this->NewPropertyGroupObj->properties), 4);
        $this->assertEquals(count($this->FirstEmptyPropertyGroupObj->properties), 0);
        $this->assertEquals(count($this->SecondEmptyPropertyGroupObj->properties), 0);

        $this->assertEquals($this->FirstAccessListObj->properties->count(), 3);
        $this->assertEquals($this->SecondAccessListObj->properties->count(), 0);
        $this->assertEquals($this->FirstAccessListObj->users->count(), 3);
        $this->assertEquals($this->SecondAccessListObj->users->count(), 0);

        $this->assertEquals(count($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), 0);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->count(), 0);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), 1);
        $this->assertEquals($this->SeventhGenericUserObj->allPropertyGroup->properties->count(), 0);

        $this->assertEquals(count($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->SecondGenericUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        $this->assertEquals(count($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        $this->assertEquals(count($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->pluck('id')), 3);
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->count(), 3);
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), 1);
        $this->assertEquals($this->SecondAdminUserObj->allPropertyGroup->properties->count(), 3);

        $this->reset_some_values_properties();
        /**
         *
         * @todo fix me See HER-1585
         * reset our objects to clear the optimistic cache
         * now lets remove $this->SecondAdminUserObj from $this->FirstAccessList
         */
        $this->AccessListUserRepositoryObj->findWhere(
            [
                'access_list_id' => $this->FirstAccessListObj->id,
                'user_id'        => $this->SecondAdminUserObj->id,
            ]
        )->first()->delete();

        $this->checkAndResetPropertyGroupCalcStatus(Client::PROPERTY_GROUP_CALC_STATUS_WAITING);
        $this->clear_the_optimistic_cache_and_reset_some_values();

        /**
         * some checks
         */
        $this->assertEquals($this->NewPropertyGroupObj->properties->count(), 4);
        $this->assertEquals($this->FirstEmptyPropertyGroupObj->properties->count(), 0);
        $this->assertEquals($this->SecondEmptyPropertyGroupObj->properties->count(), 0);
        $this->assertEquals($this->FirstAccessListObj->properties->count(), 3);
        $this->assertEquals($this->SecondAccessListObj->properties->count(), 0);
        $this->assertEquals($this->FirstAccessListObj->users->count(), 2);
        $this->assertEquals($this->SecondAccessListObj->users->count(), 0);

        $this->assertEquals(count($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), 0);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyObjArr()->count(), 0);
        $this->assertEquals($this->SeventhGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), 1);
        $this->assertEquals($this->SeventhGenericUserObj->allPropertyGroup->properties->count(), 0);

        $this->assertEquals(count($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->SecondGenericUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->SecondGenericUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        $this->assertEquals(count($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->pluck('id')), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyObjArr()->count(), $this->ClientObj->properties->count());
        $this->assertEquals($this->FirstAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), $this->ClientObj->propertyGroups->count());
        $this->assertEquals($this->FirstAdminUserObj->allPropertyGroup->properties->count(), $this->ClientObj->properties->count());

        $this->assertEquals(count($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->pluck('id')), 0);
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyObjArr()->count(), 0);
        $this->assertEquals($this->SecondAdminUserObj->getAccessiblePropertyGroupObjArr()->count(), 1);
        $this->assertEquals($this->SecondAdminUserObj->allPropertyGroup->properties->count(), 0);
    }

    /**
     * @test
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_reads_access_list_detail()
    {
        /** @var  AccessListDetail $AccessListDetailObj */
        $AccessListDetailObj = $this->FirstAccessListObj;

        /** @var  AccessListDetail $dbAccessListDetailObj */
        $dbAccessListDetailObj = $this->AccessListDetailRepositoryObj->find($AccessListDetailObj->id);

        $this->assertTrue($dbAccessListDetailObj->validate());
        $this->assertTrue(is_array($dbAccessListDetailObj->toArray()['access_list_users']));
        $this->assertTrue(is_array($dbAccessListDetailObj->toArray()['access_list_properties']));

    }

    /**
     * @return Client
     */
    public function getClientObj()
    {
        return $this->ClientObj;
    }

    public function checkAndResetPropertyGroupCalcStatus($property_group_calc_status)
    {
        $this->CalculateVariousPropertyListsRepositoryObj->CalculateVariousPropertyListsJobProcessor($this->ClientObj->id);

        /**
         * get a refreshed $this->ClientObj with n
         */
        $this->ClientObj->refresh();

        if (config('queue.driver', 'sync') == 'sync')
        {
            $this->assertEquals($property_group_calc_status, $this->ClientObj->property_group_calc_status);
        }

        /**
         * reset our objects to clear the optimistic cache
         */
        if ($this->ClientObj)
        {
            $this->ClientObj = $this->ClientRepositoryObj->update(
                [
                    'property_group_calc_status'         => Client::PROPERTY_GROUP_CALC_STATUS_IDLE,
                    'property_group_calc_last_requested' => Carbon::now(),
                ],
                $this->ClientObj->id
            );
            $this->ClientObj->refresh();
        }
        $this->populateUsers();
    }

    /**
     * @todo there must be a better way than this
     *
     * reset all propertys to 10000 sq ft
     * reset property_group_calc_status on client
     */
    protected function clear_the_optimistic_cache_and_reset_some_values()
    {
        $this->populateVariousUnittestObjects();

        $this->ClientObj->refresh();
    }

    /**
     * @todo there must be a better way than this
     *
     * reset all propertys to 10000 sq ft
     * reset property_group_calc_status on client
     */
    protected function reset_some_values_properties()
    {
        if ($this->FirstPropertyObj)
        {
            $this->FirstPropertyObj->square_footage = 10000;
            $this->FirstPropertyObj->save();
            $this->FirstPropertyObj = Property::find($this->FirstPropertyObj->id);
        }
        if ($this->SecondPropertyObj)
        {
            $this->SecondPropertyObj->square_footage = 10000;
            $this->SecondPropertyObj->save();
            $this->SecondPropertyObj = Property::find($this->SecondPropertyObj->id);
        }
        if ($this->ThirdPropertyObj)
        {
            $this->ThirdPropertyObj->square_footage = 10000;
            $this->ThirdPropertyObj->save();
            $this->ThirdPropertyObj = Property::find($this->ThirdPropertyObj->id);
        }
        if ($this->FourthPropertyObj)
        {
            $this->FourthPropertyObj->square_footage = 10000;
            $this->FourthPropertyObj->save();
            $this->FourthPropertyObj = Property::find($this->FourthPropertyObj->id);
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