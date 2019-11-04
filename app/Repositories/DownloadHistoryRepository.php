<?php

namespace App\Waypoint\Repositories;

use Carbon\Carbon;

/**
 * Class DownloadHistoryRepository
 * @package App\Waypoint\Repositories
 */
class DownloadHistoryRepository extends DownloadHistoryRepositoryBase
{

    /**
     * @param array $attributes
     * @return \App\Waypoint\Models\DownloadHistory
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {

        if ( ! array_key_exists('download_time', $attributes))
        {
            $attributes['download_time'] = Carbon::now()->format('Y-m-d H:i:s');
        }
        if (
            ! array_key_exists('download_md5', $attributes)
            &&
            array_key_exists('user_id', $attributes)
            &&
            array_key_exists('download_time', $attributes)
            &&
            array_key_exists('download_type', $attributes)
        )
        {
            $attributes['download_md5'] = md5($attributes['user_id'] . $attributes['download_time'] . $attributes['download_type']);
        }

        return parent::create($attributes);
    }
}
