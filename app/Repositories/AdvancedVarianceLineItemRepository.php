<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Exceptions\GeneralException;

use App\Waypoint\Models\AdvancedVarianceLineItem;
use Carbon\Carbon;

/**
 * Class AdvancedVarianceLineItemRepository
 */
class AdvancedVarianceLineItemRepository extends AdvancedVarianceLineItemRepositoryBase
{
    public function create(array $attributes)
    {
        if ( ! isset($attributes['advanced_variance_id']) || ! $attributes['advanced_variance_id'])
        {
            throw new GeneralException('Invalid advanced_variance_id' . ' ' . __FILE__ . ':' . __LINE__);
        }

        $AdvancedVarianceRepositoryObj = App::make(AdvancedVarianceRepository::class);
        if ( ! $AdvancedVarianceObj = $AdvancedVarianceRepositoryObj->find($attributes['advanced_variance_id']))
        {
            throw new GeneralException('Invalid advanced_variance_id' . ' ' . __FILE__ . ':' . __LINE__);
        }

        if ($AdvancedVarianceObj->locked())
        {
            throw new GeneralException('Advanced variance is locked' . ' ' . __FILE__ . ':' . __LINE__);
        }

        if ( ! isset($attributes['flagged_via_policy']) || ! $attributes['flagged_via_policy'])
        {
            $attributes['flagged_via_policy'] = false;
        }
        $attributes['flagged_manually']        = false;
        $attributes['flagged_manually_date']   = null;
        $attributes['flagger_user_id']         = null;
        $attributes['resolver_user_id']        = null;
        $attributes['resolved_date']           = null;
        $attributes['explanation_update_date'] = null;

        if ($this->isReportTemplateAccountGroup($attributes))
        {
            if ( ! $this->hasNecessaryFields('report_template_account_group', $attributes))
            {
                throw new GeneralException('Please provide a complete set of paramaters' . __FILE__ . ':' . __LINE__);
            }
        }
        elseif ($this->isNativeAccount($attributes))
        {
            if ( ! $this->hasNecessaryFields('native_account', $attributes))
            {
                throw new GeneralException('Please provide a complete set of paramaters' . __FILE__ . ':' . __LINE__);
            }
        }
        elseif ($this->isCalculatedField($attributes))
        {
            if ( ! $this->hasNecessaryFields('calculated_field', $attributes))
            {
                throw new GeneralException('Please provide a complete set of paramaters' . __FILE__ . ':' . __LINE__);
            }
        }
        else
        {
            throw new GeneralException('Please provide a complete set of paramaters' . __FILE__ . ':' . __LINE__);
        }

        /**
         * @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj
         */
        $AdvancedVarianceLineItemObj = parent::create($attributes);
        /**
         * note how we suppress events here since
         */
        $AdvancedVarianceLineItemObj->check_flagged_via_policy();
        return $AdvancedVarianceLineItemObj;
    }

    /**
     * @param string $type
     * @param array $attributes
     * @return bool
     */
    private function hasNecessaryFields(string $type, array $attributes): bool
    {
        $types          = [
            'calculated_field',
            'report_template_account_group',
            'native_account',
        ];
        $fieldFragments = [
            '_overage_threshold_amount',
            '_overage_threshold_percent',
            '_overage_threshold_operator',
        ];

        foreach ($types as $typeText)
        {
            if ($typeText === $type)
            {
                foreach ($fieldFragments as $fragmentText)
                {
                    if (empty($attributes[$typeText . $fragmentText]))
                    {
                        return false;
                    }
                }
            }
            else
            {
                foreach ($fieldFragments as $fragmentText)
                {
                    if ( ! empty($attributes[$typeText . $fragmentText]))
                    {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param array $attributes
     * @return bool
     */
    private function isCalculatedField(array $attributes): bool
    {
        return
            ! empty($attributes['calculated_field_id'])
            &&
            empty($attributes['report_template_account_group_id'])
            &&
            empty($attributes['native_account_id']);
    }

    /**
     * @param array $attributes
     * @return bool
     */
    private function isNativeAccount(array $attributes): bool
    {
        return
            ! empty($attributes['native_account_id'])
            &&
            empty($attributes['report_template_account_group_id'])
            &&
            empty($attributes['calculated_field_id']);
    }

    /**
     * @param array $attributes
     * @return bool
     */
    private function isReportTemplateAccountGroup(array $attributes): bool
    {
        return
            ! empty($attributes['report_template_account_group_id'])
            &&
            empty($attributes['native_account_id'])
            &&
            empty($attributes['calculated_field_id']);
    }

    public function update(array $attributes, $id)
    {
        /**
         * you cannot update any of these
         */
        if (
            isset($attributes['native_account_id']) ||
            isset($attributes['report_template_account_group_id']) ||
            isset($attributes['explanation_update_date']) ||
            isset($attributes['explainer_id']) ||
            isset($attributes['explanation_type_date']) ||
            isset($attributes['explanation_type_user_id'])
        )
        {
            throw new GeneralException('Please provide a valid set of paramaters' . __FILE__ . ':' . __LINE__);
        }

        $UserRepositoryObj = App::make(UserRepository::class);
        $inputFiltered     = $attributes;
        if (isset($attributes['explanation']))
        {
            if ($attributes['explanation'])
            {
                $inputFiltered['explanation_update_date'] = Carbon::now()->format('Y-m-d H:i:s');
                $inputFiltered['explainer_id']            = $UserRepositoryObj->getLoggedInUser()->id;
            }
            else
            {
                $inputFiltered['explanation_update_date'] = null;
                $inputFiltered['explainer_id']            = null;
            }
        }

        if (array_key_exists('advanced_variance_explanation_type_id', $attributes))
        {

            if ($attributes['advanced_variance_explanation_type_id'])
            {
                $inputFiltered['advanced_variance_explanation_type_id'] = $attributes['advanced_variance_explanation_type_id'];
                $inputFiltered['explanation_type_date']                 = Carbon::now()->format('Y-m-d H:i:s');
                $inputFiltered['explanation_type_user_id']              = $UserRepositoryObj->getLoggedInUser()->id;
            }
            else
            {
                /**
                 * maybe $attributes['advanced_variance_explanation_type_id'] is null
                 */
                $inputFiltered['explanation_type_date']    = null;
                $inputFiltered['explanation_type_user_id'] = null;
            }
        }

        $AdvancedVarianceLineItemObj = parent::update($inputFiltered, $id);
        $AdvancedVarianceLineItemObj->check_flagged_via_policy();

        return $AdvancedVarianceLineItemObj;
    }

    /**
     * @return string
     */
    public function model()
    {
        return AdvancedVarianceLineItem::class;
    }
}
