<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeClientTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class ClientDetailApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class ClientDetailApiTest extends TestCase
{
    use MakeClientTrait, ApiTestTrait;

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
     */
    public function it_can_read_client_detail()
    {
        $this->json(
            'GET',
            '/api/v1/clientDetails/' . $this->getLoggedInUserObj()->client_id
        );
        $this->assertTrue(method_exists($this->getLoggedInUserObj()->client, 'audits'));
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * Add to this with new configs that should be present for a client
     */
    public function it_has_minimal_set_of_configs()
    {
        //$config_json = json_decode($this->ClientObj->config_json, true);
        //
        //$this->assertArrayHasKey(Client::DECIMAL_DISPLAY_FLAG, $config_json);
        //$this->assertArrayHasKey(Client::NEGATIVE_VALUE_SYMBOLS_FLAG, $config_json);

    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
