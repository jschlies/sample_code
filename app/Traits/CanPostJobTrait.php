<?php

namespace App\Waypoint;

use App;
use Illuminate\Foundation\Bus\DispatchesJobs;

/**
 * Class CanImageJSONTrait
 */
trait CanPostJobTrait
{
    /**
     * @param array $model_arr
     * @param $job_class
     * @param $queue_name
     */
    use DispatchesJobs;
    protected function post_job_to_queue(array $model_arr, $job_class, $queue_name)
    {
        $JobObj = (new $job_class($model_arr))->onConnection(config('queue.driver'))->onQueue($queue_name);
        if (config('queue.driver', 'sync') == 'sqs')
        {
            $SQSUtil = new SQSUtil($JobObj, [SQSUtil::QUEUE_ATTR_QUEUENAME => $queue_name, SQSUtil::QUEUE_ATTR_QUEUEURL => false]);
            $SQSUtil->post_to_sqs();
        }
        elseif (config('queue.driver', 'sync') == 'sync')
        {
            /**
             * README README README README
             * README README README README
             * README README README README
             * README README README README
             * if you're looking at this and wondering "If my .env for QUEUE_DRIVER = 'sqs', why is this config('queue.driver') returning a value of 'sync
             * If running a unit test, check your phpunit.xml!!!
             *
             * README README README README
             * README README README README
             */
            $this->dispatch($JobObj);
        }
        else
        {
            throw new GeneralException(__CLASS__ . ' at ' . __FILE__ . ':' . __LINE__);
        }
    }
}