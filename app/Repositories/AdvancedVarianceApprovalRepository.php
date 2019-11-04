<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVarianceApproval;

/**
 * Class AdvancedVarianceApprovalRepository
 */
class AdvancedVarianceApprovalRepository extends AdvancedVarianceApprovalRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return AdvancedVarianceApproval::class;
    }

    /**
     * @param array $attributes
     * @return AdvancedVarianceApproval
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        if ( ! isset($attributes['advanced_variance_id']) || ! $attributes['advanced_variance_id'])
        {
            throw new GeneralException('Invalid advanced_variance_id' . ' ' . __FILE__ . ':' . __LINE__);
        }

        $AdvancedVarianceRepositoryObj = \App::make(AdvancedVarianceRepository::class);
        if ( ! $AdvancedVarianceObj = $AdvancedVarianceRepositoryObj->find($attributes['advanced_variance_id']))
        {
            throw new GeneralException('Invalid advanced_variance_id' . ' ' . __FILE__ . ':' . __LINE__);
        }

        if ($AdvancedVarianceObj->locked())
        {
            throw new GeneralException('Advanced variance is locked' . ' ' . __FILE__ . ':' . __LINE__);
        }

        $AdvancedVarianceApprovalObj = parent::create($attributes);

        return $AdvancedVarianceApprovalObj;
    }

    /**
     * @param array $attributes
     * @return AdvancedVarianceApproval
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function update(array $attributes, $id)
    {
        if ( ! $AdvancedVarianceApprovalObj = $this->find($id))
        {
            throw new GeneralException('Invalid advanced_variance_approval_id' . ' ' . __FILE__ . ':' . __LINE__);
        }

        if ($AdvancedVarianceApprovalObj->advancedVariance->locked())
        {
            throw new GeneralException('Advanced variance is locked' . ' ' . __FILE__ . ':' . __LINE__);
        }

        $AdvancedVarianceApprovalObj = parent::create($attributes);

        return $AdvancedVarianceApprovalObj;

    }
}
