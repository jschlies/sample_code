<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyDetail;
use App;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Exceptions\GeneralException;
use function implode;

/**
 * Class PropertyApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class PropertyDetailApiTest extends TestCase
{
    use MakePropertyTrait, ApiTestTrait;
    use MakeUserTrait;

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
    public function it_can_read_property_list()
    {
        /** @var Property $FirstPropertyObj */
        $FirstPropertyObj = $this->FirstPropertyObj;
        /** @var Property $SecondPropertyObj */
        $SecondPropertyObj = $this->SecondPropertyObj;

        $FirstPropertyObj->client->addUserToAllAccessList($this->getLoggedInUserObj()->id);
        $SecondPropertyObj->client->addUserToAllAccessList($this->getLoggedInUserObj()->id);

        $this->json(
            'GET',
            '/api/v1/clients/' . $FirstPropertyObj->client_id . '/propertyDetails/' . implode(',', [$FirstPropertyObj->id, $SecondPropertyObj->id])
        );
        $this->assertApiListResponse(PropertyDetail::class);
        $this->assertEquals(2, count($this->getDataObjectArr()));
        $this->assertTrue(isset($this->getDataObjectArr()['Property_' . $FirstPropertyObj->id]));
        $this->assertTrue(isset($this->getDataObjectArr()['Property_' . $SecondPropertyObj->id]));

        $this->json(
            'GET',
            '/api/v1/clients/' . $FirstPropertyObj->client_id . '/propertyDetails/' . $FirstPropertyObj->id
        );
        $this->assertApiListResponse(PropertyDetail::class);
        $this->assertEquals(count($this->getDataObjectArr()), 1);
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_property_list()
    {
        /** @var Property $FirstPropertyObj */
        $FirstPropertyObj = $this->FirstPropertyObj;
        /** @var PropertyDetail $SecondPropertyObj */
        $SecondPropertyObj = $this->SecondPropertyObj;

        $NewUserObj = $this->FourthGenericUserObj;

        $this->logInUser($NewUserObj);
        $this->json(
            'GET',
            '/api/v1/clients/' . $FirstPropertyObj->client_id . '/propertyDetails/' . implode(',', [$FirstPropertyObj->id, $SecondPropertyObj->id])
        );
        $this->assertApiFailure();
        $this->json(
            'GET',
            '/api/v1/clients/' . $FirstPropertyObj->client_id . '/propertyDetails/' . $FirstPropertyObj->id
        );
        $this->assertApiFailure();

        $this->ClientObj->addUserToAllAccessList($NewUserObj->id);
        $NewUserObj->refresh();
        $NewUserObj = User::find($NewUserObj->id);

        /**
         * this is needed here since I think Entrust is caching the user obj which has accessabel_properties_arr lazy loaded
         */
        $this->LogOutUser();
        $this->logInUser();
        $this->LogOutUser();
        $this->logInUser($NewUserObj);

        $this->json(
            'GET',
            '/api/v1/clients/' . $FirstPropertyObj->client_id . '/propertyDetails/' . implode(',', [$FirstPropertyObj->id, $SecondPropertyObj->id])
        );
        $this->assertApiSuccess();
        $this->json(
            'GET',
            '/api/v1/clients/' . $FirstPropertyObj->client_id . '/propertyDetails/' . $FirstPropertyObj->id
        );
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
