<?php

namespace App\Waypoint;

use Cache;

/**
 * Class ResponseUtil
 * @package App\Waypoint
 *
 * Copied from InfyOm\Generator\Utils/ResponseUtil
 *
 */
class ResponseUtil
{
    /**
     * @param $message
     * @param $data
     * @param array $errors
     * @param array $warnings
     * @param array $metadata
     * @return array
     */
    public static function makeResponse($message, $data, $errors = [], $warnings = [], $metadata = [])
    {
        $return_me = [
            'success'  => true,
            'data'     => $data,
            'message'  => $message,
            'errors'   => $errors,
            'warnings' => $warnings,
            'metadata' => $metadata,
        ];
        return $return_me;
    }

    /**
     * @param $message
     * @param array $data
     * @param array $errors
     * @param array $warnings
     * @param array $metadata
     * @return array
     */
    public static function makeError($message, $data = [], array $errors = [], array $warnings = [], array $metadata = [])
    {
        $res = [
            'success'  => false,
            'message'  => $message,
            'errors'   => $errors,
            'warnings' => $warnings,
            'data'     => $data,
            'metadata' => $metadata,
        ];

        return $res;
    }

    /**
     * @return mixed
     */
    public static function get_cache_stats()
    {
        /** @var \Illuminate\Cache\MemcachedStore $CacheStoreObj */
        if ($CacheStoreObj = Cache::getStore())
        {
            return [
                'getStats'      => $CacheStoreObj->getMemcached()->getStats(),
                'getServerList' => $CacheStoreObj->getMemcached()->getServerList(),
                'getVersion'    => $CacheStoreObj->getMemcached()->getVersion()
            ];
        }
    }
}
