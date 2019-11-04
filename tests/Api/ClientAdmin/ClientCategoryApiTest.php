<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App\Waypoint\Models\Role;
use App\Waypoint\Seeder;
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
        $this->setLoggedInUserRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_boma_client_categories()
    {
        /** @var  array $boma_client_categories_arr */
        $boma_client_categories_arr = $this->fakeClientCategoryData();
        $this->json(
            'POST', '/api/v1/clients/' . $this->ClientObj->id . '/clientCategories',
            $boma_client_categories_arr
        );
        $clientCategories_id = $this->getFirstDataObject()['id'];

        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/clientCategories/' . $clientCategories_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET', '/api/v1/clients/' . $this->ClientObj->id . '/clientCategories/' . $clientCategories_id
        );
        $this->assertAPIFailure([404, 500, 400]);

        /**
         * now re-add it
         */
        $this->json(
            'POST', '/api/v1/clients/' . $this->ClientObj->id . '/clientCategories',
            $boma_client_categories_arr
        );
        $clientCategories_id = $this->getFirstDataObject()['id'];
        /** @var  ClientCategory $ClientCategoryObj */
        $this->assertApiSuccess();

        $this->json(
            'GET', '/api/v1/clients/' . $this->ClientObj->id . '/clientCategories/' . $this->getFirstDataObject()['id']
        );
        $this->assertApiSuccess();

        /** @var  ClientCategory $ClientCategoryObj */
        $ClientCategoryObj = $this->makeClientCategory();
        /*
             * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_client_categories_arr */
        $edited_client_categories_arr = $this->fakeClientCategoryData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT', '/api/v1/clients/' . $this->ClientObj->id . '/clientCategories/' . $ClientCategoryObj->id,
            $edited_client_categories_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE', '/api/v1/clients/' . $this->ClientObj->id . '/clientCategories/' . $clientCategories_id
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_update_non_existing_boma_client_categories()
    {
        /** @var  array $editedBomaClientCategory_arr */
        $editedBomaClientCategory_arr = $this->fakeClientCategoryData([], Seeder::DEFAULT_FACTORY_NAME);
        /** @var  ClientCategory $ClientCategoryObj */
        $this->json(
            'PUT', '/api/v1/clients/' . $this->ClientObj->id . '/clientCategories/' . '1000000' . mt_rand(),
            $editedBomaClientCategory_arr
        );
        $this->assertAPIFailure([400, 404]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_delete_non_existing_boma_client_categories()
    {
        /** @var  ClientCategory $ClientCategoryObj */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/clientCategories/1000' . mt_rand()
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
