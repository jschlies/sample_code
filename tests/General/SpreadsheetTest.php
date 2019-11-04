<?php

namespace App\Waypoint\Tests\General;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\Spreadsheet;
use App\Waypoint\Tests\TestCase;
use Exception;

class SpreadsheetTest extends TestCase
{
    public function setUp()
    {
        try
        {
            $this->setLoggedInUserRole(Role::WAYPOINT_ASSOCIATE_ROLE);
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
     */
    public function user_management_spreadsheet_has_correct_data()
    {
        $Users               = $this->UserRepositoryObj->findWhere(['client_id' => $this->ClientObj->id]);
        $users_arr_formatted = Spreadsheet::formatSpreadsheetData($Users->toArray(), Spreadsheet::getUserManagementConfig());

        $ordered_list_of_column_name = [
            'Email',
            'First Name',
            'Last Name',
            'Role',
            'Access Lists',
            'Status',
            'Invitation Status',
            'Date Created',
            'First Login',
            'Last Login',
        ];

        foreach ($users_arr_formatted as $user_arr_formatted)
        {
            // match the above ordered list
            $this->assertTrue(array_keys($user_arr_formatted) === $ordered_list_of_column_name);

            $spreadsheet_config_arr = Spreadsheet::getUserManagementConfig();

            // contain same field count as config
            $this->assertEquals(count($user_arr_formatted), count(array_keys($spreadsheet_config_arr)));

            // match config data
            foreach ($spreadsheet_config_arr as $field_name => $field_config)
            {
                if (isset($field_config['header_name']))
                {
                    $this->assertArrayHasKey($field_config['header_name'], $user_arr_formatted);
                }
                else
                {
                    $this->assertArrayHasKey($field_name, $user_arr_formatted);
                }
            }
        }
    }
}
