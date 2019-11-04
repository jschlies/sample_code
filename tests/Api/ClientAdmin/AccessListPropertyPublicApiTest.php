<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\AccessListProperty;
use App;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\Generated\MakeAccessListPropertyTrait;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class AccessListPropertyPublicApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class AccessListPropertyPublicApiTest extends TestCase
{
    use MakeAccessListPropertyTrait, ApiTestTrait;
    use MakeAccessListTrait;

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
    public function it_can_read_access_list_properties()
    {
        /** @var  AccessListProperty $AccessListPropertyObj */
        $AccessListPropertyObj = $this->makeAccessListProperty(
            [
                'access_list_id' => $this->ThirdAccessListObj->id,
                'property_id'    => $this->FourthPropertyObj->id,
            ]
        );
        $this->json(
            'GET',
            '/api/v1/clients/' . $AccessListPropertyObj->accessList->client_id . '/accessList/' . $AccessListPropertyObj->accessList->id . '/accessListProperty'
        );
        $this->assertApiListResponse(AccessListProperty::class);

        $this->assertTrue(count($this->getJSONContent()['data']) > 0);
        $this->assertTrue(count($this->getJSONContent()['data']) <= config('waypoint.unittest_loop'), 'Response element issue');
        foreach ($this->getJSONContent()['data'] as $responseDataElement)
        {
            $AccessListPropertyObj = AccessListProperty::find($responseDataElement['id']);

            $this->assertInstanceOf(Client::class, $AccessListPropertyObj->accessList->client);

            $this->assertInstanceOf(AccessListProperty::class, $AccessListPropertyObj);
            $this->assertInstanceOf(Property::class, $AccessListPropertyObj->property);
        }
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_access_list_property()
    {
        /** @var  AccessListProperty $AccessListPropertyObj */
        $AccessListPropertyObj = $this->makeAccessListProperty(
            [
                'access_list_id' => $this->ThirdAccessListObj->id,
                'property_id'    => $this->FourthPropertyObj->id,
            ]
        );
        $this->json(
            'GET',
            '/api/v1/clients/' . $AccessListPropertyObj->accessList->client_id . '/accessList/' . $AccessListPropertyObj->accessList->id . '/accessListProperty/' . $AccessListPropertyObj->id
        );
        $this->assertApiSuccess();
        $this->assertTrue(is_array($this->getJSONContent()['data']), serialize($this->getJSONContent()));

        $this->json(
            'GET',
            '/api/v1/clients/' . $AccessListPropertyObj->accessList->client_id . '/accessListProperties/' . $AccessListPropertyObj->id . '/audits/'
        );
        $this->assertApiSuccess();

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $AccessListPropertyObj->accessList->client_id . '/accessList/' . $AccessListPropertyObj->accessList->id . '/accessListProperty/' . $AccessListPropertyObj->id
        );
        $this->assertApiSuccess();
    }

    /**
     * @param array $AccessListPropertyFields
     * @return AccessListProperty
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function makeAccessListProperty($accessListPropertyFields = [])
    {
        $theme = $this->fakeAccessListPropertyData($accessListPropertyFields);
        return $this->AccessListPropertyRepositoryObj->create($theme);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
