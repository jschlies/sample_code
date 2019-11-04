<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Models\Property;
use App;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Exceptions\GeneralException;
use function mt_rand;

/**
 * Class PropertyApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class PropertyApiTest extends TestCase
{
    use MakePropertyTrait, ApiTestTrait;

    /**
     * @var PropertyRepository
     * this is needed in MakePropertyTrait
     */
    protected $PropertyRepositoryObj;

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
    public function it_can_create_and_read_properties_list()
    {
        /**
         * Create an access List
         */
        $property_arr = $this->fakePropertyData();
        $this->json(
            'POST',
            'api/v1/clients/' . $property_arr['client_id'] . '/properties',
            $property_arr
        );
        $this->assertApiSuccess();
        $property_id = $this->getFirstDataObject()['id'];
        $PropertyObj = $this->PropertyRepositoryObj->find($property_id);

        $this->json(
            'GET',
            '/api/v1/clients/' . $PropertyObj->client_id . '/properties?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertApiListResponse(Property::class);

        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $editedProperty_arr */
        $editedProperty_arr = $this->fakePropertyData([], Seeder::DEFAULT_FACTORY_NAME);

        $this->json(
            'PUT',
            '/api/v1/clients/' . $PropertyObj->client_id . '/properties/' . $PropertyObj->id, $editedProperty_arr);
        $this->assertApiSuccess();
    }

    /**
     * @test
     */
    public function it_can_create_and_read_custom_attributes()
    {
        /**
         * Create an property
         */
        $property_arr = $this->fakePropertyData();
        $this->json(
            'POST', 'api/v1/clients/' . $property_arr['client_id'] . '/properties',
            $property_arr
        );
        $this->assertApiSuccess();
        $property_id = $this->getFirstDataObject()['id'];
        $PropertyObj = $this->PropertyRepositoryObj->find($property_id);

        $this->assertTrue($PropertyObj->propertyNativeCoas()->count() == 1);
        /**
         * add some customAttributes
         */
        $this->json(
            'POST',
            'api/v1/clients/' . $property_arr['client_id'] . '/properties/' . $PropertyObj->id . '/customAttributes',
            [
                'attribute_name'  => 'fee',
                'attribute_value' => mt_rand(),
            ]
        );
        $this->assertApiSuccess();

        $this->json(
            'POST',
            'api/v1/clients/' . $property_arr['client_id'] . '/properties/' . $PropertyObj->id . '/customAttributes',
            [
                'attribute_name'  => 'fie',
                'attribute_value' => mt_rand(),
            ]
        );
        $this->assertApiSuccess();

        $this->json(
            'POST', 'api/v1/clients/' . $property_arr['client_id'] . '/properties/' . $PropertyObj->id . '/customAttributes',
            [
                'attribute_name'  => 'foo',
                'attribute_value' => mt_rand(),
            ]
        );
        $this->assertApiSuccess();

        $this->json(
            'POST', 'api/v1/clients/' . $property_arr['client_id'] . '/properties/' . $PropertyObj->id . '/customAttributes',
            [
                'attribute_name'  => 'fum',
                'attribute_value' => mt_rand(),
            ]
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     */
    public function it_cannot_update_non_existing_properties()
    {
        /** @var  array $editedProperty_arr */
        $editedProperty_arr = $this->fakePropertyData([], Seeder::DEFAULT_FACTORY_NAME);
        /** @var  Property $propertyObj */
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . mt_rand(),
            $editedProperty_arr
        );
        $this->assertApiFailure([400, 500]);
    }

    /**
     * @test
     */
    public function it_cannot_native_coa_id()
    {
        $property_arr                  = $this->fakePropertyData();
        $property_arr['native_coa_id'] = 99999999999;
        $this->json(
            'POST', 'api/v1/clients/' . $property_arr['client_id'] . '/properties',
            $property_arr
        );
        $this->assertApiFailure([400, 500]);
    }

    /**
     * @test
     */
    public function it_can_native_coa_id()
    {
        $property_arr                  = $this->fakePropertyData();
        $property_arr['native_coa_id'] = $this->ClientObj->nativeCoas->first()->id;
        $this->json(
            'POST', 'api/v1/clients/' . $property_arr['client_id'] . '/properties',
            $property_arr
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     */
    public function it_can_default_native_coa_id()
    {
        $property_arr = $this->fakePropertyData();
        $this->json(
            'POST', 'api/v1/clients/' . $property_arr['client_id'] . '/properties',
            $property_arr
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
