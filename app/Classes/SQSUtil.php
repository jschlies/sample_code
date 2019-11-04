<?php

namespace App\Waypoint;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\QueueException;
use App\Waypoint\Jobs\Job;
use AWS;
use Aws\Sqs\SqsClient;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class SQSUtil
 * @package App\Waypoint
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class SQSUtil
{
    const QUEUE_ATTR_QUEUEURL  = 'QueueUrl';
    const QUEUE_ATTR_QUEUENAME = 'QueueName';

    use DispatchesJobs;

    /** @var SqsClient */
    private $SQSClient = null;

    /** @var Job */
    public $JobObj = null;

    /** @var string */
    public $queue = null;

    /**
     * SQSUtil constructor.
     * @param Job $JobObj
     * @param array $queue
     * @throws QueueException
     */
    public function __construct(Job $JobObj, array $queue)
    {
        try
        {
            $this->JobObj = $JobObj;
            if ( ! isset($queue[self::QUEUE_ATTR_QUEUENAME]))
            {
                throw new GeneralException('Queue does not exist', 500);
            }

            /**
             * when creating queues, cannot pass 'QueueUrl'. Odd since when you
             * check for if a queue exists, you need to pre-calc it.
             */
            if ( ! isset($queue[self::QUEUE_ATTR_QUEUEURL]))
            {
                $queue[self::QUEUE_ATTR_QUEUEURL] = config('queue.connections.sqs.prefix') . config(
                        'queue.connections.sqs.aws_sqs_client_id'
                    ) . '/' . $queue[self::QUEUE_ATTR_QUEUENAME];
                if (filter_var($queue[self::QUEUE_ATTR_QUEUEURL], FILTER_VALIDATE_URL) === false)
                {
                    throw new GeneralException('SQS connection issue', 500);
                }
            }
            $this->queue = $queue;

            $config          = [
                'version'     => config('queue.connections.sqs.version'),
                'region'      => config('queue.connections.sqs.region'),
                'credentials' => [
                    'key'    => config('queue.connections.sqs.key'),
                    'secret' => config('queue.connections.sqs.secret'),
                ],
            ];
            $this->SQSClient = AWS::createClient(
                'sqs', $config

            );
        }
        catch (\Exception $e)
        {
            throw new QueueException(__CLASS__, 404, $e);
        }
    }

    /**
     *
     */
    public function post_to_sqs()
    {
        try
        {
            if ( ! SQSUtil::queue_exists($this->queue[self::QUEUE_ATTR_QUEUENAME], $this->SQSClient))
            {
                /**
                 * when creating queues, cannot pass self::QUEUE_ATTR_QUEUEURL. Odd since when you
                 * check for if a queue exists, you need to pre-calc it.
                 */
                unset($this->queue[self::QUEUE_ATTR_QUEUEURL]);
                $result                                 = $this->SQSClient->createQueue($this->queue);
                $this->queue[self::QUEUE_ATTR_QUEUEURL] = $result->get(self::QUEUE_ATTR_QUEUEURL);
            }

            /**
             * @todo maybe we should also pass in repository into job.... Rt now, going with the Lavarel flow
             */
            $this->dispatch($this->JobObj);
        }
        catch (\Exception $e)
        {
            throw new QueueException(__CLASS__, 404, $e);
        }
    }

    /**
     * @param $queue_name
     * @param SqsClient $SQSClient
     * @return bool
     * @throws GeneralException
     */
    public static function queue_exists($queue_name, SqsClient $SQSClient)
    {
        try
        {
            $queue_create_request[SQSUtil::QUEUE_ATTR_QUEUENAME] = $queue_name;
            /**
             * this is kind of a hack to get around the how/why
             * of how AWS does it's queue ACL's
             */
            try
            {
                $SQSClient->getQueueUrl($queue_create_request);
                return true;
            }
            catch (\Exception $e)
            {
                if ( ! strpos($e->getMessage(), 'NonExistentQueue'))
                {
                    throw new GeneralException('Failed checking aws queue', 500, $e);
                }
            }
            return false;
        }
        catch (\Exception $e)
        {
            throw new GeneralException('SQS connection issue', 500, $e);
        }
    }

    /**
     * @return \App\Waypoint\Jobs\Job
     */
    public function getJob()
    {
        return $this->JobObj;
    }

    /**
     * @param \App\Waypoint\Jobs\Job $JobObj
     */
    public function setJob($JobObj)
    {
        $this->JobObj = $JobObj;
    }

    /**
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param string $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }
}
