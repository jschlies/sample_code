<?php

namespace App\Waypoint\Tests\Api\ApiKey;

use App\Waypoint\CurlServiceTrait;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Models\User;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\ApiTestTrait;

/**
 * Class BridgeApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 *
 */
class BridgeApiTest extends TestCase
{
    use ApiTestTrait;
    use MakeUserTrait;
    use MakePropertyTrait;
    use CurlServiceTrait;

    /** @var ApiKey */
    protected $ApiKeyObj;

    /** @var User */
    protected $UserObj;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        parent::setUp();

        /**
         * @todo I know, I know, there is no 'logged in user' for apiKey routes
         */
        /** @var User $UserObj */
        $this->UserObj = $this->getLoggedInUserObj();
        if ( ! $this->UserObj->apiKey)
        {
            ApiKey::make($this->UserObj->id);
        }
        /** @var User $User2Obj */
        $this->ApiKeyObj = $this->getLoggedInUserObj()->apiKey;
    }

    /**
     * @test
     */
    public function it_can_read_clients_json()
    {
        $this->json(
            'GET',
            '/api/v1/waypointMasterBridge/clients',
            [],
            [
                'Content-Type'    => 'application/json',
                'X-Authorization' => $this->ApiKeyObj->key,
            ]
        );
        $this->assertApiSuccess();

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            $this->json(
                'GET',
                "/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients",
                [],
                [
                    'Content-Type'    => 'application/json',
                    'X-Authorization' => $this->ApiKeyObj->key,
                ]
            );
            $this->assertApiSuccess();
            $this->assertTrue(count($this->getJSONContent()['data']) > 0);
        }
    }

    /**
     * @test
     */
    public function it_can_read_clients_boma_coa_codes_json()
    {
        $this->json(
            'GET',
            '/api/v1/waypointMasterBridge/clients/boma_coa_codes',
            [],
            [
                'Content-Type'    => 'application/json',
                'X-Authorization' => $this->ApiKeyObj->key,
            ]
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getJSONContent()['data']) > 0);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            $this->json(
                'GET',
                "/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/boma_coa_codes",
                [],
                [
                    'Content-Type'    => 'application/json',
                    'X-Authorization' => $this->ApiKeyObj->key,
                ]
            );
            $this->assertApiSuccess();
            $this->assertTrue(count($this->getJSONContent()['data']) > 0);
        }
    }

    /**
     * @test
     */
    public function it_can_read_clients_client_sftp_details_json()
    {
        $this->json(
            'GET',
            '/api/v1/waypointMasterBridge/clients/client_sftp_details',
            [],
            [
                'Content-Type'    => 'application/json',
                'X-Authorization' => $this->ApiKeyObj->key,
            ]
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getJSONContent()['data']) > 0);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            $this->json(
                'GET',
                "/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/client_sftp_details",
                [],
                [
                    'Content-Type'    => 'application/json',
                    'X-Authorization' => $this->ApiKeyObj->key,
                ]
            );
            $this->assertApiSuccess();
            $this->assertTrue(count($this->getJSONContent()['data']) > 0);
        }
    }

    /**
     * @test
     */
    public function it_can_read_clients_wp_asset_type_json()
    {
        $this->json(
            'GET',
            '/api/v1/waypointMasterBridge/clients/wp_asset_type',
            [],
            [
                'Content-Type'    => 'application/json',
                'X-Authorization' => $this->ApiKeyObj->key,
            ]
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getJSONContent()['data']) > 0);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            $this->json(
                'GET',
                "/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/wp_asset_type",
                [],
                [
                    'Content-Type'    => 'application/json',
                    'X-Authorization' => $this->ApiKeyObj->key,
                ]
            );
            $this->assertApiSuccess();
            $this->assertTrue(count($this->getJSONContent()['data']) > 0);
        }
    }

    /**
     * @test
     */
    public function it_can_read_clients_version_metadata_json()
    {
        $this->json(
            'GET',
            '/api/v1/waypointMasterBridge/clients/version_metadata',
            [],
            [
                'Content-Type'    => 'application/json',
                'X-Authorization' => $this->ApiKeyObj->key,
            ]
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getJSONContent()['data']) > 0);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            {
                $this->json(
                    'GET',
                    "/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/version_metadata",
                    [],
                    [
                        'Content-Type'    => 'application/json',
                        'X-Authorization' => $this->ApiKeyObj->key,
                    ]
                );
                $this->assertApiSuccess();
                $this->assertTrue(count($this->getJSONContent()['data']) > 0);
            }
        }
    }

    /**
     * @test
     */
    public function it_can_read_clients_column_datatypes_json()
    {
        $this->json(
            'GET',
            '/api/v1/waypointMasterBridge/clients/column_datatypes',
            [],
            [
                'Content-Type'    => 'application/json',
                'X-Authorization' => $this->ApiKeyObj->key,
            ]
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getJSONContent()['data']) > 0);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {

            $this->json(
                'GET',
                "/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/column_datatypes",
                [],
                [
                    'Content-Type'    => 'application/json',
                    'X-Authorization' => $this->ApiKeyObj->key,
                ]
            );
            $this->assertApiSuccess();
            $this->assertTrue(count($this->getJSONContent()['data']) > 0);
        }
    }

    /**
     */
    public function it_can_read_waypoint_boma_coa_mapping_json()
    {
        /*****
         * READ ME - LOOK AT THIS - I MEAN YOU - Yea YOU
         * if your unit test is failing here, it's because your data, Waypoint Commercial or Waypoint Commercial I'll bet,
         * had a native code that is not mapped to a report_template_account_group
         * READ ME - LOOK AT THIS - I MEAN YOU - Yea YOU
         * @test
         */
        $this->json(
            'GET',
            '/api/v1/waypointMasterBridge/clients/' . $this->ClientObj->client_id_old . '/waypoint_boma_coa_mapping',
            [],
            [
                'Content-Type'    => 'application/json',
                'X-Authorization' => $this->ApiKeyObj->key,
            ]
        );
        $this->assertApiSuccess();

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            $this->json(
                'GET',
                "/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE .
                "/clients/" . $this->ClientObj->client_id_old . "/waypoint_boma_coa_mapping",
                [],
                [
                    'Content-Type'    => 'application/json',
                    'X-Authorization' => $this->ApiKeyObj->key,
                ]
            );
            $this->assertApiSuccess();
        }
    }

    /**
     * @test
     */
    public function it_can_read_index_waypoint_account_codes_json()
    {
        $this->json(
            'GET',
            '/api/v1/waypointMasterBridge/clients/' . $this->ClientObj->client_id_old . '/waypoint_account_codes',
            [],
            [
                'Content-Type'    => 'application/json',
                'X-Authorization' => $this->ApiKeyObj->key,
            ]
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getJSONContent()['data']) > 0);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {

            $this->json(
                'GET',
                "/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE .
                "/clients/" . $this->ClientObj->client_id_old . "/waypoint_account_codes",
                [],
                [
                    'Content-Type'    => 'application/json',
                    'X-Authorization' => $this->ApiKeyObj->key,
                ]
            );
            $this->assertApiSuccess();
            $this->assertTrue(count($this->getJSONContent()['data']) > 0);
        }
    }

    /**
     * @test
     */
    public function it_can_read_index_occupancy_lease_type_d()
    {
        $this->json(
            'GET',
            '/api/v1/waypointMasterBridge/clients/occupancy_lease_type_d',
            [],
            [
                'Content-Type'    => 'application/json',
                'X-Authorization' => $this->ApiKeyObj->key,
            ]
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getJSONContent()['data']) > 0);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {

            $this->json(
                'GET',
                "/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/occupancy_lease_type_d",
                [],
                [
                    'Content-Type'    => 'application/json',
                    'X-Authorization' => $this->ApiKeyObj->key,
                ]
            );
            $this->assertApiSuccess();
            $this->assertTrue(count($this->getJSONContent()['data']) > 0);
        }
    }

    /**
     * @test
     */
    public function it_can_read_map()
    {
        $this->json(
            'GET',
            '/api/v1/waypointMasterBridge/clients/map',
            [],
            [
                'Content-Type'    => 'application/json',
                'X-Authorization' => $this->ApiKeyObj->key,
            ]
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getJSONContent()['data']) > 0);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {

            $this->json(
                'GET',
                "/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/map",
                [],
                [
                    'Content-Type'    => 'application/json',
                    'X-Authorization' => $this->ApiKeyObj->key,
                ]
            );
            $this->assertApiSuccess();
            $this->assertTrue(count($this->getJSONContent()['data']) > 0);
        }
    }

    /**
     * @test
     */
    public function it_can_read_reportTemplates()
    {
        $this->json(
            'GET',
            '/api/v1/waypointMasterBridge/clients/' . $this->ClientObj->client_id_old . '/reportTemplates',
            [],
            [
                'Content-Type'    => 'application/json',
                'X-Authorization' => $this->ApiKeyObj->key,
            ]
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getJSONContent()['data']) > 0);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {

            $this->json(
                'GET',
                "/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/" . $this->ClientObj->client_id_old . "/reportTemplates",
                [],
                [
                    'Content-Type'    => 'application/json',
                    'X-Authorization' => $this->ApiKeyObj->key,
                ]
            );
            $this->assertApiSuccess();
            $this->assertTrue(count($this->getJSONContent()['data']) > 0);
        }
    }

    /**
     * @test
     */
    public function it_can_read_reportTemplate_native_coa()
    {
        foreach ($this->ClientObj->reportTemplates as $ReportTemplateObj)
        {
            // @todo See HER_2781
            if ($ReportTemplateObj->is_boma_report_template)
            {
                continue;
            }
            $this->json(
                'GET',
                '/api/v1/waypointMasterBridge/clients/' . $this->ClientObj->client_id_old . '/reportTemplates/' . $ReportTemplateObj->id . '/nativeCoas',
                [],
                [
                    'Content-Type'    => 'application/json',
                    'X-Authorization' => $this->ApiKeyObj->key,
                ]
            );
            $this->assertApiSuccess();
            $this->assertTrue(count($this->getJSONContent()['data']) > 0);
        }

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {

            foreach ($this->ClientObj->reportTemplates as $ReportTemplateObj)
            {
                // @todo See HER_2781
                if ($ReportTemplateObj->is_boma_report_template)
                {
                    continue;
                }
                $this->json(
                    'GET',
                    "/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/" . $this->ClientObj->client_id_old . "/reportTemplates/" . $ReportTemplateObj->id . "/nativeCoas",
                    [],
                    [
                        'Content-Type'    => 'application/json',
                        'X-Authorization' => $this->ApiKeyObj->key,
                    ]
                );
                $this->assertApiSuccess();
                $this->assertTrue(count($this->getJSONContent()['data']) > 0);
            }
        }
    }

    /**
     * @test
     */
    public function it_can_read_reportTemplate_native_detail()
    {
        foreach ($this->ClientObj->reportTemplates as $ReportTemplateObj)
        {
            $this->json(
                'GET',
                '/api/v1/waypointMasterBridge/clients/' . $this->ClientObj->client_id_old . '/reportTemplates/' . $ReportTemplateObj->id . '/detail',
                [],
                [
                    'Content-Type'    => 'application/json',
                    'X-Authorization' => $this->ApiKeyObj->key,
                ]
            );
            $this->assertApiSuccess();
            $this->assertTrue(count($this->getJSONContent()['data']) > 0);
        }

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {

            foreach ($this->ClientObj->reportTemplates as $ReportTemplateObj)
            {
                $this->json(
                    'GET',
                    "/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/" . $this->ClientObj->client_id_old . "/reportTemplates/" . $ReportTemplateObj->id . "/detail",
                    [],
                    [
                        'Content-Type'    => 'application/json',
                        'X-Authorization' => $this->ApiKeyObj->key,
                    ]
                );
                $this->assertApiSuccess();
                $this->assertTrue(count($this->getJSONContent()['data']) > 0);
                $this->generateMock();
            }
        }
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        unset($this->ApiKeyObj);
        unset($this->UserObj);
        parent::tearDown();
    }
}
