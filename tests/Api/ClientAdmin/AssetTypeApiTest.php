<?php

namespace App\Waypoint\Tests\Api\Root;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AssetType;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAssetTypeTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class AssetTypeApiBaseTest
 *
 * @codeCoverageIgnore
 */
class AssetTypeApiBaseTest extends TestCase
{
    use MakeAssetTypeTrait, ApiTestTrait;

    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_asset_types()
    {
        /** @var  array $asset_types_arr */
        $asset_types_arr = $this->fakeAssetTypeData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/assetTypes',
            $asset_types_arr
        );
        $this->assertApiSuccess();
        $assetTypes_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/assetTypes/' . $assetTypes_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/' . substr('assetTypes', 0, 32) . '/' . $assetTypes_id
        );

        /**
         * since users are never deleted, just made inactive......
         */
        if (get_class($this) == 'App\Waypoint\Tests\Generated\UserApiBaseTest')
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
                '/api/v1/clients/' . $this->ClientObj->id . '/assetTypes',
                $asset_types_arr
            );
            $this->assertApiSuccess();
        }

        $assetTypes_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/assetTypes/' . $assetTypes_id
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/assetTypes/' . $assetTypes_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/assetTypes/' . $assetTypes_id
        );
        $this->assertApiFailure();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_asset_types_deprecated()
    {
        /** @var  array $asset_types_arr */
        $asset_types_arr = $this->fakeAssetTypeData();
        $this->json(
            'POST',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/assetTypes',
            $asset_types_arr
        );
        $this->assertApiSuccess();
        $assetTypes_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/assetTypes/' . $assetTypes_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/' . substr('assetTypes', 0, 32) . '/' . $assetTypes_id
        );

        $this->assertAPIFailure([400]);

        /**
         * now re-add it
         */
        $this->json(
            'POST',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/assetTypes',
            $asset_types_arr
        );
        $this->assertApiSuccess();

        $assetTypes_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/assetTypes/' . $assetTypes_id
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/assetTypes/' . $assetTypes_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/assetTypes/' . $assetTypes_id
        );
        $this->assertApiFailure();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     */
    public function it_can_read_asset_types_list()
    {
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/assetTypes/' . '?limit=' . config('waypoint.unittest_loop')
        );
        $this->assertAPIListResponse(AssetType::class);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_asset_types()
    {
        $this->json(
            'GET', '/api/v1/clients/' . $this->ClientObj->id . '/assetTypes/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_delete_non_existing_asset_types()
    {
        /** @var  AssetType $assetTypeObj */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/assetTypes/1000' . mt_rand()
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
