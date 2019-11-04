<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class EnvCheckCommand
 * @package App\Waypoint\Console\Commands
 */
class EnvCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:env_check  
                        {--silent : No output if .env file passes test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'env_check';

    /**
     * EnvCheckCommand constructor.
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
     * @return mixed
     * @throws \Exception|GeneralException
     */
    public function handle()
    {
        parent::handle();

        $resolver_arr['.env'] = $this->parse_ENV_file(base_path() . '/.env');
        foreach ($resolver_arr['.env'] as $value_name => $value)
        {
            $resolver_arr['all'][$value_name]['.env'] = $value;
        }
        $resolver_arr['.env.example'] = $this->parse_ENV_file(base_path() . '/.env.example');
        foreach ($resolver_arr['.env.example'] as $value_name => $value)
        {
            $resolver_arr['all'][$value_name]['.env.example'] = $value;
        }
        if ( ! $this->option('silent'))
        {
            $this->alert('ENV_VAR_NAME,.env.example,.env,getenv');
        }
        foreach ($resolver_arr['all'] as $value_name => $config_group)
        {
            if ( ! isset($config_group['.env.example']))
            {
                throw new GeneralException('Missing value in .env.example ' . $value_name, 500);
            }
            if ( ! isset($config_group['.env']))
            {
                throw new GeneralException('Missing value in .env ' . $value_name, 500);
            }
            if ( ! $this->option('silent'))
            {
                $this->alert($value_name . ',' . $config_group['.env.example'] . ',' . $config_group['.env'] . ',' . getenv($value_name));
            }
        }

        return true;
    }

    /**
     * @param $fq_file_name
     * @return array
     * @throws GeneralException
     */
    private function parse_ENV_file($fq_file_name)
    {
        $resolver_arr = [];
        foreach (file($fq_file_name) as $env_line)
        {
            if (preg_match("/^\s*\#/", $env_line))
            {
                continue;
            }

            if (preg_match("/^([A-Z_]*)\=(.*)$/", $env_line, $gleaned))
            {
                if (isset($resolver_arr[$gleaned[1]]))
                {
                    throw new GeneralException($gleaned[1] . ' is twice defined in ' . $fq_file_name, 500);
                }
                $resolver_arr[$gleaned[1]] = $gleaned[2];
            }
        }
        return $resolver_arr;
    }
}
