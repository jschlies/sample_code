<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeAccessListUserTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class AccessListDetailAPITest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class NativeChartAmountAPITest extends TestCase
{
    use MakeAccessListTrait, ApiTestTrait;
    use MakeAccessListUserTrait;
    use MakePropertyTrait;

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
    public function it_can_read_access_native_chart_amounts()
    {
        /** @var Property $PropertyGroupObj */
        $PropertyGroupObj = $this->FirstAdminUserObj->allPropertyGroup;

        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        $ReportTemplateAccountGroupObj =
            $this
                ->ClientObj
                ->reportTemplates
                ->first()
                ->reportTemplateAccountGroups
                ->filter(
                    function (ReportTemplateAccountGroup $ReportTemplateAccountGroupObj)
                    {
                        return ! $ReportTemplateAccountGroupObj->parent_report_template_account_group_id
                               && count($ReportTemplateAccountGroupObj->get_native_account_id_arr());
                    }
                )->first();

        $CalculatedFieldObj = $ReportTemplateAccountGroupObj
            ->reportTemplate
            ->calculatedFields
            ->filter(
                function (CalculatedField $CalculatedFieldObj)
                {
                    return $CalculatedFieldObj->get_native_account_id_arr() &&
                           $CalculatedFieldObj->calculatedFieldEquations->count();
                }
            )->first();
        /**
         * remember that the seeder only populated 2017 and 2018 = See propertySeeder
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyObj->id . '/reportTemplateAccountGroups/' . $ReportTemplateAccountGroupObj->id . '/actualBudgetVarianceTotal?year=2018'
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyObj->id . '/reportTemplateAccountGroups/' . $ReportTemplateAccountGroupObj->id . '/actualBudgetVarianceTotal?year=2018&rank=1'
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' . $PropertyGroupObj->id . '/reportTemplateAccountGroups/' . $ReportTemplateAccountGroupObj->id . '/actualBudgetVarianceTotal?year=2018'
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' . $PropertyGroupObj->id . '/reportTemplateAccountGroups/' . $ReportTemplateAccountGroupObj->id . '/actualBudgetVarianceTotal?year=2018&rank=1'
        );
        $this->assertApiSuccess();

        /********************/
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyObj->id . '/reportTemplateAccountGroups/' . $ReportTemplateAccountGroupObj->id . '/actualBudgetVarianceOverTime?year=2018'
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' . $PropertyGroupObj->id . '/reportTemplateAccountGroups/' . $ReportTemplateAccountGroupObj->id . '/actualBudgetVarianceOverTime?year=2018'
        );
        $this->assertApiSuccess();

        /********************/
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyObj->id . '/calculatedFields/' . $CalculatedFieldObj->id . '/actualBudgetVarianceTotal?year=2018'
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' . $PropertyGroupObj->id . '/calculatedFields/' . $CalculatedFieldObj->id . '/actualBudgetVarianceTotal?year=2017'
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' . $PropertyGroupObj->id . '/calculatedFields/' . $CalculatedFieldObj->id . '/actualBudgetVarianceOverTime?year=2017'
        );
        $this->assertApiSuccess();
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyObj->id . '/calculatedFields/' . $CalculatedFieldObj->id . '/actualBudgetVarianceOverTime?year=2017'
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
