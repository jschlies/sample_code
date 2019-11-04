<?php

namespace App\Waypoint\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
Use Symfony\Component\Console\Input\InputOption;

class Inspire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        $this->comment(PHP_EOL . Inspiring::quote() . PHP_EOL);

        return true;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['clients', 'c', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'WordPress client ID\'s'],
            ['primary', null, InputOption::VALUE_REQUIRED, 'Save model schema to file'],
            //['fieldsFile', null, InputOption::VALUE_REQUIRED, 'Fields input as json file'],
            //['tableName', null, InputOption::VALUE_REQUIRED, 'Table Name'],
            //['fromTable', null, InputOption::VALUE_NONE, 'Generate from existing table'],
            //['save', null, InputOption::VALUE_NONE, 'Save model schema to file'],
            //['primary', null, InputOption::VALUE_REQUIRED, 'Save model schema to file'],
            //['prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for all files'],
            //['paginate', null, InputOption::VALUE_REQUIRED, 'Pagination for index.blade.php'],
            //['dumpOptimized', null, InputOption::VALUE_OPTIONAL, 'Pagination for index.blade.php', true],
        ];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }
}
