<?php

namespace App\Waypoint\Tests\General;

use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Ledger\Ledger;
use App\Waypoint\Models\NativeAccountType;
use App\Waypoint\Models\User;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Models\Role;

class CustomReportTemplateAnalyticsTest extends TestCase
{
    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     *
     * Todo (Alex) - remove this test when we switch ON the custom report template analytics
     */
    public function is_custom_report_template_analytics_SWITCHED_OFF_for_all_clients()
    {
        $this->getAllClientsExceptDummy()->each(function (Client $ClientObj)
        {
            $this->assertFalse($ClientObj->getConfigValue(Client::CUSTOM_REPORT_TEMPLATE_ANALYTICS_FLAG));
        });
    }

    /**
     * @test
     */
    public function does_CLIENT_CONFIG_contain_necessary_keys_for_custom_report_template_analytics()
    {
        $this->getAllClientsExceptDummy()->each(function (Client $ClientObj)
        {
            $client_config_arr = $ClientObj->getConfigJSON(true);

            $this->assertArrayHasKey(
                Client::WAYPOINT_LEDGER_DROPDOWNS,
                $client_config_arr
            );
            $this->assertArrayHasKey(
                Client::DEFAULTS_CONFIG_KEY,
                $client_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS]
            );
            $this->assertArrayHasKey(
                NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY,
                $client_config_arr
            );
            $this->assertArrayHasKey(
                AdvancedVariance::ADVANCED_VARIANCE_CONFIG_KEY,
                $client_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY]
            );
            // Todo (Alex) remove assert for ANALYTICS_CONFIG_KEY when this key is pulled from the client config obj
            $this->assertArrayHasKey(
                Ledger::ANALYTICS_CONFIG_KEY,
                $client_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY]
            );
            $this->assertNotNull(
                $client_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][AdvancedVariance::ADVANCED_VARIANCE_CONFIG_KEY]
            );
            $this->assertNotNull(
                $client_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][Ledger::ANALYTICS_CONFIG_KEY]
            );

        });
    }

    /**
     * @test
     */
    public function does_USER_CONFIG_contain_necessary_keys_for_custom_report_template_analytics()
    {
        $this->getAllClientsExceptDummy()->each(function (Client $ClientObj)
        {

            $ClientObj->users()->each(function (User $UserObj)
            {

                $user_config_arr = $UserObj->getConfigJSON(true);

                $this->assertArrayHasKey(
                    Client::WAYPOINT_LEDGER_DROPDOWNS,
                    $user_config_arr
                );
                $this->assertArrayHasKey(
                    Client::DEFAULTS_CONFIG_KEY,
                    $user_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS]
                );
                $this->assertArrayHasKey(
                    NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY,
                    $user_config_arr
                );
                $this->assertArrayHasKey(
                    Ledger::ANALYTICS_CONFIG_KEY,
                    $user_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY]
                );
                $this->assertNotNull(
                    $user_config_arr[User::DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG]
                );
                $this->assertNotNull(
                    $user_config_arr[Client::WAYPOINT_LEDGER_DROPDOWNS][Client::DEFAULTS_CONFIG_KEY]
                );
                $this->assertNotNull(
                    $user_config_arr[NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY][Ledger::ANALYTICS_CONFIG_KEY]
                );
            });

        });
    }
}
