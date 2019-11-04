<?php

namespace App\Waypoint\Models;

use App\Waypoint\HasAttachment;

class CustomReport extends CustomReportModelBase
{
    use HasAttachment;

    const QUARTERLY  = 'quarterly';
    const MONTHLY    = 'monthly';
    const QUARTERS   = [
        'Q1',
        'Q2',
        'Q3',
        'Q4',
    ];
    const MONTHS     = [
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
    ];
    const FILE_TYPES = [
        'pdf',
        'txt',
        'xls',
    ];

    public function toArray(): array
    {
        return [
            'id'                    => $this->id,
            'custom_report_type_id' => $this->custom_report_type_id,
            'period'                => $this->period,
            'year'                  => $this->year,
            'file_type'             => $this->file_type,
            'property_id'           => $this->property_id,
            'property_group_id'     => $this->property_group_id,
            'download_url'          => $this->download_url,
            'model_name'            => self::class,
        ];
    }
}
