<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Command;
use Cache;
use Illuminate\Cache\TaggableStore;

/**
 * Class FlushCacheCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class FlushNonEntrustCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:flush_non_entrust_cache';

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

        foreach ($this->ClientRepositoryObj->all() as $ClientObj)
        {
            if (Cache::getStore() instanceof TaggableStore)
            {
                Cache::tags('Client_' . $ClientObj->id)->flush();
                Cache::tags('Property_' . $ClientObj->id)->flush();
                Cache::tags('PropertyGroup_' . $ClientObj->id)->flush();
                Cache::tags('AccessList_' . $ClientObj->id)->flush();
                Cache::tags('AdvancedVariance_' . $ClientObj->id)->flush();
                Cache::tags('BenchmarkGenerationDate_' . $ClientObj->id)->flush();
                Cache::tags('ProcessRefreshNativeAccountValues_' . $ClientObj->id)->flush();

            }
        }

        return true;
    }
}