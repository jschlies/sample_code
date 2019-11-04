<?php

namespace App\Waypoint\Tests\ClientAdmin;

use App\Waypoint\Models\NativeAccountTypeTrailer;
use App\Waypoint\Models\Role;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeNativeAccountTypeTrailerTrait;
use App\Waypoint\Tests\ApiTestTrait;
use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Tests\TestCase;
use Exception;

/**
 * Class NativeAccountTypeApiBaseTest
 *
 * @codeCoverageIgnore
 */
class NativeAccountTypeTrailerApiTest extends TestCase
{
    use MakeNativeAccountTypeTrailerTrait, ApiTestTrait;

    public function setUp()
    {
        try
        {
            $this->setLoggedInUserRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
            parent::setUp();
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException($e->getMessage(), 404, $e);
        }
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_native_account_type_trailers()
    {
        /** @var  array $native_account_type_trailer_arr */
        $native_account_type_trailer_arr = $this->fakeNativeAccountTypeTrailerData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers',
            $native_account_type_trailer_arr
        );
        $this->assertApiSuccess();
        $native_account_type_trailer_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id
        );
        $this->assertApiFailure();

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

            $this->json(
                'POST',
                '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers',
                $native_account_type_trailer_arr
            );
            $this->assertApiSuccess();
        }

        $native_account_type_trailer_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id
        );
        $this->assertApiSuccess();

        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_native_account_type_trailer_arr */
        $edited_native_account_type_trailer_arr = $this->fakeNativeAccountTypeTrailerData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id,
            $edited_native_account_type_trailer_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers'
        );
        $this->assertAPIListResponse(NativeAccountTypeTrailer::class);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);

        $editedNativeAccountType_arr = $this->fakeNativeAccountTypeTrailerData([], Seeder::DEFAULT_FACTORY_NAME);

        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . '1000000' . mt_rand(),
            $editedNativeAccountType_arr
        );
        $this->assertAPIFailure([400]);

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id
        );
        $this->assertApiFailure();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_native_account_type_trailers_deprecated()
    {
        /** @var  array $native_account_type_trailer_arr */
        $native_account_type_trailer_arr = $this->fakeNativeAccountTypeTrailerData();
        $this->json(
            'POST',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers',
            $native_account_type_trailer_arr
        );
        $this->assertApiSuccess();
        $native_account_type_trailer_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id
        );
        $this->assertApiFailure();

        $this->assertAPIFailure([400]);

        $this->json(
            'POST',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers',
            $native_account_type_trailer_arr
        );
        $this->assertApiSuccess();

        $native_account_type_trailer_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id
        );
        $this->assertApiSuccess();

        /** @var  array $edited_native_account_type_trailer_arr */
        $edited_native_account_type_trailer_arr = $this->fakeNativeAccountTypeTrailerData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id,
            $edited_native_account_type_trailer_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers'
        );
        $this->assertAPIListResponse(NativeAccountTypeTrailer::class);

        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);

        $editedNativeAccountType_arr = $this->fakeNativeAccountTypeTrailerData([], Seeder::DEFAULT_FACTORY_NAME);

        $this->json(
            'PUT',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . '1000000' . mt_rand(),
            $editedNativeAccountType_arr
        );
        $this->assertAPIFailure([400]);

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_trailer_arr['native_account_type_id'] . '/nativeAccountTypeTrailers/' . $native_account_type_trailer_id
        );
        $this->assertApiFailure();
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
