<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Command;
use App;
use App\Waypoint\Exceptions\GeneralException;
use Log;

/**
 * Class DeployNodeCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class DeployNodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:deploy_node';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deploy Node';

    /**
     * DeployNodeCommand constructor.
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
     */
    public function handle()
    {
        if (App::environment() === 'production')
        {
            return;
        }
        parent::handle();
        /**
         * if this fails, try deleting the node_modules dir and run "npm install"
         */
        $this->system_call('yarn install --ignore-engines');
        $this->system_call('yarn run build');
        return true;
    }

    /**
     * @param $call
     * @throws \Exception
     */
    private function system_call($call)
    {
        $output     = [];
        $return_var = 0;
        exec($call, $output, $return_var);
        if ($output)
        {
            $this->alert(implode(PHP_EOL, $output));
            Log::info(implode(PHP_EOL, $output));
        }
        if ($return_var)
        {
            throw new GeneralException(implode('', $output));
        }
    }
}