<?php

namespace App\Waypoint;

use \Gelf\Message as Gelf_Message;
use \Gelf\Publisher as Gelf_Publisher;
use \Gelf\Transport\TcpTransport as Gelf_Transport_TcpTransport;
use \Psr\Log\LogLevel as Psr_Log_LogLevel;
use App;
use App\Waypoint\Exceptions\ExceptionHandler;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Repositories\ClientRepository;
use Exception;
use function is_array;
use Illuminate\Console\Command as BaseCommand;

/**
 * Class Command
 * @package App\Waypoint
 */
class Command extends BaseCommand
{
    use AllRepositoryTrait;
    use CanPostJobTrait;

    /** @var boolean */
    private $headed_needed = true;

    /** @var \Gelf\Message */
    private $GraylogMessageObj;

    /**
     * Get the console command options.
     *
     * HEY YOU, YEA YOU
     * If your looking at this and scratching your head, there are
     * two ways to define the options of a command.
     * 1. use the signature property which is the method generally used at Waypoint
     * 2. over-ride this. See toto/app/Console/Commands/Inspire.php
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * Get the console command arguments.
     *
     * HEY YOU, YEA YOU
     * If your looking at this and scratching your head, there are
     * two ways to define the arguments of a command.
     * 1. use the signature property which is the method generally used at Waypoint
     * 2. over-ride this. See toto/app/Console/Commands/Inspire.php
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Command constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if (config('services.rollbar.enabled', true))
        {
            Rollbar::init(
                config('services.rollbar'),
                false,
                false
            );
        }
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj. Diddles with code generator
         */
    }

    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $this->loadAllRepositories(false);
    }

    /**
     * @param null $client_id_arr
     * @return Client|mixed
     * @throws GeneralException
     */
    protected function getClientsFromArray($client_id_arr = null)
    {
        if ( ! $this->ClientRepositoryObj)
        {
            $this->ClientRepositoryObj = App::make(ClientRepository::class);
        }

        if ($client_id_arr == 'All')
        {
            return $this->ClientRepositoryObj->all();
        }
        if (preg_match("/^[0-9]+(,[0-9]+)*$/", $client_id_arr))
        {
            $client_id_arr = explode(',', $client_id_arr);
            $ClientObjArr  = $this->ClientRepositoryObj
                ->with('properties')
                ->with('users')
                ->with('accessLists')
                ->findWhereIn('id', $client_id_arr);

            if ($client_id_arr)
            {
                if ( ! count($client_id_arr) == $ClientObjArr->count())
                {
                    throw new GeneralException('client_ids are of unknown clients', '403');
                }
            }
            return $ClientObjArr;
        }
        throw new GeneralException($client_id_arr . 'client_ids is not a comma separated list of integers or \'All\'', '500');
    }

    /**
     * @param $str
     * @return bool|array
     */
    public function stringToTypedValue($str, $return_array = false)
    {
        if (false !== strpos($str, ','))
        {
            $str = array_filter(
                explode(',', $str),
                function ($value)
                {
                    return (bool) $value;
                }
            );
        }
        elseif (strtolower($str) === 'true' || strtolower($str) === 'on' || strtolower($str) === 'yes')
        {
            return true;
        }
        elseif (strtolower($str) === 'false' || strtolower($str) === 'off' || strtolower($str) === 'no')
        {
            return false;
        }

        if ($return_array && ! is_array($str))
        {
            return [$str];
        }
        return $str;
    }

    /**
     * @param $short_message
     * @param $long_message
     * @throws \RuntimeException
     *
     * @todo this needs love See HER-3256
     */
    protected function logToGraylogAndEcho($level, $short_message, $long_message = null)
    {
        if ( ! config('services.graylog.enabled', false))
        {
            $this->alert($short_message . $long_message);
            return;
        }

        $transport = new Gelf_Transport_TcpTransport(
            config('services.graylog.host', 'graylog.waypointbuilding.com'),
            config('graylog_port', '12201')
        );
        // While the UDP transport is itself a publisher, we wrap it in a real Publisher for convenience.
        // A publisher allows for message validation before transmission, and also supports sending
        // messages to multiple backends at once.
        $publisher = new Gelf_Publisher();
        $publisher->addTransport($transport);
        // Now we can create custom messages and publish them

        $this->GraylogMessageObj = new Gelf_Message();
        $this->GraylogMessageObj->setShortMessage($short_message)
                                ->setLevel(Psr_Log_LogLevel::ALERT)
                                ->setFullMessage($long_message ?: $short_message)
                                ->setFacility('hermes')
                                ->setHost(gethostname());
        $this->GraylogMessageObj->setAdditional("artisan_command", $this->name);
        $this->GraylogMessageObj->setAdditional("artisan_command_paraameters", print_r($this->input->getArguments(), 1));
        $this->GraylogMessageObj->setAdditional("artisan_command_options", print_r($this->input->getOptions(), 1));
        $this->GraylogMessageObj->setAdditional("environment", env('APP_ENV'));
        $publisher->publish($this->GraylogMessageObj);

        $this->alert($short_message . $long_message);
    }

    /**
     * @param string $string
     */
    public function alert($string)
    {
        if ($this->headed_needed)
        {
            echo str_repeat('*', strlen($string) + 12) . PHP_EOL;
            $this->headed_needed = false;
        }
        echo '*     ' . $string . PHP_EOL;
        $this->headed_needed = false;
    }

    /**
     * @param mixed $ConsoleOutputObj
     */
    public function setConsoleOutputObj($ConsoleOutputObj): void
    {
        $this->ConsoleOutputObj = $ConsoleOutputObj;
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param \Exception $e
     * @return void
     */
    protected function reportException(Exception $e)
    {
        parent::reportException($e);
        echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
    }

    /**
     * Report the exception to the exception handler.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Exception $e
     * @return void
     */
    protected function renderException($output, Exception $e)
    {
        $this->app[ExceptionHandler::class]->renderForConsole($output, $e);
        echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
    }
}