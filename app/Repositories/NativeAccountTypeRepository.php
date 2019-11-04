<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\NativeAccountType;

use Cache;
use Illuminate\Container\Container as Application;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class NativeAccountTypeDetailRepository
 * @package App\Waypoint\Repositories
 */
class NativeAccountTypeRepository extends NativeAccountTypeRepositoryBase
{
    /**
     * @var NativeAccountTypeTrailerRepository
     */
    private $NativeAccountTypeTrailerRepositoryObj;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->NativeAccountTypeTrailerRepositoryObj = App::make(NativeAccountTypeTrailerRepository::class);
    }

    /**
     * @param $client_id
     * @return \App\Waypoint\Collection
     */
    public function getForClient($client_id)
    {
        return $this->findWhere(
            [
                'client_id' => $client_id,
            ]
        );
    }

    /**
     * @param array $attributes
     * @return NativeAccountType
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        if (
            ! isset($attributes['display_name']) &&
            isset($attributes['native_account_type_name'])
        )
        {
            $attributes['display_name'] = $attributes['native_account_type_name'];
        }

        /** @var NativeAccountType $NativeAccountTypeObj */
        $NativeAccountTypeObj = parent::create($attributes);

        /**
         * now create a trailer for each $NativeCoaObj of client in question
         */
        foreach ($NativeAccountTypeObj->client->nativeCoas as $NativeCoaObj)
        {
            $this->NativeAccountTypeTrailerRepositoryObj->create(
                [
                    'native_coa_id'                 => $NativeCoaObj->id,
                    'native_account_type_id'        => $NativeAccountTypeObj->id,
                    'property_id'                   => null,
                    'actual_coefficient'            => 1,
                    'budgeted_coefficient'          => 1,
                    'advanced_variance_coefficient' => 1,
                ]
            );
        }
        Cache::tags('AdvancedVariance_' . $NativeAccountTypeObj->client_id)->flush();
        return $NativeAccountTypeObj;
    }

    /**
     * Update a NativeAccountType entity in repository by id
     *
     * @param array $attributes
     * @param int $native_account_type_id
     * @return NativeAccountType
     * @throws ValidatorException
     */
    public function update(array $attributes, $native_account_type_id)
    {
        $NativeAccountTypeObj = parent::update($attributes, $native_account_type_id);
        Cache::tags('AdvancedVariance_' . $NativeAccountTypeObj->client_id)->flush();

        return $NativeAccountTypeObj;
    }

    /**
     * Delete a NativeAccountType entity in repository by id
     *
     * @param int $native_account_type_id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($native_account_type_id)
    {
        $NativeAccountTypeObj = $this->find($native_account_type_id);
        $result               = parent::delete($native_account_type_id);
        Cache::tags('AdvancedVariance_' . $NativeAccountTypeObj->client_id)->flush();

        return $result;
    }

    /**
     * @return string
     */
    public function model()
    {
        return NativeAccountType::class;
    }
}
