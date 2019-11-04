<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertySummary;
use App\Waypoint\Models\Role;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\Generated\MakeRelatedUserTypeTrait;
use App\Waypoint\Tests\TestCase;
use function is_array;

/**
 * Class PropertySummaryApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class PropertySummaryApiTest extends TestCase
{
    use MakePropertyTrait, ApiTestTrait;
    use MakeRelatedUserTypeTrait;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        parent::setUp();
        $this->ClientObj->addUserToAllAccessList($this->getLoggedInUserObj()->id);
    }

    /**
     * @test
     */
    public function it_can_read_property_summary()
    {
        /** @var  PropertySummary $PropertySummaryObj */
        $PropertySummaryObj = $this->makePropertySummary(['client_id' => $this->ClientObj->id]);

        if ( ! $this->getLoggedInUserObj()->canAccessProperty($PropertySummaryObj->id))
        {
            $this->AccessListUserRepositoryObj->create(
                [
                    'access_list_id' => $this->ClientObj->allAccessList->id,
                    'user_id'        => $this->getLoggedInUserObj()->id,
                ]
            );
        }

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertiesSummary/' . $PropertySummaryObj->id
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     */
    public function it_can_update_standard_attributes()
    {
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/standardAttributes'
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/standardAttributeUniqueValues'
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     */
    public function it_can_update_custom_attributes()
    {
        $this->json('GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/properties/customAttributeUniqueValues');
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getDataObjectArr()) >= 4);

        /** @var  PropertySummary $PropertySummaryObj */
        $property_summary_arr = $this->fakePropertyData(['client_id' => $this->ClientObj->id]);
        $PropertySummaryObj   = $this->PropertySummaryRepositoryObj->create($property_summary_arr);

        $fake_attr_1  = Seeder::getFakerObj()->word;
        $fake_attr_2  = Seeder::getFakerObj()->word;
        $fake_value_1 = Seeder::getFakerObj()->words(2, true);

        $this->json(
            'POST',
            'api/v1/clients/' . $PropertySummaryObj->client_id . '/properties/' . $PropertySummaryObj->id . '/customAttributes',
            [
                'attribute_name'  => $fake_attr_1,
                'attribute_value' => $fake_value_1,
            ]
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertiesSummary/' . $PropertySummaryObj->id
        );
        $this->assertApiSuccess();
        $this->assertTrue(is_array($this->getFirstDataObject()['custom_attributes']));

        $this->assertTrue(isset($this->getFirstDataObject()['custom_attributes'][$fake_attr_1]));
        $this->assertEquals($this->getFirstDataObject()['custom_attributes'][$fake_attr_1], $fake_value_1);

        $this->json(
            'POST',
            'api/v1/clients/' . $PropertySummaryObj->client_id . '/properties/' . $PropertySummaryObj->id . '/customAttributes',
            [
                'attribute_name'  => $fake_attr_1,
                'attribute_value' => null,
            ]
        );

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertiesSummary/' . $PropertySummaryObj->id
        );
        $this->assertTrue(is_array($this->getFirstDataObject()['custom_attributes']));
        $this->assertFalse(isset($this->getFirstDataObject()['custom_attributes'][$fake_attr_1]));

        $this->json(
            'POST',
            'api/v1/clients/' . $PropertySummaryObj->client_id . '/properties/' . $PropertySummaryObj->id . '/customAttributes',
            [
                'attribute_name'  => $fake_attr_2,
                'attribute_value' => null,
            ]
        );

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertiesSummary/' . $PropertySummaryObj->id
        );
        $this->assertApiSuccess();
        $this->assertTrue(is_array($this->getFirstDataObject()['custom_attributes']));
        $this->assertFalse(isset($this->getFirstDataObject()['custom_attributes'][$fake_attr_2]));
    }

    /**
     * @test
     */
    public function it_can_read_related_user_types()
    {
        $this->fakeRelatedUserType(
            [
                'client_id'           => $this->ClientObj->id,
                'related_object_type' => Property::class,
            ]
        )->save();
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/relatedUserTypes'
        );

        $this->assertApiSuccess();
        $this->assertTrue(count($this->getDataObjectArr()) > 0);
    }

    /**
     * @param array $PropertiesSummaryFields
     * @return Property
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function makePropertySummary($PropertiesSummaryFields = [])
    {
        $theme = $this->fakePropertyData($PropertiesSummaryFields);
        return $this->PropertySummaryRepositoryObj->create($theme);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
