<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App\Waypoint\Models\PropertyGroupProperty;
use App;
use App\Waypoint\Tests\Generated\MakePropertyGroupPropertyTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Models\Role;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class PropertyGroupPropertyApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class PropertyGroupPropertyApiTest extends TestCase
{
    use MakePropertyGroupPropertyTrait, ApiTestTrait;

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
     */
    public function it_can_delete_property_group_properties_1()
    {
        /**
         * @todo WOW - need much BETTER COVERAGE HERE See HER-2019
         */
        /**
         * NOTE NOTE NOTE
         * $this->makePropertyGroupProperty() (actually the seeder) adds the logged in user to allccessList for client
         * thus we need to create $propertyGroupPropertyObj before we create $UserThatHasAccessToNothingObj
         */
        /** @var  PropertyGroupProperty $propertyGroupPropertyObj */
        $propertyGroupPropertyObj = $this->makePropertyGroupProperty();

        $UserThatHasAccessToNothingObj = $this->SixthGenericUserObj;
        $this->logInUser($UserThatHasAccessToNothingObj);

        $this->assertEquals(0, count($UserThatHasAccessToNothingObj->getAccessiblePropertyObjArr()->pluck('id')->toArray()));
        $this->assertEquals(0, count($this->getLoggedInUserObj()->getAccessiblePropertyObjArr()->pluck('id')->toArray()));
        $this->assertEquals($UserThatHasAccessToNothingObj->id, $this->getLoggedInUserObj()->id);
        $this->assertEquals(1, count($UserThatHasAccessToNothingObj->getRoleNamesArr()));
        $this->assertEquals(1, count($this->getLoggedInUserObj()->getRoleNamesArr()));

        $x_www_form_urlencoded = [
            'property_id'       => $propertyGroupPropertyObj->property_id,
            'property_group_id' => $propertyGroupPropertyObj->property_group_id,
        ];
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroupProperty/',
            $x_www_form_urlencoded
        );
        /**
         * should fail - access issue
         */
        $this->assertApiFailure();

        $UserThatHasAccessToEverythingObj = $this->FifthGenericUserObj;
        $this->ClientObj->addUserToAllAccessList($UserThatHasAccessToEverythingObj->id);

        $this->logInUser($UserThatHasAccessToEverythingObj);
        $this->assertTrue(count($UserThatHasAccessToEverythingObj->getAccessiblePropertyObjArr()->pluck('id')->toArray()) > 0);
        $this->assertTrue(count($this->getLoggedInUserObj()->getAccessiblePropertyObjArr()->pluck('id')->toArray()) > 0);
        $this->assertEquals($UserThatHasAccessToEverythingObj->id, $this->getLoggedInUserObj()->id);
        $this->assertEquals(1, count($UserThatHasAccessToEverythingObj->getRoleNamesArr()));
        $this->assertEquals(1, count($this->getLoggedInUserObj()->getRoleNamesArr()));

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroupProperty/',
            $x_www_form_urlencoded
        );
        $this->assertApiSuccess();

        // Can no longer GET or DELETE the PropertyGroupProperty
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroupProperty/' . $propertyGroupPropertyObj->id);
        $this->assertAPIFailure();

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroupProperty',
            $x_www_form_urlencoded
        );
        $this->assertAPIFailure();
    }

    /**
     * @test
     */
    public function it_can_delete_property_group_properties_2()
    {
        /**
         * NOTE NOTE NOTE
         * $this->makePropertyGroupProperty() (actually the seeder) adds the logged in user to allccessList for client
         * thus we need to create $propertyGroupPropertyObj before we create $UserThatHasAccessToNothingObj
         */
        /** @var  PropertyGroupProperty $propertyGroupPropertyObj */
        $propertyGroupPropertyObj = $this->makePropertyGroupProperty();

        $UserThatHasAccessToNothingObj = $this->SeventhGenericUserObj;

        $this->LogOutUser();
        $this->logInUser($UserThatHasAccessToNothingObj);

        $this->assertEquals(0, count($UserThatHasAccessToNothingObj->getAccessiblePropertyObjArr()->pluck('id')->toArray()));
        $this->assertEquals(0, count($this->getLoggedInUserObj()->getAccessiblePropertyObjArr()->pluck('id')->toArray()));
        $this->assertEquals($UserThatHasAccessToNothingObj->id, $this->getLoggedInUserObj()->id);

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroupProperty/' . $propertyGroupPropertyObj->id
        );
        /**
         * should fail - access issue
         */
        $this->assertApiFailure();

        $UserThatHasAccessToEverythingObj = $this->FourthGenericUserObj;
        $this->ClientObj->addUserToAllAccessList($UserThatHasAccessToEverythingObj->id);
        $this->assertTrue(count($UserThatHasAccessToEverythingObj->getAccessiblePropertyObjArr()->pluck('id')->toArray()) > 0);

        $this->logInUser($UserThatHasAccessToEverythingObj);
        $this->assertTrue(count($UserThatHasAccessToEverythingObj->getAccessiblePropertyObjArr()->pluck('id')->toArray()) > 0);
        $this->assertTrue(count($this->getLoggedInUserObj()->getAccessiblePropertyObjArr()->pluck('id')->toArray()) > 0);
        $this->assertEquals($UserThatHasAccessToEverythingObj->id, $this->getLoggedInUserObj()->id);

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroupProperty/' . $propertyGroupPropertyObj->id
        );
        $this->assertApiSuccess();

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroupProperty/' . $propertyGroupPropertyObj->id
        );
        $this->assertAPIFailure();

        /**
         * Can no longer GET or DELETE the PropertyGroupProperty
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroupProperty/' . $propertyGroupPropertyObj->id
        );
        $this->assertAPIFailure();
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
