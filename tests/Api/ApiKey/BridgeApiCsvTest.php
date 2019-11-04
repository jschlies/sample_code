<?php

namespace App\Waypoint\Tests\Api\ApiKey;

use App\Waypoint\CurlServiceTrait;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Models\User;
use App\Waypoint\Models\ApiKey;

/**
 * Class BridgeApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 *
 */
class BridgeApiCsvTest extends TestCase
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
        if ( ! $this->getLoggedInUserObj()->apiKey)
        {
            ApiKey::make($this->getLoggedInUserObj()->id);
        }
        $this->LoggedInUserObj = $this->getLoggedInUserObj();
        $this->ApiKeyObj       = $this->getLoggedInUserObj()->apiKey;

        $this->assertEquals(ApiKey::class, get_class($this->ApiKeyObj));
        $this->assertEquals(User::class, get_class($this->getLoggedInUserObj()));
    }

    /**
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_cannot_read_clients_csv_with_bad_apikey()
    {
        /**
         * NOTE NOTE
         * This is REALLY a api call thus you cannot debug
         */
        $response = $this->getCurlServiceObj()
                         ->to('http://localhost/api/v1/waypointMasterBridge/clients')
                         ->returnResponseObject()
                         ->withHeader('X-Authorization: ' . mt_rand())
                         ->get();

        $this->assertEquals(401, $response->status);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            $response = $this->getCurlServiceObj()
                             ->to("http://localhost/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients")
                             ->returnResponseObject()
                             ->withHeader("X-Authorization: " . mt_rand())
                             ->get();

            $this->assertEquals(401, $response->status);
        }
    }

    /**
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_clients_csv()
    {
        /**
         * NOTE NOTE
         * This is REALLY a api call thus you cannot debug
         */
        $LoggedIn  = $this->getLoggedInUserObj();
        $ApiKeyObj = $LoggedIn->apiKey;
        $key       = $ApiKeyObj->key;
        $this->assertTrue(strlen($key) > 5);
        $response = $this->getCurlServiceObj()
                         ->to('http://localhost/api/v1/waypointMasterBridge/clients')
                         ->returnResponseObject()
                         ->withHeader('X-Authorization: ' . $this->ApiKeyObj->key)
                         ->get();

        $this->assertTrue(is_string($response->content));
        $this->assertEquals(200, $response->status);

        /**
         * @todo - make this better once we start using Ledger data in unittests
         */

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            $LoggedIn  = $this->getLoggedInUserObj();
            $ApiKeyObj = $LoggedIn->apiKey;
            $key       = $ApiKeyObj->key;
            $this->assertTrue(strlen($key) > 5);
            $response = $this->getCurlServiceObj()
                             ->to("http://localhost/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients")
                             ->returnResponseObject()
                             ->withHeader("X-Authorization: " . $this->ApiKeyObj->key)
                             ->get();

            $this->assertTrue(is_string($response->content));
            $this->assertEquals(200, $response->status);
        }
    }

    /**
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_clients_boma_coa_codes_csv()
    {
        /**
         * NOTE NOTE
         * This is REALLY a api call thus you cannot debug
         */
        $response = $this->getCurlServiceObj()
                         ->to('http://localhost/api/v1/waypointMasterBridge/clients/boma_coa_codes')
                         ->returnResponseObject()
                         ->withHeader('X-Authorization: ' . $this->ApiKeyObj->key)
                         ->get();

        $this->assertTrue(is_string($response->content));

        $body = $this->parse_bridge_response_into_array($response->content);

        $this->assertEquals(20, count($body[0]));
        $this->assertEquals('BOMA_COA_CODES_ID', $body[0][0]);
        $this->assertEquals('FK_BOMA_CLIENT_ID', $body[0][1]);
        $this->assertEquals('BOMA_ACCOUNT_CODE', $body[0][2]);
        $this->assertEquals('BOMA_ACCOUNT_NAME', $body[0][3]);
        $this->assertEquals('BOMA_ACCOUNT_NAME_UPPER', $body[0][4]);
        $this->assertEquals('VERSION_NUM', $body[0][5]);
        $this->assertEquals('BOMA_USAGE_TYPE', $body[0][6]);
        $this->assertEquals('DATA_SOURCE', $body[0][7]);
        $this->assertEquals('BOMA_ACCOUNT_HEADER_1_CODE', $body[0][8]);
        $this->assertEquals('BOMA_ACCOUNT_HEADER_1_NAME', $body[0][9]);
        $this->assertEquals('BOMA_ACCOUNT_HEADER_2_CODE', $body[0][10]);
        $this->assertEquals('BOMA_ACCOUNT_HEADER_2_NAME', $body[0][11]);
        $this->assertEquals('BOMA_ACCOUNT_HEADER_3_CODE', $body[0][12]);
        $this->assertEquals('BOMA_ACCOUNT_HEADER_3_NAME', $body[0][13]);
        $this->assertEquals('BOMA_ACCOUNT_HEADER_4_CODE', $body[0][14]);
        $this->assertEquals('BOMA_ACCOUNT_HEADER_4_NAME', $body[0][15]);
        $this->assertEquals('BOMA_ACCOUNT_HEADER_5_CODE', $body[0][16]);
        $this->assertEquals('BOMA_ACCOUNT_HEADER_5_NAME', $body[0][17]);
        $this->assertEquals('BOMA_ACCOUNT_HEADER_6_CODE', $body[0][18]);
        $this->assertEquals('BOMA_ACCOUNT_HEADER_6_NAME', $body[0][19]);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            $response = $this->getCurlServiceObj()
                             ->to("http://localhost/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/boma_coa_codes")
                             ->returnResponseObject()
                             ->withHeader("X-Authorization: " . $this->ApiKeyObj->key)
                             ->get();

            $this->assertTrue(is_string($response->content));

            $body = $this->parse_bridge_response_into_array($response->content);

            $this->assertEquals(20, count($body[0]));
            $this->assertEquals("BOMA_COA_CODES_ID", $body[0][0]);
            $this->assertEquals("FK_BOMA_CLIENT_ID", $body[0][1]);
            $this->assertEquals("BOMA_ACCOUNT_CODE", $body[0][2]);
            $this->assertEquals("BOMA_ACCOUNT_NAME", $body[0][3]);
            $this->assertEquals("BOMA_ACCOUNT_NAME_UPPER", $body[0][4]);
            $this->assertEquals("VERSION_NUM", $body[0][5]);
            $this->assertEquals("BOMA_USAGE_TYPE", $body[0][6]);
            $this->assertEquals("DATA_SOURCE", $body[0][7]);
            $this->assertEquals("BOMA_ACCOUNT_HEADER_1_CODE", $body[0][8]);
            $this->assertEquals("BOMA_ACCOUNT_HEADER_1_NAME", $body[0][9]);
            $this->assertEquals("BOMA_ACCOUNT_HEADER_2_CODE", $body[0][10]);
            $this->assertEquals("BOMA_ACCOUNT_HEADER_2_NAME", $body[0][11]);
            $this->assertEquals("BOMA_ACCOUNT_HEADER_3_CODE", $body[0][12]);
            $this->assertEquals("BOMA_ACCOUNT_HEADER_3_NAME", $body[0][13]);
            $this->assertEquals("BOMA_ACCOUNT_HEADER_4_CODE", $body[0][14]);
            $this->assertEquals("BOMA_ACCOUNT_HEADER_4_NAME", $body[0][15]);
            $this->assertEquals("BOMA_ACCOUNT_HEADER_5_CODE", $body[0][16]);
            $this->assertEquals("BOMA_ACCOUNT_HEADER_5_NAME", $body[0][17]);
            $this->assertEquals("BOMA_ACCOUNT_HEADER_6_CODE", $body[0][18]);
            $this->assertEquals("BOMA_ACCOUNT_HEADER_6_NAME", $body[0][19]);
        }
    }

    /**
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_clients_client_sftp_details_csv()
    {
        /**
         * NOTE NOTE
         * This is REALLY a api call thus you cannot debug
         *
         * We can't use the normal method of hitting a controller/method due to
         * quirks in \Excel and phpunit (how and in what order headers are sent)
         */
        $response = $this->getCurlServiceObj()
                         ->to('http://localhost/api/v1/waypointMasterBridge/clients/client_sftp_details')
                         ->returnResponseObject()
                         ->withHeader('X-Authorization: ' . $this->ApiKeyObj->key)
                         ->get();

        $this->assertTrue(is_string($response->content));
        $this->assertEquals(200, $response->status);

        /**
         * @todo - make this better once we start using Ledger data in unittests
         */

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            $response = $this->getCurlServiceObj()
                             ->to("http://localhost/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/client_sftp_details")
                             ->returnResponseObject()
                             ->withHeader("X-Authorization: " . $this->ApiKeyObj->key)
                             ->get();

            $this->assertTrue(is_string($response->content));
            $this->assertEquals(200, $response->status);
        }
    }

    /**
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_clients_wp_asset_type_csv()
    {
        /**
         * NOTE NOTE
         * This is REALLY a api call thus you cannot debug
         *
         * We can't use the normal method of hitting a controller/method due to
         * quirks in \Excel and phpunit (how and in what order headers are sent)
         */
        $response = $this->getCurlServiceObj()
                         ->to('http://localhost/api/v1/waypointMasterBridge/clients/wp_asset_type')
                         ->returnResponseObject()
                         ->withHeader('X-Authorization: ' . $this->ApiKeyObj->key)
                         ->get();

        $this->assertTrue(is_string($response->content));

        $body = $this->parse_bridge_response_into_array($response->content);
        $this->assertEquals(2, count($body[0]));
        $this->assertEquals('WP_ASSET_TYPE_ID', $body[0][0]);
        $this->assertEquals('WP_ASSET_TYPE_VALUE', $body[0][1]);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            $response = $this->getCurlServiceObj()
                             ->to("http://localhost/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/wp_asset_type")
                             ->returnResponseObject()
                             ->withHeader("X-Authorization: " . $this->ApiKeyObj->key)
                             ->get();

            $this->assertTrue(is_string($response->content));

            $body = $this->parse_bridge_response_into_array($response->content);
            $this->assertEquals(2, count($body[0]));
            $this->assertEquals("WP_ASSET_TYPE_ID", $body[0][0]);
            $this->assertEquals("WP_ASSET_TYPE_VALUE", $body[0][1]);
        }
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_clients_version_metadata_csv()
    {
        /**
         * NOTE NOTE
         * This is REALLY a api call thus you cannot debug
         *
         * We can't use the normal method of hitting a controller/method due to
         * quirks in \Excel and phpunit (how and in what order headers are sent)
         */
        $response = $this->getCurlServiceObj()
                         ->to('http://localhost/api/v1/waypointMasterBridge/clients/version_metadata')
                         ->returnResponseObject()
                         ->withHeader('X-Authorization: ' . $this->ApiKeyObj->key)
                         ->get();

        $this->assertTrue(is_string($response->content));

        $body = $this->parse_bridge_response_into_array($response->content);
        $this->assertEquals(6, count($body[0]));
        $this->assertEquals('VERSION_NUM', $body[0][0]);
        $this->assertEquals('METADATA_NAME', $body[0][1]);
        $this->assertEquals('RELEASE_DATE', $body[0][2]);
        $this->assertEquals('RELEASE_NOTES', $body[0][3]);
        $this->assertEquals('CREATE_ON', $body[0][4]);
        $this->assertEquals('MODIFIED_ON', $body[0][5]);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            $response = $this->getCurlServiceObj()
                             ->to("http://localhost/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/version_metadata")
                             ->returnResponseObject()
                             ->withHeader("X-Authorization: " . $this->ApiKeyObj->key)
                             ->get();

            $this->assertTrue(is_string($response->content));

            $body = $this->parse_bridge_response_into_array($response->content);
            $this->assertEquals(6, count($body[0]));
            $this->assertEquals("VERSION_NUM", $body[0][0]);
            $this->assertEquals("METADATA_NAME", $body[0][1]);
            $this->assertEquals("RELEASE_DATE", $body[0][2]);
            $this->assertEquals("RELEASE_NOTES", $body[0][3]);
            $this->assertEquals("CREATE_ON", $body[0][4]);
            $this->assertEquals("MODIFIED_ON", $body[0][5]);
        }
    }

    /**
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_read_clients_column_datatypes_csv()
    {
        /**
         * NOTE NOTE
         * This is REALLY a api call thus you cannot debug
         *
         * We can't use the normal method of hitting a controller/method due to
         * quirks in \Excel and phpunit (how and in what order headers are sent)
         */
        $response = $this->getCurlServiceObj()
                         ->to('http://localhost/api/v1/waypointMasterBridge/clients/column_datatypes')
                         ->returnResponseObject()
                         ->withHeader('X-Authorization: ' . $this->ApiKeyObj->key)
                         ->get();

        $this->assertTrue(is_string($response->content));

        $body = $this->parse_bridge_response_into_array($response->content);
        $this->assertEquals(3, count($body[0]));
        $this->assertEquals('COLUMN_DATATYPES_ID', $body[0][0]);
        $this->assertEquals('COLUMN_NAME_TEXT', $body[0][1]);
        $this->assertEquals('DATA_TYPE_VALUES', $body[0][2]);

        if (env('LOAD_DEPRECATED_ROUTES_HER_2913', true))
        {
            $response = $this->getCurlServiceObj()
                             ->to("http://localhost/api/v1/waypointMasterBridge/" . Role::WAYPOINT_ROOT_ROLE . "/clients/column_datatypes")
                             ->returnResponseObject()
                             ->withHeader("X-Authorization: " . $this->ApiKeyObj->key)
                             ->get();

            $this->assertTrue(is_string($response->content));

            $body = $this->parse_bridge_response_into_array($response->content);
            $this->assertEquals(3, count($body[0]));
            $this->assertEquals("COLUMN_DATATYPES_ID", $body[0][0]);
            $this->assertEquals("COLUMN_NAME_TEXT", $body[0][1]);
            $this->assertEquals("DATA_TYPE_VALUES", $body[0][2]);
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
