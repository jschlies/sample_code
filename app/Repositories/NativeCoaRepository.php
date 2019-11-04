<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\NativeCoa;
use App;
use Cache;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class NativeCoaRepository
 * @package App\Waypoint\Repositories
 */
class NativeCoaRepository extends NativeCoaRepositoryBase
{
    /**
     * @param array $attributes
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    public function create(array $attributes = [])
    {
        $NativeCoaObj = parent::create($attributes);
        Cache::tags('AdvancedVariance_' . $NativeCoaObj->client_id)->flush();

        return $NativeCoaObj;
    }

    /**
     * Update a NativeCoa entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return NativeCoa
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $NativeCoaObj = parent::update($attributes, $id);
        Cache::tags('AdvancedVariance_' . $NativeCoaObj->client_id)->flush();

        return $NativeCoaObj;
    }

    /**
     * Delete a NativeCoa entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $NativeCoaObj = $this->find($id);
        $result       = parent::delete($id);
        Cache::tags('AdvancedVariance_' . $NativeCoaObj->client_id)->flush();

        return $result;
    }
    /**
     * @return string
     */
    public function model()
    {
        return NativeCoa::class;
    }
}
