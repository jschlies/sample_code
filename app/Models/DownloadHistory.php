<?php

namespace App\Waypoint\Models;

/**
 * Class DownloadHistory
 * @package App\Waypoint\Models
 */
class DownloadHistory extends DownloadHistoryModelBase
{
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'id'                 => 'sometimes|integer',
        'original_file_name' => 'sometimes',
        'download_time'      => 'required',
        'download_md5'       => 'required|max:255',
        'download_type'      => 'required|max:255',
        'user_id'            => 'required|integer',
        'data'               => 'sometimes',
    ];

    const HTTP  = 'http';
    const HTTPS = 'https';
    const FTP   = 'https';
    const SFTP  = 'inactive';
    public static $download_type_values = [
        self::HTTP,
        self::HTTPS,
        self::FTP,
        self::SFTP,
    ];

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     */
    public function toArray(): array
    {
        return [
            "id"                 => $this->id,
            'original_file_name' => $this->original_file_name,
            "download_time"      => $this->perhaps_format_date($this->download_time),
            "download_md5"       => $this->download_md5,
            "download_type"      => $this->download_type,
            "user_id"            => $this->user_id,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }
}
