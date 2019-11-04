<?php

namespace App\Waypoint\Tests;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\Tenant;
use App\Waypoint\Models\TenantAttributeDetail;
use App\Waypoint\Models\TenantDetail;
use App\Waypoint\Models\TenantDetailForPropertyGroups;
use App\Waypoint\Models\TenantIndustryDetail;
use App\Waypoint\Repositories\TenantDetailRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakePropertyGroupTrait;
use App\Waypoint\Tests\Generated\MakeTenantAttributeTrait;
use App\Waypoint\Tests\Generated\MakeTenantTrait;
use App\Waypoint\Tests\Generated\MakeTenantIndustryTrait;
use Carbon\Carbon;
use Exception;

/**
 * Class TenantDetailApiTest
 *
 * @codeCoverageIgnore
 */
class TenantDetailApiTest extends TestCase
{
    use MakeTenantTrait, ApiTestTrait;
    use  MakeTenantAttributeTrait;
    use  MakeTenantIndustryTrait;
    use  MakePropertyGroupTrait;

    /**
     * @var TenantDetailRepository
     * this is needed in MakeTenantTrait
     */
    protected $TenantDetailRepositoryObj;

    public function setUp()
    {
        try
        {
            $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
            parent::setUp();
            $this->TenantDetailRepositoryObj = App::make(TenantDetailRepository::class);
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException($e->getMessage(), 404, $e);
        }
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_tenants()
    {
        $tenant_attribute_arr = $this->fakeTenantAttributeData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantAttributes',
            $tenant_attribute_arr
        );
        $this->assertApiSuccess();

        $tenant_attribute_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantAttributeDetails'
        );
        $this->assertApiListResponse(TenantAttributeDetail::class);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantAttributeDetails/' . $tenant_attribute_id
        );
        $this->assertApiSuccess();

        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantAttributes/' . $tenant_attribute_id,
            [
                'name' => 'foo',
            ]
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantAttributeDetails/' . $tenant_attribute_id
        );
        $this->assertApiSuccess();

        $this->assertEquals('foo', $this->getFirstDataObject()['name']);

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantAttributes/' . $tenant_attribute_id
        );
        $this->assertApiSuccess();

        $tenant_attribute_arr = $this->fakeTenantAttributeData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantAttributes',
            $tenant_attribute_arr
        );
        $this->assertApiSuccess();

        $tenant_attribute_id = $this->getFirstDataObject()['id'];

        $tenant_industry_arr = $this->fakeTenantIndustryData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantIndustries',
            $tenant_industry_arr
        );
        $this->assertApiSuccess();

        $tenant_industry_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantIndustryDetails'
        );
        $this->assertApiListResponse(TenantIndustryDetail::class);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantIndustryDetails/' . $tenant_industry_id
        );
        $this->assertApiSuccess();

        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantIndustries/' . $tenant_industry_id,
            [
                'name' => 'foo',
            ]
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantIndustryDetails/' . $tenant_industry_id
        );
        $this->assertApiSuccess();

        $this->assertEquals('foo', $this->getFirstDataObject()['name']);

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantIndustries/' . $tenant_industry_id
        );
        $this->assertApiSuccess();

        $tenant_industry_arr = $this->fakeTenantIndustryData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantIndustries',
            $tenant_industry_arr
        );
        $this->assertApiSuccess();

        $tenant_industry_id = $this->getFirstDataObject()['id'];

        /**
         * get a property with some leases
         */
        /** @var Property $PropertyObj */
        do
        {
            $PropertyObj = $this->ClientObj->properties->filter(
                function (Property $PropertyObj)
                {
                    return $PropertyObj->getActiveLeaseDetailObjArr()->count();
                }
            )->random();
            $this->assertNotNull($PropertyObj, 'could not find a property with active leases');

        } while ( ! $PropertyObj && $PropertyObj->propertyGroups->count() > 1);

        /** @var PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj = $this->makePropertyGroup();
        $PropertyGroupObj->addProperty($PropertyObj);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $PropertyObj->id . '/tenantDetails'
        );
        $this->assertApiListResponse(TenantDetail::class);
        $this->assertGreaterThan(0, count($this->getJSONContent()['data']));
        $tenant_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' . $PropertyGroupObj->id . '/tenantDetails'
        );
        $this->assertApiListResponse(TenantDetailForPropertyGroups::class);
        $this->assertGreaterThan(0, count($this->getJSONContent()['data']));

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantDetails/' . $tenant_id
        );
        $this->assertApiSuccess();

        /** @var Tenant $TenantObj */
        $TenantObj = Tenant::find($tenant_id);

        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenants/' . $tenant_id,
            [
                'tenant_industry_id' => null,
            ]
        );
        $this->assertApiFailure();

        /**
         * put it back
         */
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenants/' . $tenant_id,
            [
                'tenant_industry_id' => $tenant_industry_id,
            ]
        );
        $this->assertApiSuccess();

        /**
         * try to update something else
         */
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenants/' . $tenant_id,
            [
                'tenant_industry_id' => $tenant_industry_id,
                'name'               => 'foo',
            ]
        );
        $this->assertApiFailure();

        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenants/' . $tenant_id,
            [
                'name' => 'foo',
            ]
        );
        $this->assertApiFailure();

        /**
         * this better fail, cant delete a tenantIndustry that a tenant is pointing at
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantIndustries/' . $tenant_industry_id,
            [
                'tenant_industry_id' => $tenant_industry_id,
            ]
        );
        $this->assertApiFailure();

        /**
         * now test tenantTenantAttributes
         */
        $this->ClientObj->refresh();
        $num_tenant_tenant_attributes = $this->ClientObj->tenantAttributeDetails->count();

        $tenant_attribute_arr = $this->fakeTenantAttributeData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantAttributes',
            $tenant_attribute_arr
        );
        $this->assertApiSuccess();
        $tenant_attributes_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantAttributeDetails'
        );
        $this->assertApiListResponse(TenantAttributeDetail::class);
        $this->assertEquals($num_tenant_tenant_attributes + 1, count($this->getJSONContent()['data']));

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantAttributes/' . $tenant_attributes_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantAttributeDetails'
        );
        $this->assertApiListResponse(TenantAttributeDetail::class);
        $this->assertEquals($num_tenant_tenant_attributes, count($this->getJSONContent()['data']));

        /**
         * tie another tenantTenantAttributes to tenant
         */

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenants/' . $tenant_id . '/tenantAttributeDetails'
        );
        $this->assertApiListResponse(TenantAttributeDetail::class);
        $num_tenant_tenant_attributes = count($this->getJSONContent()['data']);

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenants/' . $tenant_id . '/tenantTenantAttributes',
            [
                'tenant_id'           => $tenant_id,
                'tenant_attribute_id' => $tenant_attribute_id,
            ]
        );
        $this->assertApiSuccess();
        $tenant_tenant_attributes_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenants/' . $tenant_id . '/tenantAttributeDetails'
        );
        $this->assertApiListResponse(TenantAttributeDetail::class);
        $this->assertEquals($num_tenant_tenant_attributes + 1, count($this->getJSONContent()['data']));

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenants/' . $tenant_id . '/tenantTenantAttributes/' . $tenant_tenant_attributes_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenants/' . $tenant_id . '/tenantAttributeDetails'
        );
        $this->assertApiListResponse(TenantAttributeDetail::class);
        $this->assertEquals($num_tenant_tenant_attributes, count($this->getJSONContent()['data']));

        /**
         * test eff date
         */
        $LeasesObjArr = $TenantObj->leases;
        $PropertyGroupObj->addProperty($PropertyObj);

        /**
         * make sure all leases are active
         */
        foreach ($LeasesObjArr as $LeasesObj)
        {
            $LeasesObj->lease_start_date      = Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '-5 months')->format('Y-m-d H:i:s');
            $LeasesObj->lease_expiration_date = Seeder::getFakerObj()->dateTimeBetween($startDate = '+5 months', $endDate = '+30 months')->format('Y-m-d H:i:s');
            $LeasesObj->save();
        }

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $PropertyObj->id . '/tenantDetails?lease_as_of_date=' . Carbon::now()->format('m-d-Y')
        );
        $this->assertApiListResponse(TenantDetail::class);

        $num_tenant_tenant_attributes_property = count($this->getJSONContent()['data']);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' . $PropertyGroupObj->id . '/tenantDetails?lease_as_of_date=' . Carbon::now()->format('m-d-Y')
        );
        $this->assertApiListResponse(TenantDetailForPropertyGroups::class);
        $num_tenant_tenant_attributes_property_group = count($this->getJSONContent()['data']);

        /**
         * make sure all leases are expired
         */
        foreach ($LeasesObjArr as $LeasesObj)
        {
            $LeasesObj->lease_start_date      = Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '-5 months')->format('Y-m-d H:i:s');
            $LeasesObj->lease_expiration_date = Seeder::getFakerObj()->dateTimeBetween($startDate = '-4 months', $endDate = '-1 months')->format('Y-m-d H:i:s');
            $LeasesObj->save();
        }

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $PropertyObj->id . '/tenantDetails?lease_as_of_date=' . Carbon::now()->format('m-d-Y')
        );
        $this->assertApiListResponse(TenantDetail::class);
        $this->assertEquals($num_tenant_tenant_attributes_property - 1, count($this->getJSONContent()['data']));

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' . $PropertyGroupObj->id . '/tenantDetails?lease_as_of_date=' . Carbon::now()->format('m-d-Y')
        );
        $this->assertApiListResponse(TenantDetailForPropertyGroups::class);
        $this->assertEquals($num_tenant_tenant_attributes_property_group - 1, count($this->getJSONContent()['data']));

        /**
         * make sure all leases are active
         */
        foreach ($LeasesObjArr as $LeasesObj)
        {
            $LeasesObj->lease_start_date      = Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '-5 months')->format('Y-m-d H:i:s');
            $LeasesObj->lease_expiration_date = Seeder::getFakerObj()->dateTimeBetween($startDate = '+5 months', $endDate = '+30 months')->format('Y-m-d H:i:s');
            $LeasesObj->save();
        }

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $PropertyObj->id . '/tenantDetails?lease_as_of_date=' . Carbon::now()->format('m-d-Y')
        );
        $this->assertApiListResponse(TenantDetail::class);
        $this->assertEquals($num_tenant_tenant_attributes_property, count($this->getJSONContent()['data']));

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' . $PropertyGroupObj->id . '/tenantDetails?lease_as_of_date=' . Carbon::now()->format('m-d-Y')
        );
        $this->assertApiListResponse(TenantDetailForPropertyGroups::class);
        $this->assertEquals($num_tenant_tenant_attributes_property_group, count($this->getJSONContent()['data']));

    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_tenants()
    {
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/tenantDetails/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        unset($this->TenantDetailRepositoryObj);
        parent::tearDown();
    }
}
