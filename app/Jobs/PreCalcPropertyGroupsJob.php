<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Repositories\PreCalcRepository;
use App\Waypoint\SQSUtil;
use Exception;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Log;

/**
 * Class PreCalcPropertyGroupsJob
 * @package App\Waypoint\Jobs
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class PreCalcPropertyGroupsJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    use DispatchesJobs;

    /** @var integer */
    private $property_group_id;
    /** @var [] */
    private $launch_job_property_group_id_arr = null;
    /** @var  PreCalcRepository */
    public $PreCalcRepositoryObj;

    /**
     * Create a new job instance.
     *
     * PreCalcClientsJob constructor.
     * @param $model_arr
     * @throws JobException
     */
    public function __construct($model_arr)
    {
        foreach ($model_arr as $key => $value)
        {
            $this->$key = $value;
        }
    }

    /**
     * @throws GeneralException
     * @throws JobException
     */
    public function handle()
    {
        try
        {
            if ($this->launch_job_property_group_id_arr)
            {
                foreach ($this->launch_job_property_group_id_arr as $property_group_id)
                {
                    $this->model_arr['model_name']        = PropertyGroup::class;
                    $this->model_arr['property_group_id'] = $property_group_id;
                    $job_class                            = PreCalcPropertyGroupsJob::class;

                    $new_model_arr                                     = $this->model_arr;
                    $new_model_arr['launch_job_property_group_id_arr'] = null;

                    $JobObj           =
                        (new $job_class($new_model_arr))
                            ->onConnection(config('queue.driver', 'sync'))
                            ->onQueue($this->queue);

                    if (config('queue.driver', 'sync') == 'sqs')
                    {
                        $SQSUtil =
                            new SQSUtil(
                                $JobObj,
                                [SQSUtil::QUEUE_ATTR_QUEUENAME => $this->queue]
                            );
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
                         * README README README READMEv
                         */
                        $this->dispatch($JobObj);
                    }
                    else
                    {
                        throw new GeneralException(__CLASS__ . ' at ' . __FILE__ . ':' . __LINE__);
                    }
                }
                return;
            }

            if ( ! PropertyGroup::find($this->property_group_id))
            {
                Log::error('No PropertyGroup ' . $this->property_group_id . ' at ' . __CLASS__ . ':' . __LINE__);
            }

            $this->PreCalcRepositoryObj = App::make(PreCalcRepository::class)->setSuppressEvents(true);
            $this->PreCalcRepositoryObj->PreCalcPropertyGroupsJobProcessor($this->property_group_id);
        }
        catch (GeneralException $e)
        {
            throw  $e;
        }
        catch (Exception $e)
        {
            throw new JobException($e->getMessage(), 500, $e);
        }
    }
}
