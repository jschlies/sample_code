<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Command;
use Cache;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Config;

/**
 * Class FlushCacheCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class FlushEntrustCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:flush_entrust_cache  ';

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

        if (Cache::getStore() instanceof TaggableStore)
        {
            Cache::tags(Config::get('entrust.permission_role_table'))->flush();
            Cache::tags(Config::get('entrust.role_user_table'))->flush();
            Cache::tags(Config::get('entrust.roles_table'))->flush();
        }

        return true;
    }
}