<?php

namespace App\Waypoint\Models;

use App\Waypoint\Exceptions\GeneralException;

class CustomReportType extends CustomReportTypeModelBase
{

    const YEARLY_PERIOD_TEXT    = 'yearly';
    const MONTHLY_PERIOD_TEXT   = 'monthly';
    const QUARTERLY_PERIOD_TEXT = 'quarterly';
    public static $period_types = [
        self::YEARLY_PERIOD_TEXT,
        self::MONTHLY_PERIOD_TEXT,
        self::QUARTERLY_PERIOD_TEXT,
    ];

    const PERIOD_TYPES_TO_PERIODS_LOOKUP = [
        self::MONTHLY_PERIOD_TEXT   => [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ],
        self::QUARTERLY_PERIOD_TEXT => [
            'Q1',
            'Q2',
            'Q3',
            'Q4',
        ],
        self::YEARLY_PERIOD_TEXT    => [
            2012,
            2013,
            2014,
            2015,
            2016,
            2017,
            2018,
        ],
    ];

    const EXAMPLE_REPORT_TYPES = [
        'Raw Data',
        'CapEx Loan Schedule',
        'Expense Distribution',
        'Variance Report',
        'Leasing Activity',
        'Significant Aged Recievables',
        'Investor Report',
        'Annual Report',
    ];

    const YEARS = [
        2018,
        2017,
        2016,
        2015,
        2014,
    ];

    const DEFAULT_CUSTOM_REPORT_TYPE_NAME       = 'Raw Data';
    const DEFAULT_CUSTOM_REPORT_TYPE_ATTRIBUTES = [
        'name'         => self::DEFAULT_CUSTOM_REPORT_TYPE_NAME,
        'display_name' => self::DEFAULT_CUSTOM_REPORT_TYPE_NAME,
        'period_type'  => self::YEARLY_PERIOD_TEXT,
    ];

    /**
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(self::$baseRules, self::$rules);
        }
        $rules                 = parent::get_model_rules($rules, $object_id);
        $rules['period_type']  = 'sometimes|string|max:255|in:' . implode(',', CustomReportType::$period_types);
        $rules['name']         = 'sometimes|not_in:' . CustomReportType::DEFAULT_CUSTOM_REPORT_TYPE_NAME;
        $rules['display_name'] = 'sometimes|not_in:' . CustomReportType::DEFAULT_CUSTOM_REPORT_TYPE_NAME;
        return $rules;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'           => is_null($this->id) ? 0 : $this->id, // not ideal but handles the exception case for custom report type not added to the database
            'client_id'    => $this->client_id,
            'name'         => $this->name,
            'display_name' => $this->display_name,
            'period_type'  => $this->period_type,
            'entity_type'  => $this->entity_type,
            'model_name'   => self::class,
        ];
    }
}
