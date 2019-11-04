<?php

namespace App\Waypoint\Models;

class CustomReportDetail extends CustomReport
{
    public function toArray(): array
    {
        return [
            'id'                    => $this->id,
            'custom_report_type_id' => $this->custom_report_type_id,
            'report_type'           => ucfirst($this->customReportType()->first()->display_name),
            'period_type'           => $this->customReportType()->first()->period_type,
            'entity_type'           => $this->customReportType()->first()->entity_type,
            'period'                => ucfirst($this->period),
            'year'                  => $this->year,
            'file_type'             => $this->file_type,
            'property_id'           => $this->property_id,
            'property_group_id'     => $this->property_group_id,
            'download_url'          => $this->download_url,
            'url'                   => $this->download_url,
            'model_name'            => self::class,
        ];
    }
}
