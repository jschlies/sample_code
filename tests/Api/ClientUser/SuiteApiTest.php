<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeSuiteTrait;
use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Tests\TestCase;

/**
 * Class SuiteApiBaseTest
 *
 * @codeCoverageIgnore
 */
class SuiteApiTest extends TestCase
{
    use MakeSuiteTrait, ApiTestTrait;
    use MakeAccessListTrait;

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
     */
    public function it_can_create_suites()
    {
        /** @var  array $suites_arr */
        $SuiteObj = $this->FirstPropertyObj->suites->random();

        $this->ClientObj->addUserToAllAccessList($this->getLoggedInUserObj()->id);

        /** @var App\Waypoint\Models\PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj = $SuiteObj->property->client->propertyGroups->first();

        foreach ($this->ClientObj->properties as $PropertyObj)
        {
            $PropertyGroupObj->addProperty($PropertyObj);
        }

        $this->json(
            'GET',
            '/api/v1/clients/' . $SuiteObj->property->client_id . '/properties/' . $SuiteObj->property_id . '/suiteDetails/' . $SuiteObj->id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $SuiteObj->property->client_id . '/properties/' . $SuiteObj->property_id . '/suiteDetails'
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $SuiteObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id . '/suiteDetails'
        );
        $this->assertApiSuccess();

        $PropertyGroupObj->addProperty($SuiteObj->property);
        $this->json(
            'GET',
            '/api/v1/clients/' . $SuiteObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id . '/suiteDetails'
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
