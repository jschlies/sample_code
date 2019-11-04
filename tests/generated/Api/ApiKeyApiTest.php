<?php

namespace App\Waypoint\Tests;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeApiKeyTrait;

/**
 * Class ApiKeyApiBaseTest
 *
 * @codeCoverageIgnore
 */
class ApiKeyApiBaseTest extends TestCase
{
    use MakeApiKeyTrait, ApiTestTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_api_keys()
    {
        /** @var  array $api_keys_arr */
        $api_keys_arr = $this->fakeApiKeyData();
        $this->json(
            'POST',
            '/api/v1/' . substr('apiKeys', 0, 32),
            $api_keys_arr
        );
        $this->assertApiSuccess();
        $apiKeys_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('apiKeys', 0, 32) . '/' . $apiKeys_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('apiKeys', 0, 32) . '/' . $apiKeys_id
        );

        /**
         * since users are never deleted, just made inactive......
         */
        if (get_class($this) == UserApiBaseTest::class)
        {
            $this->assertApiSuccess();
        }
        else
        {
            $this->assertAPIFailure([400]);

            /**
             * now re-add it
             */
            $this->json(
                'POST',
                '/api/v1/' . substr('apiKeys', 0, 32),
                $api_keys_arr
            );
            $this->assertApiSuccess();
        }

        $apiKeys_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/' . substr('apiKeys', 0, 32) . '/' . $this->getFirstDataObject()['id']
        );
        $this->assertApiSuccess();

        /** @var  ApiKey $apiKeyObj */
        $apiKeyObj = $this->makeApiKey();
        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_api_keys_arr */
        $edited_api_keys_arr = $this->fakeApiKeyData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/' . substr('apiKeys', 0, 32) . '/' . $apiKeyObj->id,
            $edited_api_keys_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('apiKeys', 0, 32) . '/' . $apiKeys_id
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     */
    public function it_can_read_api_keys_list()
    {
        /** @var  array $api_keys_arr */
        $api_keys_arr = $this->fakeApiKeyData();
        $this->json(
            'POST',
            '/api/v1/' . substr('apiKeys', 0, 32),
            $api_keys_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('apiKeys', 0, 32) . '?limit=' . config('waypoint.unittest_loop')
        );

        $this->assertAPIListResponse(ApiKey::class);

    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_api_keys()
    {
        $this->json(
            'GET',
            '/api/v1/' . substr('apiKeys', 0, 32) . '/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_update_non_existing_api_keys()
    {
        /** @var  array $editedApiKey_arr */
        $editedApiKey_arr = $this->fakeApiKeyData([], Seeder::DEFAULT_FACTORY_NAME);
        /** @var  ApiKey $apiKeyObj */
        $this->json(
            'PUT',
            '/api/v1/' . substr('apiKeys', 0, 32) . '/' . '1000000' . mt_rand(), $editedApiKey_arr
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_delete_non_existing_api_keys()
    {
        /** @var  ApiKey $apiKeyObj */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('apiKeys', 0, 32) . '/1000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
