<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;
use App\Waypoint\Repositories\CalculateVariousPropertyListsRepository;
use Exception;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;

/**
 * Class CalculateVariousPropertyListsJob
 * @package App\Waypoint\Jobs
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class CalculateVariousPropertyListsJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var integer */
    private $client_id;
    /** @var  CalculateVariousPropertyListsRepository */
    public $CalculateVariousPropertyListsRepositoryObj;

    /**
     * Create a new job instance.
     *
     * CalculateVariousPropertyListsJob constructor.
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
            $this->CalculateVariousPropertyListsRepositoryObj = App::make(CalculateVariousPropertyListsRepository::class)->setSuppressEvents(true);
            $this->CalculateVariousPropertyListsRepositoryObj->CalculateVariousPropertyListsJobProcessor($this->client_id);
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
