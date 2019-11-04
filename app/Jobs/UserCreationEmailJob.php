<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\JobException;
use Exception;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use App\Waypoint\Repositories\UserRepository;

/**
 * Class UserCreationEmailJob
 * @package App\Waypoint\Jobs
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class UserCreationEmailJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var integer */
    private $client_id;
    /** @var  UserRepository */
    public $UserRepositoryObj;

    /**
     * Create a new job instance.
     *
     * UserCreationEmailJob constructor.
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
     * @throws JobException
     */
    public function handle()
    {
        try
        {
            // code to email created user goes here
        }
        catch (Exception $e)
        {
            throw new JobException(__CLASS__, 404, $e);
        }
    }
}
