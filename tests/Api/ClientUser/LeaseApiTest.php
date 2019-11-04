<?php

namespace App\Waypoint\Tests\Api\ClientUser;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\LeaseDetail;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\PropertyLeaseRollup;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakePropertyGroupTrait;
use App\Waypoint\Tests\Generated\MakeSuiteLeaseTrait;
use App\Waypoint\Tests\Generated\MakeTenantTrait;
use App\Waypoint\Tests\TestCase;
use Carbon\Carbon;

/**
 * Class LeaseApiBaseTest
 *
 * @codeCoverageIgnore
 */
class LeaseApiTest extends TestCase
{
    use MakeSuiteLeaseTrait, ApiTestTrait;
    use MakePropertyGroupTrait;
    use MakeAccessListTrait;
    use MakeTenantTrait;

    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_can_create_leases()
    {
        /** @var  array $leases_arr */
        $SuiteLeaseObj = $this->fakeSuiteLease()->save();
        /** @var PropertyGroup $PropertyGroupObj */
        $PropertyGroupObj = $this->fakePropertyGroup(['client_id' => $this->ClientObj->id])->save();

        $tenant_attrs              = $this->fakeTenantData();
        $tenant_attrs['client_id'] = $this->ClientObj->id;
        $TenantObj                 = $this->TenantRepositoryObj->create($tenant_attrs);

        $this->TenantTenantAttributeRepositoryObj->create(
            [
                'tenant_attribute_id' => $this->ClientObj->tenantAttributes->random()->id,
                'tenant_id'           => $TenantObj->id,
            ]
        );

        $this->LeaseTenantRepositoryObj->create(
            [
                'lease_id'  => $SuiteLeaseObj->lease->id,
                'tenant_id' => $TenantObj->id,
            ]
        );

        $SuiteLeaseObj->refresh();
        $leases_id = $SuiteLeaseObj->lease_id;

        /** @var Lease $LeaseObj */
        $LeaseObj = $SuiteLeaseObj->lease;

        $PropertyGroupObj->addProperty($LeaseObj->property);
        $PropertyGroupObj->fresh();

        $AccessListObj = $this->makeAccessList();
        $this->AccessListUserRepositoryObj->create(
            [
                'user_id'        => $this->getLoggedInUserObj()->id,
                'access_list_id' => $AccessListObj->id,
            ]
        );
        /**
         * make $AccessListObj all property
         */
        foreach ($this->ClientObj->properties as $PropertyObj)
        {
            $AccessListObj->addProperty($PropertyObj);
        }
        /**
         * make $PropertyGroupObj all property
         */
        foreach ($this->ClientObj->properties as $PropertyObj)
        {
            $PropertyGroupObj->addProperty($PropertyObj);
        }

        $PropertyObj = $LeaseObj->property;
        /************************************/

        /**
         * now lets gather some interesting dates
         *
         * @var Carbon $ClientMinLeaseStartDateObj
         */
        $ClientLeasesObjArr   = $this->ClientObj->properties->map(
            function (Property $PropertyObj)
            {
                return $PropertyObj->leases;
            }
        )->flatten();
        $PropertyLeasesObjArr = $PropertyObj->leases;

        $PropertyGroupLeasesObjArr = $PropertyGroupObj->properties->map(
            function (Property $PropertyObj)
            {
                return $PropertyObj->leases;
            }
        )->flatten();

        /** @noinspection PhpUnusedLocalVariableInspection */
        $num_client_leases         = $ClientLeasesObjArr->count();
        $num_property_group_leases = $PropertyGroupLeasesObjArr->count();
        $num_property_leases       = $PropertyLeasesObjArr->count();

        $ClientMinLeaseStartDateObj = $ClientLeasesObjArr->min('lease_start_date');
        $ClientMaxLeaseStartDateObj = $ClientLeasesObjArr->max('lease_expiration_date');

        $PropertyMinLeaseStartDateObj = $PropertyLeasesObjArr->min('lease_start_date');
        $PropertyMaxLeaseStartDateObj = $PropertyLeasesObjArr->max('lease_expiration_date');

        $ClientMinLeaseStartDateMinusTwoMonthsObj = $ClientMinLeaseStartDateObj->copy()->subMonth(2);
        /** @noinspection PhpUnusedLocalVariableInspection */
        $ClientMaxLeaseStartDatePlusTwoMonthsObj = $ClientMaxLeaseStartDateObj->copy()->addMonth(2);

        $PropertyMinLeaseStartDateMinusTwoMonthsObj = $PropertyMinLeaseStartDateObj->copy()->subMonth(2);
        $PropertyMaxLeaseStartDatePlusTwoMonthsObj  = $PropertyMaxLeaseStartDateObj->copy()->addMonth(2);

        $PropertyGroupMinLeaseStartDateObj = $PropertyGroupLeasesObjArr->min('lease_start_date');
        $PropertyGroupMaxLeaseStartDateObj = $PropertyGroupLeasesObjArr->max('lease_expiration_date');

        $PropertyGroupMinLeaseStartDateMinusTwoMonthsObj = $PropertyGroupMinLeaseStartDateObj->copy()->subMonth(2);
        $PropertyGroupMaxLeaseStartDatePlusTwoMonthsObj  = $PropertyGroupMaxLeaseStartDateObj->copy()->addMonth(2);

        $Year1900ExpDateObj = Carbon::create(1900, 1, 1, 0, 0, 0);
        $Year2100ExpDateObj = Carbon::create(2100, 12, 31, 0, 0, 0);

        /************************************/
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/properties/' . $LeaseObj->property_id . '/leaseDetails/' . $leases_id
        );
        $this->assertApiSuccess();
        $this->assertGreaterThan(0, count($this->getDataObjectArr()));

        $LeaseObj->property->square_footage = 1000000;
        $LeaseObj->property->save();

        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/properties/' . $LeaseObj->property_id . '/leaseDetails'
        );
        $this->assertApiSuccess();
        $this->assertEquals($num_property_leases, count($this->getDataObjectArr()));

        $StartDateObj = $PropertyMinLeaseStartDateMinusTwoMonthsObj;
        $EndDateObj   = $PropertyGroupMaxLeaseStartDatePlusTwoMonthsObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/properties/' . $PropertyObj->id .
            '/leaseDetails?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class);
        $this->assertEquals($num_property_leases, count($this->getDataObjectArr()));

        $StartDateObj = $Year1900ExpDateObj;
        $EndDateObj   = $PropertyGroupMaxLeaseStartDatePlusTwoMonthsObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/properties/' . $PropertyObj->id .
            '/leaseDetails?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class);
        $this->assertEquals($num_property_leases, count($this->getDataObjectArr()));

        $StartDateObj = $PropertyMinLeaseStartDateMinusTwoMonthsObj;
        $EndDateObj   = $Year2100ExpDateObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/properties/' . $PropertyObj->id .
            '/leaseDetails?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class);
        $this->assertEquals($num_property_leases, count($this->getDataObjectArr()));

        $StartDateObj = $PropertyGroupMaxLeaseStartDatePlusTwoMonthsObj;
        $EndDateObj   = $Year2100ExpDateObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/properties/' . $PropertyObj->id .
            '/leaseDetails?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class, 0, 0);
        $this->assertEquals(0, count($this->getDataObjectArr()));

        $StartDateObj = $Year1900ExpDateObj;
        $EndDateObj   = $PropertyMaxLeaseStartDatePlusTwoMonthsObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/properties/' . $PropertyObj->id .
            '/leaseDetails?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class, 0, 0);
        $this->assertEquals($num_property_leases, count($this->getDataObjectArr()));

        /*******************************/

        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/properties/' . $PropertyObj->id . '/leaseDetails/active'
        );
        $this->assertApiListResponse(LeaseDetail::class);
        $this->assertGreaterThan(0, count($this->getDataObjectArr()));

        if ($LeaseObj->lease_expiration_date)
        {
            $this->assertGreaterThan(0, $this->getFirstDataObject()['lease_term']);
        }

        $StartDateObj = $PropertyMinLeaseStartDateMinusTwoMonthsObj;
        $EndDateObj   = $PropertyGroupMaxLeaseStartDatePlusTwoMonthsObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/properties/' . $PropertyObj->id .
            '/leaseDetails/active?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class);
        $this->assertEquals($num_property_leases, count($this->getDataObjectArr()));

        $StartDateObj = $Year1900ExpDateObj;
        $EndDateObj   = $Year2100ExpDateObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/properties/' . $PropertyObj->id .
            '/leaseDetails/active?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class);
        $this->assertEquals($num_property_leases, count($this->getDataObjectArr()));

        $StartDateObj = $Year1900ExpDateObj;
        $EndDateObj   = $PropertyMinLeaseStartDateMinusTwoMonthsObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/properties/' . $PropertyObj->id .
            '/leaseDetails/active?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class, 0, 0);
        $this->assertEquals(0, count($this->getDataObjectArr()));

        /***************************/

        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id . '/leaseDetails'
        );
        $this->assertApiListResponse(LeaseDetail::class);
        $this->assertEquals($num_property_group_leases, count($this->getDataObjectArr()));

        $StartDateObj = $Year1900ExpDateObj;
        $EndDateObj   = $Year2100ExpDateObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id .
            '/leaseDetails?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class, 0, 0);
        $this->assertEquals($num_property_group_leases, count($this->getDataObjectArr()));

        $StartDateObj = $Year1900ExpDateObj;
        $EndDateObj   = $PropertyGroupMinLeaseStartDateMinusTwoMonthsObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id .
            '/leaseDetails?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class, 0, 0);
        $this->assertEquals(0, count($this->getDataObjectArr()));

        /*****************************************/

        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id . '/leaseDetails/active'
        );
        $this->assertApiListResponse(LeaseDetail::class);
        $this->assertEquals($num_property_group_leases, count($this->getDataObjectArr()));

        $StartDateObj = $PropertyGroupMinLeaseStartDateMinusTwoMonthsObj;
        $EndDateObj   = $PropertyGroupMaxLeaseStartDatePlusTwoMonthsObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id .
            '/leaseDetails/active?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class);
        $this->assertEquals($num_property_group_leases, count($this->getDataObjectArr()));

        $StartDateObj = $PropertyGroupMaxLeaseStartDatePlusTwoMonthsObj;
        $EndDateObj   = $Year2100ExpDateObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id .
            '/leaseDetails/active?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class, 0, 0);
        $this->assertEquals(0, count($this->getDataObjectArr()));

        $StartDateObj = $Year1900ExpDateObj;
        $EndDateObj   = $Year2100ExpDateObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id .
            '/leaseDetails/active?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class);
        $this->assertEquals($num_property_group_leases, count($this->getDataObjectArr()));

        /*****************************/

        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/propertyLeaseRollups'
        );
        $this->assertApiListResponse(PropertyLeaseRollup::class);
        $this->assertEquals($this->ClientObj->properties->count(), count($this->getDataObjectArr()));
        $total_leases = 0;
        foreach ($this->getDataObjectArr() as $property_lease_rollup_arr)
        {
            $total_leases += $property_lease_rollup_arr['active_num_leases'];
        }
        //$this->assertEquals($num_client_leases, $total_leases);

        $AsOfDateObj = $ClientMinLeaseStartDateMinusTwoMonthsObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id .
            '/propertyLeaseRollups?as_of_date=' . $AsOfDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(PropertyLeaseRollup::class);
        $this->assertEquals($this->ClientObj->properties->count(), count($this->getDataObjectArr()));
        $total_leases = 0;
        foreach ($this->getDataObjectArr() as $property_lease_rollup_arr)
        {
            $total_leases += $property_lease_rollup_arr['active_num_leases'];
        }
        $this->assertEquals(0, $total_leases);

        $AsOfDateObj = $PropertyGroupMinLeaseStartDateObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id .
            '/propertyLeaseRollups?as_of_date=' . $AsOfDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(PropertyLeaseRollup::class);
        $this->assertEquals($this->ClientObj->properties->count(), count($this->getDataObjectArr()));
        $total_leases = 0;
        foreach ($this->getDataObjectArr() as $property_lease_rollup_arr)
        {
            $total_leases += $property_lease_rollup_arr['active_num_leases'];
        }
        //$this->assertGreaterThan(0, $total_leases);

        $AsOfDateObj = $Year2100ExpDateObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id .
            '/propertyLeaseRollups?as_of_date=' . $AsOfDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(PropertyLeaseRollup::class);
        $this->assertEquals($this->ClientObj->properties->count(), count($this->getDataObjectArr()));
        $total_leases = 0;
        foreach ($this->getDataObjectArr() as $property_lease_rollup_arr)
        {
            $total_leases += $property_lease_rollup_arr['active_num_leases'];
        }
        $this->assertEquals(0, $total_leases);

        /*****************************/

        $this->json(
            'GET',
            '/api/v1/ClientUser/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id . '/propertyLeaseRollups'
        );
        $this->assertApiListResponse(PropertyLeaseRollup::class);
        $this->assertEquals($PropertyGroupObj->properties->count(), count($this->getDataObjectArr()));
        $total_leases = 0;
        foreach ($this->getDataObjectArr() as $property_lease_rollup_arr)
        {
            $total_leases += $property_lease_rollup_arr['active_num_leases'];
        }
        //$this->assertEquals($num_property_group_leases, $total_leases);

        $AsOfDateObj = $Year2100ExpDateObj;
        $this->json(
            'GET',
            '/api/v1/ClientUser/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id .
            '/propertyLeaseRollups?as_of_date=' . $AsOfDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(PropertyLeaseRollup::class);
        $this->assertEquals($PropertyGroupObj->properties->count(), count($this->getDataObjectArr()));
        $total_leases = 0;
        foreach ($this->getDataObjectArr() as $property_lease_rollup_arr)
        {
            $total_leases += $property_lease_rollup_arr['active_num_leases'];
        }
        $this->assertEquals(0, $total_leases);

        $AsOfDateObj = $PropertyGroupMinLeaseStartDateObj;
        $this->json(
            'GET',
            '/api/v1/ClientUser/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id .
            '/propertyLeaseRollups?as_of_date=' . $AsOfDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(PropertyLeaseRollup::class);
        $this->assertEquals($PropertyGroupObj->properties->count(), count($this->getDataObjectArr()));
        $total_leases = 0;
        foreach ($this->getDataObjectArr() as $property_lease_rollup_arr)
        {
            $total_leases += $property_lease_rollup_arr['active_num_leases'];
        }
        //$this->assertGreaterThan(0, $total_leases);

        /*****************************/

        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id . '/leaseDetails'
        );
        $this->assertApiListResponse(LeaseDetail::class);
        $this->assertEquals($num_property_group_leases, count($this->getDataObjectArr()));

        $StartDateObj = $Year1900ExpDateObj;
        $EndDateObj   = $Year2100ExpDateObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id .
            '/leaseDetails?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiListResponse(LeaseDetail::class, 0, 0);
        $this->assertEquals($num_property_group_leases, count($this->getDataObjectArr()));

        $StartDateObj = $Year1900ExpDateObj;
        $EndDateObj   = $PropertyGroupMinLeaseStartDateMinusTwoMonthsObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id .
            '/leaseDetails?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiSuccess();
        $this->assertEquals(0, count($this->getDataObjectArr()));

        $StartDateObj = $PropertyGroupMaxLeaseStartDatePlusTwoMonthsObj;
        $EndDateObj   = $Year2100ExpDateObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id .
            '/leaseDetails?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiSuccess();
        $this->assertEquals(0, count($this->getDataObjectArr()));

        $StartDateObj = $ClientMinLeaseStartDateObj;
        $EndDateObj   = $Year2100ExpDateObj;
        $this->json(
            'GET',
            '/api/v1/clients/' . $LeaseObj->property->client_id . '/propertyGroups/' . $PropertyGroupObj->id .
            '/leaseDetails?from_date=' . $StartDateObj->format('m-d-Y') . '&to_date=' . $EndDateObj->format('m-d-Y')
        );
        $this->assertApiSuccess();
        $this->assertGreaterThan(0, count($this->getDataObjectArr()));
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

}
