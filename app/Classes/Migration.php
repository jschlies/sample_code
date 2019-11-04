<?php

namespace App\Waypoint;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Repositories\NativeAccountTypeRepository;
use DB;
use Illuminate\Database\Migrations\Migration as MigrationBase;

class Migration extends MigrationBase
{
    var $boma_code_hash = [];
    var $client_name_hash = [];
    var $client_id_hash = [];
    var $property_name_hash = [];
    var $property_code_hash = [];
    var $header_needed = true;

    public function print_header_if_needed()
    {
        if ($this->header_needed)
        {
            echo '-------------------------------' . PHP_EOL;
        }
        $this->header_needed = false;
    }

    /**
     * @param $mapping_arr
     * @return bool|mixed
     */
    public function get_client_in_question_with_name($mapping_arr)
    {
        /** Client */
        if (isset($this->client_name_hash[$mapping_arr['client_name']]))
        {
            $ClientObj = $this->client_name_hash[$mapping_arr['client_name']];
        }
        else
        {
            $client_arr = DB::select(
                DB::raw(
                    'SELECT *
                            FROM clients
                            WHERE name= :name'
                ),
                [
                    'name' => $mapping_arr['client_name'],
                ]
            );
            if (count($client_arr) != 1)
            {
                return false;
            }
            $ClientObj                                           = $client_arr[0];
            $this->client_name_hash[$mapping_arr['client_name']] = $ClientObj;
        }
        return $ClientObj;
    }

    /**
     * @param $client_id
     * @return bool|mixed
     */
    public function get_client_in_question_with_client_id($client_id)
    {
        /** Client */
        if (isset($this->client_id_hash[$client_id]))
        {
            $ClientObj = $this->client_id_hash[$client_id];
        }
        else
        {
            $client_arr = DB::select(
                DB::raw(
                    'SELECT *
                            FROM clients
                            WHERE id= :id'
                ),
                [
                    'id' => $client_id,
                ]
            );
            if (count($client_arr) != 1)
            {
                return false;
            }
            $ClientObj                        = $client_arr[0];
            $this->client_id_hash[$client_id] = $ClientObj;
        }
        return $ClientObj;
    }

    /**
     * @param $mapping_arr
     * @param $ClientObj
     * @return mixed
     * @throws GeneralException
     */
    public function get_property_in_question_with_property_name($mapping_arr, $ClientObj)
    {

        /** Property */
        if (isset($this->property_name_hash[$mapping_arr['client_name']][$mapping_arr['property_name']]))
        {
            $PropertyObj = $this->property_name_hash[$mapping_arr['client_name']][$mapping_arr['property_name']];
        }
        else
        {
            $property_arr = DB::select(
                DB::raw(
                    'SELECT *,native_coas.id AS native_coas_id
                        FROM properties
                        JOIN property_native_coas ON properties.id = property_native_coas.property_id 
                        JOIN native_coas ON native_coas.id = property_native_coas.native_coa_id
                        WHERE properties.name= :name AND properties.client_id = :client_id'
                ),
                [
                    'name'      => $mapping_arr['property_name'],
                    'client_id' => $ClientObj->id,
                ]
            );
            if (count($property_arr) != 1)
            {
                throw new GeneralException('WTF');
            }

            $PropertyObj                                                                          = $property_arr[0];
            $this->property_name_hash[$mapping_arr['client_name']][$mapping_arr['property_name']] = $PropertyObj;
        }
        return $PropertyObj;
    }

    /**
     * @param $mapping_arr
     * @return array|mixed
     */
    public function get_property_in_question_with_property_code($mapping_arr)
    {
        /** Property */
        if (isset($this->property_code_hash[$mapping_arr['PROPERTY_CODE']]))
        {
            $property_arr = $this->property_code_hash[$mapping_arr['PROPERTY_CODE']];
        }
        else
        {
            $property_arr = DB::select(
                DB::raw(
                    'SELECT *,native_coas.id AS native_coas_id, properties.id AS property_id
                        FROM properties
                        JOIN property_native_coas ON properties.id = property_native_coas.property_id 
                        JOIN native_coas ON native_coas.id = property_native_coas.native_coa_id
                        WHERE properties.property_code= :property_code '
                ),
                [
                    'property_code' => $mapping_arr['PROPERTY_CODE'],
                ]
            );

            $this->property_code_hash[$mapping_arr['PROPERTY_CODE']] = $property_arr;
        }
        return $property_arr;
    }

    /**
     * @param $mapping_arr
     * @return mixed
     * @throws GeneralException
     */
    public function get_report_template_account_group_in_question($mapping_arr)
    {
        /** Boma */
        if (isset($this->boma_code_hash[$mapping_arr['BOMA_ACCOUNT_CODE']]))
        {
            $ReportTemplateAccountGroupCodeObj = $this->boma_code_hash[$mapping_arr['BOMA_ACCOUNT_CODE']];
        }
        else
        {
            $report_template_account_group_code_arr = DB::select(
                DB::raw(
                    'SELECT *
                            FROM report_template_account_groups
                            WHERE report_template_account_group_code= :report_template_account_group_code'
                ),
                [
                    'report_template_account_group_code' => $mapping_arr['BOMA_ACCOUNT_CODE'],
                ]
            );
            if (count($report_template_account_group_code_arr) != 1)
            {
                throw new GeneralException('WTF');
            }
            $ReportTemplateAccountGroupCodeObj                       = $report_template_account_group_code_arr[0];
            $this->boma_code_hash[$mapping_arr['BOMA_ACCOUNT_CODE']] = $ReportTemplateAccountGroupCodeObj;
        }
        return $ReportTemplateAccountGroupCodeObj;
    }

    /**
     * @param $mapping_arr
     * @param $PropertyObj
     * @return mixed
     * @throws GeneralException
     */
    public function get_native_account_in_question($mapping_arr, $PropertyObj)
    {
        if ( ! isset($mapping_arr['ACCOUNT_NAME']) || ! $mapping_arr['ACCOUNT_NAME'])
        {
            $mapping_arr['ACCOUNT_NAME'] = 'nullname ' . mt_rand();
        }

        $native_account_arr   = DB::select(
            DB::raw(
                'SELECT *
                            FROM native_accounts
                            WHERE native_coa_id= :native_coa_id AND 
                                  native_coa_code = :native_coa_code'
            ),
            [
                'native_coa_id'   => $PropertyObj->native_coas_id,
                'native_coa_code' => $mapping_arr['ACCOUNT_CODE'],
            ]
        );
        $NativeAccountTypeObj = \App::make(NativeAccountTypeRepository::class)
                                    ->findWhere([
                                                    'native_coa_id' => $PropertyObj->native_coas_id,
                                                    'name'          => NativeAccount::NATIVE_ACCOUNT_TYPE_DEFAULT,
                                                ]);
        if ( ! $NativeAccountTypeObj)
        {
            throw new GeneralException('no default $NativeAccountTypeObj for property_id = ' . $PropertyObj->id . ' native_coas_id = ' . $PropertyObj->native_coas_id);
        }
        if (count($native_account_arr) == 0)
        {
            DB::insert(
                DB::raw(
                    'INSERT INTO native_accounts
                                SET native_coa_id = :native_coa_id , 
                                    native_account_code = :native_account_code , 
                                    native_account_name = :native_account_name , 
                                    is_category = FALSE, 
                                    is_recoverable = FALSE,
                                    native_coa_type = :native_coa_type'
                ),
                [
                    'native_coa_id'       => $PropertyObj->native_coas_id,
                    'native_account_code' => $mapping_arr['ACCOUNT_CODE'],
                    'native_account_name' => $mapping_arr['ACCOUNT_NAME'],
                    'native_coa_type_id'  => $NativeAccountTypeObj->id,
                ]
            );

            $this->print_header_if_needed();
            echo 'Added native_account for client id = ' . $PropertyObj->client_id . ' property id = ' . $PropertyObj->property_id . ' ACCOUNT_CODE = ' . $mapping_arr['ACCOUNT_CODE'] . PHP_EOL;

            $native_account_arr = DB::select(
                DB::raw(
                    'SELECT *
                                FROM native_accounts
                                WHERE native_coa_id= :native_coa_id AND 
                                      native_account_code = :native_account_code'
                ),
                [
                    'native_coa_id'   => $PropertyObj->native_coas_id,
                    'native_coa_code' => $mapping_arr['ACCOUNT_CODE'],
                ]
            );
        }
        if (count($native_account_arr) != 1)
        {
            throw new GeneralException('WTF');
        }
        else
        {
            $NativeAccountObj = $native_account_arr[0];
        }

        return $NativeAccountObj;
    }

    /**
     * @param $ReportTemplateAccountGroupCodeObj
     * @param $NativeAccountObj
     * @param $PropertyObj
     * @return mixed
     * @throws GeneralException
     */
    public function get_report_template_mappings_in_question($ReportTemplateAccountGroupCodeObj, $NativeAccountObj, $PropertyObj)
    {
        $report_template_mapping_arr = DB::select(
            DB::raw(
                'SELECT *
                            FROM report_template_mappings
                            WHERE native_account_id= :native_account_id AND 
                                  report_template_account_group_id = :report_template_account_group_id'
            ),
            [
                'native_account_id'                => $NativeAccountObj->id,
                'report_template_account_group_id' => $ReportTemplateAccountGroupCodeObj->id,
            ]
        );
        if (count($report_template_mapping_arr) == 0)
        {
            DB::insert(
                DB::raw(
                    'INSERT INTO report_template_mappings
                                SET native_account_id= :native_account_id , 
                                    report_template_account_group_id= :report_template_account_group_id 
                                    '
                ),
                [
                    'native_account_id'                => $NativeAccountObj->id,
                    'report_template_account_group_id' => $ReportTemplateAccountGroupCodeObj->id,
                ]
            );

            $this->print_header_if_needed();
            echo 'Added mapping for client id = ' . $PropertyObj->client_id . ' property id = ' . $PropertyObj->property_id . ' ACCOUNT_CODE = ' .
                 $NativeAccountObj->native_account_code .
                 ' to ' . $ReportTemplateAccountGroupCodeObj->report_template_account_group_code . PHP_EOL;

            $report_template_mapping_arr = DB::select(
                DB::raw(
                    'SELECT *
                            FROM report_template_mappings
                            WHERE native_account_id= :native_account_id AND 
                                  report_template_account_group_id = :report_template_account_group_id'
                ),
                [
                    'native_account_id'                => $NativeAccountObj->id,
                    'report_template_account_group_id' => $ReportTemplateAccountGroupCodeObj->id,
                ]
            );
        }
        if (count($report_template_mapping_arr) != 1)
        {
            throw new GeneralException('WTF');
        }
        else
        {
            $ReportTemplateMappingObj = $report_template_mapping_arr[0];
        }
        return $ReportTemplateMappingObj;
    }
}