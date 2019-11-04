<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\NativeAccountTypeTrailer;
use Cache;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class NativeAccountTypeTrailerRepository
 * @package App\Waypoint\Repositories
 */
class NativeAccountTypeTrailerRepository extends NativeAccountTypeTrailerRepositoryBase
{
    /**
     * Save a new NativeAccountTypeTrailer in repository
     *
     * @param array $attributes
     * @return NativeAccountTypeTrailer
     * @throws ValidatorException
     */
    public function create(array $attributes)
    {
        $NativeAccountTypeTrailerObj = parent::create($attributes);
        Cache::tags('AdvancedVariance_' . $NativeAccountTypeTrailerObj->nativeCoa->client_id)->flush();

        return $NativeAccountTypeTrailerObj;
    }

    /**
     * Update a NativeAccountTypeTrailer entity in repository by id
     *
     * @param array $attributes
     * @param int $id
     * @return NativeAccountTypeTrailer
     * @throws ValidatorException
     */
    public function update(array $attributes, $id)
    {
        $NativeAccountTypeTrailerObj = parent::update($attributes, $id);
        Cache::tags('AdvancedVariance_' . $NativeAccountTypeTrailerObj->nativeCoa->client_id)->flush();

        return $NativeAccountTypeTrailerObj;
    }

    /**
     * Delete a NativeAccountTypeTrailer entity in repository by id
     *
     * @param int $id
     * @return bool
     * @throws RepositoryException
     */
    public function delete($id)
    {
        $NativeAccountTypeTrailerObj = $this->find($id);
        $result                      = parent::delete($id);
        Cache::tags('AdvancedVariance_' . $NativeAccountTypeTrailerObj->nativeCoa->client_id)->flush();

        return $result;
    }

    /**
     * @return string
     */
    public function model()
    {
        return NativeAccountTypeTrailer::class;
    }
}
