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
class FlushCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:flush_cache  
                        {--tags= : entityTags to flush or All (BE CAREFUL).}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush Memcache';

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

        $this->flushCache($this->option('tags'));

        return true;
    }

    /**
     * @param $tags
     * @return bool
     * @throws GeneralException
     */
    public function flushCache($tags)
    {
        if ($tags == 'All')
        {
            if (config('cache.cache_on', false))
            {
                Cache::flush();
            }
        }
        else
        {
            $tag_to_flush_arr = explode(',', $tags);
            foreach ($tag_to_flush_arr as $tag_to_flush)
            {
                if (config('cache.cache_on', false))
                {
                    Cache::tags([$tag_to_flush])->flush();
                }
            }
        }
        return true;
    }
}