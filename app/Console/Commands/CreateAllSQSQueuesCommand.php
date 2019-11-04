<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\SQSUtil;
use AWS;
use Aws\Sqs\SqsClient;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use Exception;
use const PHP_EOL;

/**
 * Class ListClientsCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class CreateAllSQSQueuesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:queue:create_all_sqs_queues';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create all queues as defined in conf';
    /** @var  SqsClient */
    protected $SQSClient;

    /**
     * CreateAllSQSQueuesCommand constructor.
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

        if ( ! config('queue.driver', false) == 'sqs')
        {
            return;
        }

        $this->alert($this->create_all_sqs_queues());

        return true;
    }

    /**
     * @throws GeneralException
     * @throws \InvalidArgumentException
     */
    public function create_all_sqs_queues()
    {
        $return_me = '';
        if (config('queue.driver', 'sync') !== 'sqs')
        {
            return 'queue.driver not equal to  \'sqs\'. No queues created ' . PHP_EOL;
        }

        $config = [
            'version'     => config('queue.connections.sqs.version'),
            'region'      => config('queue.connections.sqs.region'),
            'credentials' => [
                'key'    => config('queue.connections.sqs.key'),
                'secret' => config('queue.connections.sqs.secret'),
            ],
        ];

        $this->SQSClient = AWS::createClient(
            'sqs',
            $config
        );

        foreach ($queue_arr = config('queue.queue_lanes') as $queue)
        {
            $queue_create_request[SQSUtil::QUEUE_ATTR_QUEUENAME] = $queue;

            if (SQSUtil::queue_exists($queue, $this->SQSClient))
            {
                continue;
            }

            /**
             * this is kind of a hack to get around the how/why
             * of how AWS does it's queue ACL's
             */
            try
            {
                $this->SQSClient->getQueueUrl($queue_create_request);
                continue;
            }
            catch (Exception $e)
            {
                if ( ! strpos($e->getMessage(), 'NonExistentQueue'))
                {
                    throw $e;
                }
            }

            $result    = $this->SQSClient->createQueue($queue_create_request);
            $return_me .= 'Created queue ' . $result->get(SQSUtil::QUEUE_ATTR_QUEUEURL) . PHP_EOL;
        }
        return $return_me;
    }
}