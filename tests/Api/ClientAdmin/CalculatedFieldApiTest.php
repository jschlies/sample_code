<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\Role;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\CalculatedFieldRepository;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeCalculatedFieldEquationTrait;
use App\Waypoint\Tests\Generated\MakeCalculatedFieldTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\Generated\MakeReportTemplateMappingTrait;
use App\Waypoint\Tests\TestCase;

/**
 * remember you cannot 'use App\Waypoint\Models\Role here as it messes with Role unit tests
 */

/**
 * Class CalculatedFieldApiBaseTest
 *
 * @codeCoverageIgnore
 */
class CalculatedFieldApiTest extends TestCase
{
    use MakeCalculatedFieldTrait, ApiTestTrait;
    use MakeCalculatedFieldEquationTrait;
    use MakeReportTemplateMappingTrait;
    use MakePropertyTrait;

    /**
     * @var CalculatedFieldRepository
     * this is needed in MakeCalculatedFieldTrait
     */
    protected $CalculatedFieldRepositoryObj;
    /**
     * @var CalculatedFieldRepository
     * this is needed in MakeCalculatedFieldTrait
     */
    protected $ReportTemplateMappingRepositoryObj;

    /** @var  AdvancedVarianceRepository */
    protected $AdvancedVarianceRepositoryObj;

    /** @var  PropertyRepository */
    protected $PropertyRepositoryObj;

    /** @var AdvancedVariance */
    protected $FirstPropertyFirstAdvancedVarianceObj;

    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
        parent::setUp();
        $this->FirstPropertyFirstAdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->getAdvancedVariancesWithClientId($this->ClientObj->id)[0];
        $this->FirstPropertyFirstAdvancedVarianceObj->fresh();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_calculated_fields()
    {
        $report_template_id = $this->FirstPropertyFirstAdvancedVarianceObj->report_template_id;

        $num_orig_calculated_fields = $this->FirstPropertyFirstAdvancedVarianceObj->reportTemplate->calculatedFields->count();

        $ReportTemplateMappingObj = null;

        /** @var App\Waypoint\Models\ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
        foreach ($this->FirstPropertyFirstAdvancedVarianceObj->reportTemplate->reportTemplateAccountGroups as $ReportTemplateAccountGroupObj)
        {
            foreach ($ReportTemplateAccountGroupObj->reportTemplateMappings as $ReportTemplateMappingObj)
            {
                break 2;
            }
        }

        /**
         * create it
         */
        /** @var  array $calculated_field_arr */
        $calculated_field_arr                    = $this->fakeCalculatedFieldData(['report_template_id' => $report_template_id]);
        $calculated_field_arr['equation_string'] = '[NA_' . $ReportTemplateMappingObj->native_account_id . '] + ' . mt_rand(1,
                                                                                                                            99999) . ' * [RTAG_' . $ReportTemplateMappingObj->report_template_account_group_id . ']';

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_id . '/calculatedFields',
            $calculated_field_arr
        );
        $this->assertApiSuccess();
        $calculated_field_id = $this->getFirstDataObject()['id'];
        $this->assertEquals($report_template_id, $this->getFirstDataObject()['report_template_id']);

        /**
         * create second
         */
        $calculated_field_arr                    = $this->fakeCalculatedFieldData(['report_template_id' => $report_template_id]);
        $calculated_field_arr['equation_string'] = '[NA_' . $ReportTemplateMappingObj->native_account_id . '] + ' . mt_rand(1,
                                                                                                                            99999) . ' * [RTAG_' . $ReportTemplateMappingObj->report_template_account_group_id . ']';

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_id . '/calculatedFields',
            $calculated_field_arr
        );
        $this->assertApiSuccess();
        $second_calculated_field_id = $this->getFirstDataObject()['id'];
        $this->assertEquals($report_template_id, $this->getFirstDataObject()['report_template_id']);

        /**
         * is it there???
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_id . '/calculatedFields/' . $calculated_field_id
        );
        $this->assertApiSuccess();
        $this->assertEquals($calculated_field_id, $this->getFirstDataObject()['id']);
        $this->assertEquals($report_template_id, $this->getFirstDataObject()['report_template_id']);

        /**
         * Are they both there?
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id .
            '/reportTemplates/' . $report_template_id . '/calculatedFields/' .
            implode(
                ','
                ,
                [
                    $calculated_field_id,
                    $second_calculated_field_id,
                ]
            )
        );
        $this->assertApiSuccess();
        $this->assertEquals(2, count($this->JSONContent['data']));

        /**
         * Are they all there?
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_id . '/calculatedFields/'
        );
        $this->assertApiSuccess();
        $this->assertEquals(2 + $num_orig_calculated_fields, count($this->JSONContent['data']));

        /**
         * now delete the thing we just created, $second_calculated_field_id
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_id . '/calculatedFields/' . $second_calculated_field_id
        );
        $this->assertApiSuccess();

        /**
         * is the first still there???
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_id . '/calculatedFields'
        );
        $this->assertApiSuccess();
        $this->assertEquals(1 + $num_orig_calculated_fields, count($this->JSONContent['data']));

        /**
         * edit it
         */
        /** @var  array $edited_calculated_fields_arr */
        $edited_calculated_fields_arr = $this->fakeCalculatedFieldData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_id . '/calculatedFields/' . $calculated_field_id,
            $edited_calculated_fields_arr
        );
        $this->assertApiSuccess();
        $this->assertEquals($edited_calculated_fields_arr['name'], $this->JSONContent['data']['name']);

        /**
         * are all equations there
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_id . '/calculatedFields/' . $calculated_field_id . '/calculatedFieldEquations'
        );
        $this->assertApiSuccess();
        $this->assertEquals(1, count($this->JSONContent['data']));

        $calculated_field_equation_arr = $this->fakeCalculatedFieldEquationData(['calculated_field_id' => $calculated_field_id], Seeder::DEFAULT_FACTORY_NAME);

        $calculated_field_equation_arr['equation_string']
            = '[NA_' . $ReportTemplateMappingObj->native_account_id . '] + ' . mt_rand(1, 99999) . ' * [RTAG_' . $ReportTemplateMappingObj->report_template_account_group_id . ']';

        $calculated_field_equation_arr['property_id'] = $this->ClientObj->properties->first()->id;

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_id . '/calculatedFields/' . $calculated_field_id . '/calculatedFieldEquations',
            $calculated_field_equation_arr
        );
        $this->assertApiSuccess();
        $calculated_field_equation_with_properties_id = $this->JSONContent['data']['id'];
        /**
         * now add another equation for this property - should fail
         */
        $calculated_field_equation_arr
            = $this->fakeCalculatedFieldEquationData(['calculated_field_id' => $calculated_field_id], Seeder::DEFAULT_FACTORY_NAME);

        $calculated_field_equation_arr['equation_string']
            = '[NA_' . $ReportTemplateMappingObj->native_account_id . '] + ' . mt_rand(1, 99999) . ' * [RTAG_' . $ReportTemplateMappingObj->report_template_account_group_id . ']';

        $calculated_field_equation_arr['property_id'] = $this->ClientObj->properties->first()->id;
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_id . '/calculatedFields/' . $calculated_field_id . '/calculatedFieldEquations',
            $calculated_field_equation_arr
        );
        $this->assertApiFailure();

        /**
         * are all equations there again
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_id . '/calculatedFields/' . $calculated_field_id . '/calculatedFieldEquations'
        );
        $this->assertApiSuccess();
        $this->assertEquals(2, count($this->JSONContent['data']));

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id .
            '/reportTemplates/' . $report_template_id . '/calculatedFields/' . $calculated_field_id .
            '/calculatedFieldEquations/' . $calculated_field_equation_with_properties_id . '/calculatedFieldEquationProperties'
        );
        $this->assertApiSuccess();
        $this->assertEquals(1, count($this->JSONContent['data']));

        /**
         * add second property to equatiion
         */
        $PropertyObj = $this->SeventhPropertyObj;
        $PropertyObj->save();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/reportTemplates/' . $report_template_id . '/calculatedFields/' . $calculated_field_id . '/calculatedFieldEquations/' . $calculated_field_equation_with_properties_id . '/calculatedFieldEquationProperties',
            [
                'property_id' => $PropertyObj->id,
            ]
        );
        $this->assertApiSuccess();

        $calculated_field_equation_property_id = $this->JSONContent['data']['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id .
            '/reportTemplates/' . $report_template_id . '/calculatedFields/' . $calculated_field_id .
            '/calculatedFieldEquations/' . $calculated_field_equation_with_properties_id . '/calculatedFieldEquationProperties'
        );
        $this->assertApiSuccess();
        $this->assertEquals(2, count($this->JSONContent['data']));

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id .
            '/reportTemplates/' . $report_template_id . '/calculatedFields/' . $calculated_field_id .
            '/calculatedFieldEquations/' . $calculated_field_equation_with_properties_id . '/calculatedFieldEquationProperties/' . $calculated_field_equation_property_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id .
            '/reportTemplates/' . $report_template_id . '/calculatedFields/' . $calculated_field_id .
            '/calculatedFieldEquations/' . $calculated_field_equation_with_properties_id . '/calculatedFieldEquationProperties'
        );
        $this->assertApiSuccess();
        $this->assertEquals(1, count($this->JSONContent['data']));
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
