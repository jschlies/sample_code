<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class GenerateJavaScriptConfigCommand
 * @package App\Waypoint\Console\Commands
 */
class GenerateJavaScriptConfigCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:generate_javaScript_config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate JavaScript env.js Command';

    /**
     * GenerateJavaScriptConfigCommand constructor.
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
     * @throws \Exception
     */
    public function handle()
    {
        parent::handle();

        $javascript_template_arr = file(
            config('waypoint.javascript_config_template_path')
        );

        $env_hash = [];

        if (file_exists(base_path('.env')))
        {
            $env_arr = file(
                base_path('.env')
            );
        }
        else
        {
            /**
             * Just remember that the .env variable in question must be contained in .env with a value. Not
             * necessarily the desired value. See Peter B for details
             */
            throw new GeneralException('failed to find ' . base_path('.env') . ' in GenerateJavaScriptConfigCommand', 500);
        }

        foreach ($env_arr as $env_line)
        {
            if (substr($env_line, 0) == '#')
            {
                continue;
            }

            if (preg_match('/^([A-Z_0-9]*)\=(.*)$/', $env_line, $gleaned))
            {
                $env_hash[$gleaned[1]] = $gleaned[2];
            }
        }

        if ( ! isset($env_hash['POLLING_INTERVAL']))
        {
            $env_hash['POLLING_INTERVAL'] = env('POLLING_INTERVAL', 15000);
        }
        foreach ($env_hash as $token => $env_value)
        {
            if (getenv($token))
            {
                $env_value = getenv($token);
            }
            foreach ($javascript_template_arr as $j => $template_line)
            {
                $template_line               = preg_replace('/__' . $token . '__/', $env_value, $template_line);
                $javascript_template_arr[$j] = $template_line;
            }
        }

        $javascript_file_name = config('waypoint.javascript_config_path');
        $fh = fopen($javascript_file_name, 'w') or die("can't open file");
        fwrite($fh, implode('', $javascript_template_arr));
        fclose($fh);
        return true;
    }
}
