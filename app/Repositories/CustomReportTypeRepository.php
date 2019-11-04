<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\CustomReportType;
use App\Waypoint\Exceptions\GeneralException;

class CustomReportTypeRepository extends CustomReportTypeRepositoryBase
{
    /**
     * @param $period
     * @param $period_type
     * @param $year
     * @return bool
     * @throws GeneralException
     */
    public function validatePayloadWithoutRequestObj($period_type, $period, $year)
    {
        if (nullOrEmpty($period) || nullOrEmpty($year))
        {
            throw new GeneralException('fields cannot be null or empty, please double check your request parameters');
        }
        if ($this->isYearly($period))
        {
            return true;
        }
        if ( ! $this->periodMatchcesPeriodType($period_type, $period))
        {
            throw new GeneralException('period must be one of: ' . implode(', ', CustomReportType::PERIOD_TYPES_TO_PERIODS_LOOKUP[$period_type]));
        }
    }

    /**
     * @param $period
     * @return bool
     */
    public function isYearly($period)
    {
        return strcasecmp($period, CustomReportType::YEARLY_PERIOD_TEXT) == 0;
    }

    /**
     * @param $period
     * @return bool
     */
    public function periodMatchcesPeriodType($period_type, $period)
    {
        return in_array(
            strtolower($period),
            array_map('strtolower', CustomReportType::PERIOD_TYPES_TO_PERIODS_LOOKUP[$period_type])
        );
    }

    /**
     * Configure the Model
     *
     **/
    public function model()
    {
        return CustomReportType::class;
    }
}