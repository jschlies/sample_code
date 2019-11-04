<?php

namespace App\Waypoint\Tests;

use \Ixudra\Curl\CurlService as Ixudra_Curl_CurlService;
use App;
use App\Waypoint\Auth0\Auth0ApiManagement;
use App\Waypoint\Auth0\Auth0ClientCredential;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Tests\Generated\MakeUserTrait;

/**
 * Class UserRepositoryBaseTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class Auth0ApiManagementTest extends TestCase
{
    use MakeUserTrait, ApiTestTrait;
    use MakeFavoriteTrait;

    /** @var  Auth0ApiManagement */
    protected $Auth0ApiManagementObj;

    public function setUp()
    {
        parent::setUp();
        $this->Auth0ApiManagementObj = App::make(Auth0ApiManagement::class);
    }

    /**
     * @test
     *
     * see http://php.net/manual/en/function.parse-url.php
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_gets_various_values()
    {
        $this->assertTrue(self::is_syntactially_valid_url($this->Auth0ApiManagementObj->getManagementUrl()));
        $management_url_parsed = parse_url($this->Auth0ApiManagementObj->getManagementUrl());
        $this->assertEquals($management_url_parsed['scheme'], 'https');
        $this->assertTrue(self::is_valid_domain_name($management_url_parsed['host']));
        $this->assertEquals($management_url_parsed['host'], config('waypoint.management_auth0_domain', false));
        $this->assertEquals($management_url_parsed['path'], '/api/' . Auth0ApiManagement::AUTH0_MANAGEMENT_API_VERSION . '/');

        $this->assertEquals(get_class($this->Auth0ApiManagementObj->getCurlServiceObj()), Ixudra_Curl_CurlService::class);

        $this->assertTrue(self::is_valid_domain_name($this->Auth0ApiManagementObj->getAuth0Domain()));
        $this->assertEquals($this->Auth0ApiManagementObj->getAuth0Domain(), config('waypoint.management_auth0_domain', false));

        $this->assertTrue(strlen($this->Auth0ApiManagementObj->getManagementClientId()) > 0);
        $this->assertEquals($this->Auth0ApiManagementObj->getManagementClientId(), config('waypoint.management_client_id', false));

        $this->assertTrue(strlen($this->Auth0ApiManagementObj->getManagementClientSecret()) > 0);
        $this->assertEquals($this->Auth0ApiManagementObj->getManagementClientSecret(), config('waypoint.management_client_secret', false));

        $this->assertTrue(self::is_syntactially_valid_url($this->Auth0ApiManagementObj->getManagementAudience()));
        $this->assertEquals($this->Auth0ApiManagementObj->getManagementAudience(), config('waypoint.management_audience', false));
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_gets_Auth0ClientCredential()
    {
        $this->assertEquals(get_class($this->Auth0ApiManagementObj->getAuth0ClientCredentialObj()), Auth0ClientCredential::class);

        $this->assertTrue(strlen($this->Auth0ApiManagementObj->getAuth0ClientCredentialObj()->getAccessToken()) > 0);

        $this->assertTrue(strlen($this->Auth0ApiManagementObj->getAuth0ClientCredentialObj()->getScope()) > 0);

        $this->assertTrue(strlen($this->Auth0ApiManagementObj->getAuth0ClientCredentialObj()->getExpiresIn()) > 0);
        $this->assertInternalType('integer', $this->Auth0ApiManagementObj->getAuth0ClientCredentialObj()->getExpiresIn());
        $this->assertTrue($this->Auth0ApiManagementObj->getAuth0ClientCredentialObj()->getExpiresIn() > 600);

        $this->assertTrue(strlen($this->Auth0ApiManagementObj->getAuth0ClientCredentialObj()->getTokenType()) > 0);
        $this->assertEquals('Bearer', $this->Auth0ApiManagementObj->getAuth0ClientCredentialObj()->getTokenType());
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}