<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\EcmProject;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\Spreadsheet;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeEcmProjectTrait;
use App\Waypoint\Tests\Generated\MakePropertyGroupTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;
use function implode;

/**
 * Class EcmProjectsApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class EcmProjectsApiTest extends TestCase
{
    use MakeEcmProjectTrait, ApiTestTrait, MakePropertyGroupTrait, MakeAccessListTrait;
    use MakeUserTrait;

    protected $Spreadsheet;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        parent::setUp();
        $this->ClientObj->updateConfig('FEATURE_PROJECTS', true);

        /**
         * this test presumes LoggedInUser has full access to properties
         */
        $this->ClientObj->addUserToAllAccessList($this->getLoggedInUserObj()->id);
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \Exception
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function ecm_project_data_is_correct_format_for_property_spreadsheets()
    {
        $this->ClientObj->addUserToAllAccessList($this->getLoggedInUserObj()->id);
        /** @var  EcmProject $EcmProjectObj */
        $EcmProjectObj  = $this->makeEcmProject();
        $EcmProject2Obj = $this->makeEcmProject();
        /** @var Spreadsheet $SpreadsheetObj */
        $SpreadsheetObj = App::make(Spreadsheet::class);

        /** @var AccessList $AccessListObj */
        $AccessListObj = $this->makeAccessList(['client_id' => $EcmProjectObj->property->client_id]);
        $AccessListObj->addProperty($EcmProjectObj->property);
        $AccessListObj->addProperty($EcmProject2Obj->property);
        $AccessListObj->addUser($this->getLoggedInUserObj());
        $this->ClientObj->removeUserToAllAccessList($this->getLoggedInUserObj()->id);
        $ecm_projects_ids = implode(',', [$EcmProjectObj->property_id, $EcmProject2Obj->property_id]);

        $this->json(
            'GET',
            '/api/v1/clients/' . $EcmProjectObj->property->client_id . '/properties/' . $ecm_projects_ids . '/ecmProjects' . '?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertApiSuccess();

        // are the two projects present
        $this->assertTrue(isset($this->getJSONContent()['data'][$EcmProjectObj->property_id]));
        $this->assertTrue(isset($this->getJSONContent()['data'][$EcmProject2Obj->property_id]));

        // asset the data in the project response include the necessary spreadsheet columns
        $SpreadsheetObj->setEcmProjectSpreadsheetFormat();
        $project_response_data_array = $this->getJSONContent()['data'][$EcmProjectObj->property_id];
        foreach (current($project_response_data_array) as $key => $array)
        {
            $this->assertTrue(in_array($key, $SpreadsheetObj->columnsToHide) || array_key_exists($key, $SpreadsheetObj->columnTitles));
        }
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_create_ecm_projects()
    {
        $ecm_project_data_arr = $this->fakeEcmProjectData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $ecm_project_data_arr['property_id'] . '/ecmProjects',
            $ecm_project_data_arr
        );
        $this->assertApiSuccess();
        $ecm_project_id  = $this->getFirstDataObject()['id'];
        $ecm_property_id = $this->getFirstDataObject()['property_id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $ecm_property_id . '/ecmProjects/' . $ecm_project_id
        );
        $this->assertApiSuccess();

        $ecm_project_data_arr = $this->fakeEcmProjectData();
        unset($ecm_project_data_arr['property_id']);
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $ecm_property_id . '/ecmProjects/' . $ecm_project_id,
            $ecm_project_data_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $ecm_property_id . '/ecmProjects/' . $ecm_project_id
        );
        $this->assertApiSuccess();

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $ecm_property_id . '/ecmProjects/' . $ecm_project_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/ecmProjects/' . $ecm_project_id
        );
        $this->assertApiFailure();
    }

    /**
     * @test
     */
    public function it_cannot_create_ecm_projects_with_bad_name()
    {
        /** @var Client $ClientObj */
        $ClientObj = $this->getLoggedInUserObj()->client;
        $ClientObj->updateConfig('FEATURE_PROJECTS', true);
        $ecm_project_data_arr         = $this->fakeEcmProjectData();
        $ecm_project_data_arr['name'] = 'a';
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/ecmProjects',
            $ecm_project_data_arr
        );
        $this->assertApiFailure();
    }

    /**
     * @test
     */
    public function it_cannot_create_ecm_projects_with_bad_project_summary()
    {
        $ecm_project_data_arr                    = $this->fakeEcmProjectData();
        $ecm_project_data_arr['project_summary'] = 'a';
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/ecmProjects',
            $ecm_project_data_arr
        );
        $this->assertApiFailure();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_ecm_project_categories()
    {
        $this->json(
            'GET',
            '/api/v1/ecmProjects/available/ProjectCategories'
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_ecm_project_statuses()
    {
        $this->json(
            'GET',
            '/api/v1/ecmProjects/available/ProjectStatuses'
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_ecm_energy_units()
    {
        $this->json('GET', '/api/v1/ecmProjects/available/EnergyUnits');
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_ecm_projects_by_property()
    {
        /** @var  EcmProject $EcmProjectObj */
        $EcmProjectObj = $this->makeEcmProject();
        do
        {
            $EcmProject2Obj = $this->makeEcmProject();
        } while ($EcmProject2Obj->property_id == $EcmProjectObj->property_id);

        do
        {
            $EcmProject3Obj = $this->makeEcmProject();
        } while (
            $EcmProjectObj->property_id == $EcmProject2Obj->property_id ||
            $EcmProjectObj->property_id == $EcmProject3Obj->property_id ||
            $EcmProject2Obj->property_id == $EcmProject3Obj->property_id
        );

        /** @var AccessList $AccessListObj */
        $AccessListObj = $this->makeAccessList(['client_id' => $EcmProjectObj->property->client_id]);
        $AccessListObj->addProperty($EcmProjectObj->property);
        $AccessListObj->addProperty($EcmProject2Obj->property);

        $UserObj = $this->SixthGenericUserObj;
        $AccessListObj->addUser($UserObj);
        $AccessListObj->refresh();

        $this->LogOutUser();
        $this->logInUser($UserObj);

        $this->json(
            'GET',
            '/api/v1/clients/' . $EcmProjectObj->property->client_id . '/properties/' .
            implode(',', [$EcmProjectObj->property_id, $EcmProject2Obj->property_id, $EcmProject3Obj->property_id]) .
            '/ecmProjects' . '?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertApiFailure();

        $UserObj = $this->SecondGenericUserObj;
        $AccessListObj->addUser($UserObj);
        $this->LogOutUser();
        $this->logInUser($UserObj);
        $UserObj->refresh();

        $this->json(
            'GET',
            '/api/v1/clients/' . $EcmProjectObj->property->client_id . '/properties/' .
            implode(',', [$EcmProjectObj->property_id, $EcmProject2Obj->property_id]) .
            '/ecmProjects' . '?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertApiSuccess();

        $this->assertTrue(isset($this->getJSONContent()['data'][$EcmProjectObj->property_id]));
        $this->assertTrue(isset($this->getJSONContent()['data'][$EcmProject2Obj->property_id]));

        /** @var  App\Waypoint\Models\PropertyGroup $PropertyGroup1Obj */
        $PropertyGroup1Obj = $this->makePropertyGroup();
        $PropertyGroup3Obj = $this->makePropertyGroup();
        $PropertyGroup1Obj->addProperty($EcmProjectObj->property);
        $PropertyGroup1Obj->addProperty($EcmProject2Obj->property);
        $PropertyGroup3Obj->addProperty($EcmProject3Obj->property);

        $this->json(
            'GET', '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' .
                   implode(',', [$PropertyGroup1Obj->id, $PropertyGroup3Obj->id]) .
                   '/ecmProjects' . '?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertApiFailure();

        $this->json(
            'GET', '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' .
                   implode(',', [$PropertyGroup1Obj->id]) .
                   '/ecmProjects' . '?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertApiSuccess();
    }

    /**
     * @param array $EcmProjectFields
     * @return EcmProject
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function makeEcmProject($EcmProjectFields = [])
    {
        $theme = $this->fakeEcmProjectData($EcmProjectFields);
        return $this->EcmProjectRepositoryObj->create($theme);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
