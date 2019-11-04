<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use Cache;

/**
 * Class FlushCacheCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class FlushNonSessionCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:flush_non_session_cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush Non-Session Memcache';

    /**
     * FlushCacheCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj in parent::__construct. Messes up code generator
         */
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        parent::handle();

        $this->flushNonSessionCache();

        return true;
    }

    /**
     * @throws GeneralException
     * @throws \BadMethodCallException
     */
    public function flushNonSessionCache()
    {
        if (config('cache.cache_on', false) && ! (config('cache.default', false) == 'memcached'))
        {
            throw new GeneralException('Current cache driver not tag-able');
        }
        if (config('cache.cache_on', false))
        {
            Cache::tags(['Non-Session'])->flush();
        }
        return true;
    }
}