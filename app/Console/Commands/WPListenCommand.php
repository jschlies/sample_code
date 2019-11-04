<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\QueueException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessUtils;

/**
 * Class ClientSeederCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class WPListenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * THIS IS NOT READY FOR PROD. Just a handy tool dor DEV's
     *
     * @var string
     */
    protected $signature = 'waypoint:queue:listen_all_queues
                        {--reverse_queues=0 : reverse_queues 0 or 1}
                        {--timeout=1200 : timeout}
                        {--sleep=1 : sleep}
                        {--delay=1 : delay}
                        {--memory=512 : memory}
                        {--tries=3 : tries}
                        {--just_print_command}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'handy little for kicking off all currently configured queues - DOES NOT tailoutput like queue:listen';

    /**
     * WPListenCommand constructor.
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
        parent::handle();

        /**
         * NOTE NOTE NOTE
         * Then using php artisan queue:listen OR php artisan queue:work, note that these will spew exceptions to the error log
         * if queue does not actually exist. Otherwise, they work fine.
         */
        $binary = ProcessUtils::escapeArgument((new PhpExecutableFinder())->find(false));

        if (defined('HHVM_VERSION'))
        {
            $binary .= ' --php';
        }

        if (defined('ARTISAN_BINARY'))
        {
            $artisan = ProcessUtils::escapeArgument(ARTISAN_BINARY);
        }
        else
        {
            $artisan = 'artisan';
        }

        if (config('queue.driver', 'sync') !== 'sqs' && ! $this->option('just_print_command'))
        {
            throw new QueueException('current queue.driver is not sqs thus running this makes no sense');
        }
        $queue_arr = config('queue.queue_lanes');
        if ( ! is_array($queue_arr) || ! count($queue_arr))
        {
            throw new QueueException('no queue_lanesfound');
        }

        $reverse_queues = false;
        if ($this->hasOption('reverse_queues') && $this->option('reverse_queues'))
        {
            $reverse_queues = true;
        }
        if ($reverse_queues)
        {
            rsort($queue_arr);
        }
        else
        {
            sort($queue_arr);
        }

        $queue                    = implode(',', $queue_arr);
        $delay                    = $this->option('delay');
        $memory                   = $this->option('memory');
        $sleep                    = $this->option('sleep');
        $tries                    = $this->option('tries');
        $timeout                  = $this->option('timeout');
        $artisan_command_template = 'queue:listen --queue=%s --delay=%d --memory=%d --sleep=%d --tries=%d --timeout=%s %s';

        $artican_command = sprintf($artisan_command_template, $queue, $delay, $memory, $sleep, $tries, $timeout, config('queue.driver', 'sync'));
        $cmd             = "{$binary} {$artisan} {$artican_command}";

        /**
         * Because operations consumes the sysout of this for it's own purposes, we echo, not $this->>alert()
         */
        echo '********************************************************************************' . PHP_EOL;
        echo '* Hey you!!!! Read Me!!!!!!! Remember that runnning listeners using this script WILL NOT spew sysout.' . PHP_EOL;
        echo '* If this is unwanted, run the the below command directly.' . PHP_EOL;
        echo '********************************************************************************' . PHP_EOL;
        echo '* NOTE NOTE NOTE' . PHP_EOL;
        echo '* If a queue or driver does not actually exist, this script will silently hang.' . PHP_EOL;
        echo '********************************************************************************' . PHP_EOL;
        echo '* Listening to queues:' . PHP_EOL;
        foreach ($queue_arr as $queue_name)
        {
            echo $queue_name . PHP_EOL;
        }
        echo '********************************************************************************' . PHP_EOL;
        echo $cmd . PHP_EOL;
        echo '********************************************************************************' . PHP_EOL;
        if ($this->option('just_print_command'))
        {
            return;
        }
        echo exec($cmd);

        return true;
    }
}
