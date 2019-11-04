<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\NativeAccount;
use Cache;
use DB;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class NativeAccountRepository
 * @package App\Waypoint\Repositories
 */
class NativeAccountRepository extends NativeAccountRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return NativeAccount::class;
    }

    /**
     * @return []
     */
    public function mappingForClient($client_id)
    {
        $client_mapping_arr = DB::select(
            DB::raw(
                'SELECT 
                        clients.id AS  client_id,
                        clients.name AS  client_name,
                        properties.id AS  property_id,
                        properties.name AS  property_name,
                        native_coas.id AS native_coa_id,
                        native_coas.name AS native_coa_name,
                        native_accounts.native_account_name AS native_account_name,
                        native_accounts.native_account_code AS native_account_code,
                        native_account_types.native_account_type_name AS native_account_type_name,
                        native_account_types.native_account_type_description AS native_account_type_description,  
                        native_account_type_trailers.budgeted_coefficient AS budgeted_coefficient,
						native_account_type_trailers.advanced_variance_coefficient AS advanced_variance_coefficient,
                        report_templates.id AS report_template_id,                    
                        report_templates.report_template_name AS report_template_name,   
                        report_template_account_groups.report_template_account_group_code AS report_template_account_group_code,
                        report_template_account_groups.report_template_account_group_name AS report_template_account_group_name
                    FROM clients
                    JOIN properties ON properties.client_id = clients.id
                    JOIN property_native_coas ON property_native_coas.property_id = properties.id
                    JOIN native_coas ON native_coas.id = property_native_coas.native_coa_id
                    JOIN native_accounts ON native_accounts.native_coa_id = native_coas.id 
                    JOIN native_account_types ON native_accounts.native_account_type_id = native_account_types.id
                    JOIN native_account_type_trailers ON native_account_types.id = native_account_type_trailers.native_account_type_id
                    JOIN report_template_mappings ON report_template_mappings.native_account_id = native_accounts.id
                    JOIN report_template_account_groups ON report_template_mappings.report_template_account_group_id = report_template_account_groups.id
                    JOIN report_templates ON report_template_account_groups.report_template_id = report_templates.id
                    WHERE clients.id = :CLIENT_ID
                    ORDER BY properties.id,report_templates.id,report_template_account_group_name,native_account_name
                '
            ),
            [
                'CLIENT_ID' => $client_id,
            ]
        );
        $client_mapping_arr = array_map(
            function ($val)
            {
                return json_decode(json_encode($val), true);
            }, $client_mapping_arr
        );
        return $client_mapping_arr;

    }

    /**
     * @return []
     */
    public function mappingsPerClientProperty($client_id, $property_id)
    {
        $client_mapping_arr = DB::select(
            DB::raw(
                'SELECT 
                        clients.id AS  client_id,
                        clients.name AS  client_name,
                        properties.id AS  property_id,
                        properties.name AS  property_name,
                        native_coas.id AS native_coa_id,
                        native_coas.name AS native_coa_name,
                        native_accounts.native_account_name AS native_account_name,
                        native_accounts.native_account_code AS native_account_code,
                        native_account_types.native_account_type_name AS native_account_type_name,
                        native_account_types.native_account_type_description AS native_account_type_description,  
                        native_account_type_trailers.budgeted_coefficient AS budgeted_coefficient,
						native_account_type_trailers.advanced_variance_coefficient AS advanced_variance_coefficient,
                        report_templates.id AS report_template_id,                    
                        report_templates.report_template_name AS report_template_name,   
                        report_template_account_groups.report_template_account_group_code AS report_template_account_group_code,
                        report_template_account_groups.report_template_account_group_name AS report_template_account_group_name
                    FROM clients
                    JOIN properties ON properties.client_id = clients.id
                    JOIN property_native_coas ON property_native_coas.property_id = properties.id
                    JOIN native_coas ON native_coas.id = property_native_coas.native_coa_id
                    JOIN native_accounts ON native_accounts.native_coa_id = native_coas.id 
                    JOIN native_account_types ON native_accounts.native_account_type_id = native_account_types.id
                    JOIN native_account_type_trailers ON native_account_types.id = native_account_type_trailers.native_account_type_id
                    JOIN report_template_mappings ON report_template_mappings.native_account_id = native_accounts.id
                    JOIN report_template_account_groups ON report_template_mappings.report_template_account_group_id = report_template_account_groups.id
                    JOIN report_templates ON report_template_account_groups.report_template_id = report_templates.id
                    WHERE 
                        clients.id = :CLIENT_ID
                        AND properties.id = :PROPERTY_ID
                    ORDER BY properties.id,report_templates.id,report_template_account_group_name,native_account_name
                '
            ),
            [
                'CLIENT_ID'   => $client_id,
                'PROPERTY_ID' => $property_id,
            ]
        );
        $client_mapping_arr = array_map(
            function ($val)
            {
                return json_decode(json_encode($val), true);
            }, $client_mapping_arr
        );
        return $client_mapping_arr;
    }
    /**
     * Save a new NativeAccount in repository
     *
     * @param array $attributes
     * @return NativeAccount
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $NativeAccountObj = parent::create($attributes);
        Cache::tags('AdvancedVariance_' . $NativeAccountObj->nativeCoa->client_id)->flush();

        return $NativeAccountObj;
    }

    /**
     * Update a NativeAccount entity in repository by id
     *
     * @param array $attributes
     * @param int $native_account_id
     * @return NativeAccount
     * @throws ValidatorException
     */
    public function update(array $attributes, $native_account_id)
    {
        $NativeAccountObj = parent::update($attributes, $native_account_id);
        Cache::tags('AdvancedVariance_' . $NativeAccountObj->nativeCoa->client_id)->flush();

        return $NativeAccountObj;
    }

    /**
     * Delete a NativeAccount entity in repository by id
     *
     * @param int $native_account_id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($native_account_id)
    {
        $NativeAccountObj = $this->find($native_account_id);
        $result           = parent::delete($native_account_id);
        Cache::tags('AdvancedVariance_' . $NativeAccountObj->nativeCoa->client_id)->flush();

        return $result;
    }
}
