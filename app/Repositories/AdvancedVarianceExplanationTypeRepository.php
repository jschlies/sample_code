<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\AdvancedVarianceExplanationType;

/**
 * Class AdvancedVarianceExplanationTypeRepository
 * @package App\Waypoint\Repositories
 */
class AdvancedVarianceExplanationTypeRepository extends AdvancedVarianceExplanationTypeRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return AdvancedVarianceExplanationType::class;
    }

    /**
     * Save a new entity in repository
     *
     * @param array $attributes
     * @return AdvancedVarianceExplanationType
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        $AdvancedVarianceExplanationTypeObj = parent::create($attributes);
        return $AdvancedVarianceExplanationTypeObj;
    }

    /**
     * Delete a entity in repository by id
     *
     * @param integer $id
     * @return bool
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function delete($id)
    {
        $result = parent::delete($id);
        return $result;
    }
}
