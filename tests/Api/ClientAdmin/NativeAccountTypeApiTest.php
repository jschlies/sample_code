<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App\Waypoint\Models\NativeAccountTypeDetail;
use App\Waypoint\Models\Role;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeNativeAccountTypeTrait;
/**
 * remember you cannot 'use App\Waypoint\Models\Role here as it messes with Role unit tests
 */

use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\ApiTestTrait;
use App;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class NativeAccountTypeApiBaseTest
 *
 * @codeCoverageIgnore
 */
class NativeAccountTypeApiBaseTest extends TestCase
{
    use MakeNativeAccountTypeTrait, ApiTestTrait;

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
    public function it_can_create_native_account_types()
    {
        /** @var  array $native_account_types_arr */
        $native_account_types_arr = $this->fakeNativeAccountTypeData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes',
            $native_account_types_arr
        );
        $this->assertApiSuccess();
        $native_account_type_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypesDetail/' . $native_account_type_id
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

            $this->json(
                'POST',
                '/api/v1/clients/' . $this->ClientObj->id . '/' . substr('nativeAccountTypes', 0, 32),
                $native_account_types_arr
            );
            $this->assertApiSuccess();
        }

        $native_account_type_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypesDetail/' . $native_account_type_id
        );
        $this->assertApiSuccess();

        /** @var  NativeAccountType $nativeAccountTypeObj */
        $nativeAccountTypeObj = $this->makeNativeAccountType();
        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_native_account_types_arr */
        $edited_native_account_types_arr = $this->fakeNativeAccountTypeData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $nativeAccountTypeObj->id,
            $edited_native_account_types_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/' . $native_account_type_id
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
    public function it_can_read_native_account_types_list()
    {
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypesDetail?limit = ' . config('waypoint.unittest_loop')
        );
        $this->assertAPIListResponse(NativeAccountTypeDetail::class);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_native_account_types()
    {
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypesDetail/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_update_non_existing_native_account_types()
    {
        /** @var  array $editedNativeAccountType_arr */
        $editedNativeAccountType_arr = $this->fakeNativeAccountTypeData([], Seeder::DEFAULT_FACTORY_NAME);
        /** @var  NativeAccountType $nativeAccountTypeObj */
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/1000000' . mt_rand(),
            $editedNativeAccountType_arr
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public
    function it_cannot_delete_non_existing_native_account_types()
    {
        /** @var  NativeAccountType $nativeAccountTypeObj */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeAccountTypes/1000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected
    function tearDown()
    {
        unset($this->NativeAccountTypeRepositoryObj);
        parent::tearDown();
    }
}
