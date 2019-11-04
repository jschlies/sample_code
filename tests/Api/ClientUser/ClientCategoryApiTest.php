<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App\Waypoint\Models\Role;
use App\Waypoint\Tests\Generated\MakeClientCategoryTrait;
use App\Waypoint\Models\ClientCategory;
use App;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class ClientCategoryApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class ClientCategoryApiTest extends TestCase
{
    use MakeClientCategoryTrait, ApiTestTrait;

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
    public function it_can_read_boma_client_categories_list()
    {
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/clientCategories'
        );
        $this->assertAPIListResponse(ClientCategory::class);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_boma_client_categories()
    {
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/clientCategories/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400, 500]);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
