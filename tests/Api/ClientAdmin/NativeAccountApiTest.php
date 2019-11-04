<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Role;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeNativeAccountTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class NativeAccountApiBaseTest
 *
 * @codeCoverageIgnore
 */
class NativeAccountApiBaseTest extends TestCase
{
    use MakeNativeAccountTrait, ApiTestTrait;

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
    public function it_can_create_native_accounts()
    {
        /** @var  array $native_accounts_arr */
        $native_accounts_arr = $this->fakeNativeAccountData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts',
            $native_accounts_arr
        );
        $this->assertApiSuccess();
        $native_account_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts/' . $native_account_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts/' . $native_account_id
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
                '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts',
                $native_accounts_arr
            );
            $this->assertApiSuccess();
            $native_account_id = $this->getFirstDataObject()['id'];
        }

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts/'
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts/' . $native_account_id
        );
        $this->assertApiSuccess();

        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_native_accounts_arr */
        $edited_native_accounts_arr = $this->fakeNativeAccountData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts/' . $native_account_id,
            $edited_native_accounts_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts/' . $native_account_id
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_native_accounts_deprecated()
    {
        /** @var  array $native_accounts_arr */
        $native_accounts_arr = $this->fakeNativeAccountData();
        $this->json(
            'POST',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts',
            $native_accounts_arr
        );
        $this->assertApiSuccess();
        $native_account_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts/' . $native_account_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts/' . $native_account_id
        );

        $this->assertAPIFailure([400]);

        /**
         * now re-add it
         */

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts',
            $native_accounts_arr
        );
        $this->assertApiSuccess();
        $native_account_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts/'
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts/' . $native_account_id
        );
        $this->assertApiSuccess();

        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_native_accounts_arr */
        $edited_native_accounts_arr = $this->fakeNativeAccountData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts/' . $native_account_id,
            $edited_native_accounts_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . Role::CLIENT_ADMINISTRATIVE_USER_ROLE . '/clients/' . $this->ClientObj->id . '/nativeCoas/' . $native_accounts_arr['native_coa_id'] . '/nativeAccounts/' . $native_account_id
        );
        $this->assertApiSuccess();
    }
}
