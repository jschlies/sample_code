<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\User;
use Auth;

/**
 * Class OpportunityRepository
 * @package App\Waypoint\Repositories
 */
class OpportunityRepository extends OpportunityRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return Opportunity::class;
    }

    /**
     * Save a new Opportunity in repository
     *
     * @param array $attributes
     * @return Opportunity
     * @throws GeneralException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        if (
            ! isset($attributes['created_by_user_id']) ||
            ! $attributes['created_by_user_id']
        )
        {
            /**
             * @todo we should be not be calling \Auth::getUser(); in repository layer - see HER-3267
             */
            /** @noinspection PhpUndefinedFieldInspection */
            if (Auth::getUser()->email != User::SUPERUSER_EMAIL)
            {
                /** @noinspection PhpUndefinedFieldInspection */
                $attributes['created_by_user_id'] = Auth::getUser()->id;
            }
            else
            {
                throw new GeneralException('invalid created_by_user_id');
            }
        }
        if (
            ! isset($attributes['opportunity_status']) ||
            ! $attributes['opportunity_status']
        )
        {
            $attributes['opportunity_status'] = Opportunity::OPPORTUNITY_STATUS_DEFAULT;
        }
        if (
            ! isset($attributes['opportunity_priority']) ||
            ! $attributes['opportunity_priority']
        )
        {
            $attributes['opportunity_priority'] = Opportunity::OPPORTUNITY_PRIORITY_DEFAULT;
        }
        if (
            ! isset($attributes['opportunity_priority']) ||
            ! $attributes['opportunity_priority']
        )
        {
            $attributes['opportunity_priority'] = Opportunity::OPPORTUNITY_PRIORITY_DEFAULT;
        }
        if (
            ! isset($attributes['estimated_incentive']) ||
            ! $attributes['estimated_incentive']
        )
        {
            $attributes['estimated_incentive'] = 0;
        }
        if (
            ! isset($attributes['expense_amount']) ||
            ! $attributes['expense_amount']
        )
        {
            $attributes['expense_amount'] = 0;
        }
        $OpportunityObj = parent::create($attributes);
        return $OpportunityObj;
    }
}
