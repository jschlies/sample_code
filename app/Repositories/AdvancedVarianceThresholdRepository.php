<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Model;
use App\Waypoint\Models\AdvancedVarianceThreshold;
use Cache;

/**
 * Class AdvancedVarianceThresholdRepository
 * @package App\Waypoint\Repositories
 */
class AdvancedVarianceThresholdRepository extends AdvancedVarianceThresholdRepositoryBase
{
    /** @var null|[] */
    protected $AdvancedVarianceThresholdArr = null;

    /**
     * Save a new AdvancedVarianceThreshold in repository
     *
     * @param array $attributes
     * @return AdvancedVarianceThreshold
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        if (isset($attributes['native_account_type_id']) && $attributes['native_account_type_id'] !== null)
        {
            if (isset($attributes['native_account_id']) && $attributes['native_account_id'] !== null)
            {
                throw new GeneralException('Cannot pass native_account_type_id and native_account_id');
            }
            if (isset($attributes['report_template_account_group_id']) && $attributes['report_template_account_group_id'] !== null)
            {
                throw new GeneralException('Cannot pass native_account_type_id and report_template_account_group_id');
            }
        }

        /**
         * you must provide at least one triplet
         */
        if (
        ( !
          (
              isset($attributes['native_account_overage_threshold_amount']) && $attributes['native_account_overage_threshold_amount'] &&
              isset($attributes['native_account_overage_threshold_percent']) && $attributes['native_account_overage_threshold_percent'] &&
              isset($attributes['native_account_overage_threshold_operator']) && $attributes['native_account_overage_threshold_operator']
          )
          ||
          ! (
              isset($attributes['report_template_account_group_overage_threshold_amount']) && $attributes['report_template_account_group_overage_threshold_amount'] &&
              isset($attributes['report_template_account_group_overage_threshold_percent']) && $attributes['report_template_account_group_overage_threshold_percent'] &&
              isset($attributes['report_template_account_group_overage_threshold_operator']) && $attributes['report_template_account_group_overage_threshold_operator']
          ))
        )
        {
            throw new GeneralException('Please provide a complete set of paramaters');
        }

        /**
         * if you provide part of a triplet, you must provide whole triplet
         */
        if (
            (
                isset($attributes['native_account_overage_threshold_amount']) ||
                isset($attributes['native_account_overage_threshold_percent']) ||
                isset($attributes['native_account_overage_threshold_percent'])
            )
            &&
            ! (
                isset($attributes['native_account_overage_threshold_amount']) && $attributes['native_account_overage_threshold_amount'] &&
                isset($attributes['native_account_overage_threshold_percent']) && $attributes['native_account_overage_threshold_percent'] &&
                isset($attributes['native_account_overage_threshold_operator']) && $attributes['native_account_overage_threshold_operator']
            )
        )
        {
            throw new GeneralException('Please provide a complete set of paramaters');
        }
        if (
            (
                isset($attributes['report_template_account_group_overage_threshold_amount']) ||
                isset($attributes['report_template_account_group_overage_threshold_percent']) ||
                isset($attributes['report_template_account_group_overage_threshold_operator'])
            )
            &&
            ! (
                isset($attributes['report_template_account_group_overage_threshold_amount']) && $attributes['report_template_account_group_overage_threshold_amount'] &&
                isset($attributes['report_template_account_group_overage_threshold_percent']) && $attributes['report_template_account_group_overage_threshold_percent'] &&
                isset($attributes['report_template_account_group_overage_threshold_operator']) && $attributes['report_template_account_group_overage_threshold_operator']
            )
        )
        {
            throw new GeneralException('Please provide a complete set of paramaters');
        }

        return parent::create($attributes);
    }

    /**
     * @return null
     */
    public function getAdvancedVarianceThresholdArr($client_id)
    {
        if ( ! isset($this->AdvancedVarianceThresholdArr[$client_id]))
        {
            $this->AdvancedVarianceThresholdArr[$client_id] = $this->findWhere(
                [
                    'client_id' => $client_id,
                ]
            );
        }
        return $this->AdvancedVarianceThresholdArr[$client_id];
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param $native_account_id
     * @param $native_account_type_id
     * @return AdvancedVarianceThreshold
     */
    public function getNativeAccountAdvancedVarianceThreshold(
        $client_id,
        $property_id = null,
        $native_account_id = null,
        $native_account_type_id = null
    ): AdvancedVarianceThreshold {

        $minutes                      = config('cache.cache_on', false)
            ? config('cache.cache_tags.AdvancedVariance.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
            :
            0;
        $key = "getNativeAccountAdvancedVarianceThreshold_property_id_" . $property_id . '_native_account_id=' . $native_account_id . '_native_account_type_id_' . $native_account_type_id.'_'.md5(__FILE__.__LINE__);
        $AdvancedVarianceThresholdObj =
            Cache::tags([
                            'AdvancedVariance_' . $client_id,
                            'Non-Session',
                        ])
                 ->remember(
                     $key,
                     $minutes,
                     function () use ($client_id, $property_id, $native_account_id, $native_account_type_id)
                     {
                         if (
                         $AdvancedVarianceThresholdObj =
                             $this->getAdvancedVarianceThresholdArr($client_id)
                                  ->where('client_id', $client_id)
                                  ->where('property_id', $property_id)
                                  ->where('native_account_id', $native_account_id)
                                  ->where('calculated_field_id', null)
                                  ->filter(
                                      function ($AdvancedVarianceThresholdObj)
                                      {
                                          return $AdvancedVarianceThresholdObj->native_account_overage_threshold_amount !== null &&
                                                 $AdvancedVarianceThresholdObj->native_account_overage_threshold_percent !== null &&
                                                 $AdvancedVarianceThresholdObj->native_account_overage_threshold_operator !== null;

                                      }
                                  )
                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                         if ($AdvancedVarianceThresholdObj =
                             $this->getAdvancedVarianceThresholdArr($client_id)
                                  ->where('client_id', $client_id)
                                  ->where('property_id', $property_id)
                                  ->where('native_account_id', null)
                                  ->where('report_template_account_group_id', null)
                                  ->where('native_account_type_id', $native_account_type_id)
                                  ->where('calculated_field_id', null)
                                  ->filter(
                                      function ($AdvancedVarianceThresholdObj)
                                      {
                                          return $AdvancedVarianceThresholdObj->native_account_overage_threshold_amount !== null &&
                                                 $AdvancedVarianceThresholdObj->native_account_overage_threshold_percent !== null &&
                                                 $AdvancedVarianceThresholdObj->native_account_overage_threshold_operator !== null;

                                      }
                                  )
                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                         if ($AdvancedVarianceThresholdObj =
                             $this->getAdvancedVarianceThresholdArr($client_id)
                                  ->where('client_id', $client_id)
                                  ->where('property_id', $property_id)
                                  ->where('report_template_account_group_id', null)
                                  ->where('native_account_id', null)
                                  ->where('native_account_type_id', null)
                                  ->where('calculated_field_id', null)
                                  ->filter(
                                      function ($AdvancedVarianceThresholdObj)
                                      {
                                          return $AdvancedVarianceThresholdObj->native_account_overage_threshold_amount !== null &&
                                                 $AdvancedVarianceThresholdObj->native_account_overage_threshold_percent !== null &&
                                                 $AdvancedVarianceThresholdObj->native_account_overage_threshold_operator !== null;

                                      }
                                  )
                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }

                         if ($AdvancedVarianceThresholdObj =
                             $this->getAdvancedVarianceThresholdArr($client_id)
                                  ->where('client_id', $client_id)
                                  ->where('property_id', null)
                                  ->where('report_template_account_group_id', null)
                                  ->where('native_account_id', $native_account_id)
                                  ->where('native_account_type_id', null)
                                  ->where('calculated_field_id', null)
                                  ->filter(
                                      function ($AdvancedVarianceThresholdObj)
                                      {
                                          return $AdvancedVarianceThresholdObj->native_account_overage_threshold_amount !== null &&
                                                 $AdvancedVarianceThresholdObj->native_account_overage_threshold_percent !== null &&
                                                 $AdvancedVarianceThresholdObj->native_account_overage_threshold_operator !== null;

                                      }
                                  )
                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                         if ($AdvancedVarianceThresholdObj =
                             $this->getAdvancedVarianceThresholdArr($client_id)
                                  ->where('client_id', $client_id)
                                  ->where('property_id', null)
                                  ->where('report_template_account_group_id', null)
                                  ->where('native_account_id', null)
                                  ->where('native_account_type_id', $native_account_type_id)
                                  ->where('calculated_field_id', null)
                                  ->filter(
                                      function ($AdvancedVarianceThresholdObj)
                                      {
                                          return $AdvancedVarianceThresholdObj->native_account_overage_threshold_amount !== null &&
                                                 $AdvancedVarianceThresholdObj->native_account_overage_threshold_percent !== null &&
                                                 $AdvancedVarianceThresholdObj->native_account_overage_threshold_operator !== null;

                                      }
                                  )
                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                         if ($AdvancedVarianceThresholdObj =
                             $this->getAdvancedVarianceThresholdArr($client_id)
                                  ->where('client_id', $client_id)
                                  ->where('property_id', null)
                                  ->where('report_template_account_group_id', null)
                                  ->where('native_account_id', null)
                                  ->where('native_account_type_id', null)
                                  ->where('calculated_field_id', null)
                                  ->filter(
                                      function ($AdvancedVarianceThresholdObj)
                                      {
                                          return $AdvancedVarianceThresholdObj->native_account_overage_threshold_amount !== null &&
                                                 $AdvancedVarianceThresholdObj->native_account_overage_threshold_percent !== null &&
                                                 $AdvancedVarianceThresholdObj->native_account_overage_threshold_operator !== null;
                                      }
                                  )
                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                         throw new GeneralException('Invalid getNativeAccountAdvancedVarianceThreshold call');
                     }
                 );

        return $AdvancedVarianceThresholdObj;
    }

    /**
     * @param integer $client_id
     * @param integer $property_id
     * @param $report_template_account_group_id
     * @param $native_account_type_id
     * @return AdvancedVarianceThreshold
     */
    public function getReportTemplateAccountGroupAdvancedVarianceThreshold(
        $client_id,
        $property_id = null,
        $report_template_account_group_id = null,
        $native_account_type_id = null
    ): AdvancedVarianceThreshold {
        $minutes                      = config('cache.cache_on', false)
            ? config('cache.cache_tags.AdvancedVariance.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
            :
            0;
        $key = "getReportTemplateAccountGroupAdvancedVarianceThreshold_property_id_" . $property_id . '_rtag_id=' . $report_template_account_group_id . '_native_account_type_id=' . $native_account_type_id.'_'.md5(__FILE__.__LINE__);
        $AdvancedVarianceThresholdObj =
            Cache::tags([
                            'AdvancedVariance_' . $client_id,
                            'Non-Session',
                        ])
                 ->remember(
                     $key,
                     $minutes,
                     function () use ($client_id, $property_id, $report_template_account_group_id, $native_account_type_id)
                     {
                         if ($AdvancedVarianceThresholdObj = $this->getAdvancedVarianceThresholdArr($client_id)
                                                                  ->where('client_id', $client_id)
                                                                  ->where('property_id', $property_id)
                                                                  ->where('report_template_account_group_id', $report_template_account_group_id)
                                                                  ->where('native_account_id', null)
                                                                  ->where('native_account_type_id', null)
                                                                  ->where('calculated_field_id', null)
                                                                  ->filter(
                                                                      function ($AdvancedVarianceThresholdObj)
                                                                      {
                                                                          return $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_amount !== null &&
                                                                                 $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_percent !== null &&
                                                                                 $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_operator !== null;

                                                                      }
                                                                  )
                                                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                         if ($AdvancedVarianceThresholdObj = $this->getAdvancedVarianceThresholdArr($client_id)
                                                                  ->where('client_id', $client_id)
                                                                  ->where('property_id', $property_id)
                                                                  ->where('report_template_account_group_id', null)
                                                                  ->where('native_account_id', null)
                                                                  ->where('native_account_type_id', $native_account_type_id)
                                                                  ->where('calculated_field_id', null)
                                                                  ->filter(
                                                                      function ($AdvancedVarianceThresholdObj)
                                                                      {
                                                                          return $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_amount !== null &&
                                                                                 $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_percent !== null &&
                                                                                 $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_operator !== null;

                                                                      }
                                                                  )
                                                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                         if ($AdvancedVarianceThresholdObj = $this->getAdvancedVarianceThresholdArr($client_id)
                                                                  ->where('client_id', $client_id)
                                                                  ->where('property_id', $property_id)
                                                                  ->where('report_template_account_group_id', null)
                                                                  ->where('native_account_id', null)
                                                                  ->where('native_account_type_id', null)
                                                                  ->where('calculated_field_id', null)
                                                                  ->filter(
                                                                      function ($AdvancedVarianceThresholdObj)
                                                                      {
                                                                          return $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_amount !== null &&
                                                                                 $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_percent !== null &&
                                                                                 $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_operator !== null;

                                                                      }
                                                                  )
                                                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }

                         if ($AdvancedVarianceThresholdObj = $this->getAdvancedVarianceThresholdArr($client_id)
                                                                  ->where('client_id', $client_id)
                                                                  ->where('property_id', null)
                                                                  ->where('report_template_account_group_id', $report_template_account_group_id)
                                                                  ->where('native_account_id', null)
                                                                  ->where('native_account_type_id', null)
                                                                  ->where('calculated_field_id', null)
                                                                  ->filter(
                                                                      function ($AdvancedVarianceThresholdObj)
                                                                      {
                                                                          return $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_amount !== null &&
                                                                                 $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_percent !== null &&
                                                                                 $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_operator !== null;

                                                                      }
                                                                  )
                                                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                         if ($AdvancedVarianceThresholdObj = $this->getAdvancedVarianceThresholdArr($client_id)
                                                                  ->where('client_id', $client_id)
                                                                  ->where('property_id', null)
                                                                  ->where('report_template_account_group_id', $report_template_account_group_id)
                                                                  ->where('native_account_id', null)
                                                                  ->where('native_account_type_id', $native_account_type_id)
                                                                  ->where('calculated_field_id', null)
                                                                  ->filter(
                                                                      function ($AdvancedVarianceThresholdObj)
                                                                      {
                                                                          return $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_amount !== null &&
                                                                                 $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_percent !== null &&
                                                                                 $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_operator !== null;

                                                                      }
                                                                  )
                                                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                         if ($AdvancedVarianceThresholdObj = $this->getAdvancedVarianceThresholdArr($client_id)
                                                                  ->where('client_id', $client_id)
                                                                  ->where('property_id', null)
                                                                  ->where('report_template_account_group_id', null)
                                                                  ->where('native_account_id', null)
                                                                  ->where('native_account_type_id', null)
                                                                  ->where('calculated_field_id', null)
                                                                  ->filter(
                                                                      function ($AdvancedVarianceThresholdObj)
                                                                      {
                                                                          return $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_amount !== null &&
                                                                                 $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_percent !== null &&
                                                                                 $AdvancedVarianceThresholdObj->report_template_account_group_overage_threshold_operator !== null;

                                                                      }
                                                                  )
                                                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                         throw new GeneralException('Invalid getReportTemplateAccountGroupAdvancedVarianceThreshold call');
                     }
                 );

        return $AdvancedVarianceThresholdObj;
    }

    /**
     * @param integer $client_id
     * @param null $property_id
     * @param null $calculated_field_id
     * @return mixed
     */
    public function getCalculatedFieldAdvancedVarianceThreshold(
        $client_id,
        $property_id = null,
        $calculated_field_id = null
    ) {
        $minutes                      = config('cache.cache_on', false)
            ? config('cache.cache_tags.AdvancedVariance.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
            :
            0;
        $key = "getCalculatedFieldAdvancedVarianceThreshold_property_id_" . $property_id . '_calculated_field_id=' . $calculated_field_id.'_'.md5(__FILE__.__LINE__);
        $AdvancedVarianceThresholdObj =
            Cache::tags([
                            'AdvancedVariance_' . $client_id,
                            'Non-Session',
                        ])
                 ->remember(
                     $key,
                     $minutes,
                     function () use ($client_id, $property_id, $calculated_field_id)
                     {
                         if ($AdvancedVarianceThresholdObj =
                             $this->getAdvancedVarianceThresholdArr($client_id)
                                  ->where('client_id', $client_id)
                                  ->where('property_id', $property_id)
                                  ->where('report_template_account_group_id', null)
                                  ->where('native_account_id', null)
                                  ->where('native_account_type_id', null)
                                  ->where('calculated_field_id', $calculated_field_id)
                                  ->filter(
                                      function ($AdvancedVarianceThresholdObj)
                                      {
                                          return $AdvancedVarianceThresholdObj->calculated_field_overage_threshold_amount !== null &&
                                                 $AdvancedVarianceThresholdObj->calculated_field_overage_threshold_percent !== null &&
                                                 $AdvancedVarianceThresholdObj->calculated_field_overage_threshold_operator !== null;

                                      }
                                  )
                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                         if ($AdvancedVarianceThresholdObj =
                             $this->getAdvancedVarianceThresholdArr($client_id)
                                  ->where('client_id', $client_id)
                                  ->where('property_id', null)
                                  ->where('report_template_account_group_id', null)
                                  ->where('native_account_id', null)
                                  ->where('native_account_type_id', null)
                                  ->where('calculated_field_id', $calculated_field_id)
                                  ->filter(
                                      function ($AdvancedVarianceThresholdObj)
                                      {
                                          return $AdvancedVarianceThresholdObj->calculated_field_overage_threshold_amount !== null &&
                                                 $AdvancedVarianceThresholdObj->calculated_field_overage_threshold_percent !== null &&
                                                 $AdvancedVarianceThresholdObj->calculated_field_overage_threshold_operator !== null;

                                      }
                                  )
                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                         if ($AdvancedVarianceThresholdObj =
                             $this->getAdvancedVarianceThresholdArr($client_id)
                                  ->where('client_id', $client_id)
                                  ->where('property_id', $property_id)
                                  ->where('report_template_account_group_id', null)
                                  ->where('native_account_id', null)
                                  ->where('native_account_type_id', null)
                                  ->where('calculated_field_id', null)
                                  ->filter(
                                      function ($AdvancedVarianceThresholdObj)
                                      {
                                          return $AdvancedVarianceThresholdObj->calculated_field_overage_threshold_amount !== null &&
                                                 $AdvancedVarianceThresholdObj->calculated_field_overage_threshold_percent !== null &&
                                                 $AdvancedVarianceThresholdObj->calculated_field_overage_threshold_operator !== null;

                                      }
                                  )
                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                         if ($AdvancedVarianceThresholdObj =
                             $this->getAdvancedVarianceThresholdArr($client_id)
                                  ->where('client_id', $client_id)
                                  ->where('property_id', null)
                                  ->where('report_template_account_group_id', null)
                                  ->where('native_account_id', null)
                                  ->where('native_account_type_id', null)
                                  ->where('calculated_field_id', null)
                                  ->filter(
                                      function ($AdvancedVarianceThresholdObj)
                                      {
                                          return $AdvancedVarianceThresholdObj->calculated_field_overage_threshold_amount !== null &&
                                                 $AdvancedVarianceThresholdObj->calculated_field_overage_threshold_percent !== null &&
                                                 $AdvancedVarianceThresholdObj->calculated_field_overage_threshold_operator !== null;

                                      }
                                  )
                                  ->first()
                         )
                         {
                             return $AdvancedVarianceThresholdObj;
                         }
                     }
                 );
        return $AdvancedVarianceThresholdObj;
    }
}

