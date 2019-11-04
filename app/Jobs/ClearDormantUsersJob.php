<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\JobException;
use App\Waypoint\Models\Client;
use App\Waypoint\Repositories\ClientRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Models\User;

/**
 * Class ClearDormantUsersJob
 * @package App\Waypoint\Jobs
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class ClearDormantUsersJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var integer */
    private $client_id;
    /** @var  ClientRepository */
    public $ClientRepositoryObj;
    /** @var  UserRepository */
    public $UserRepositoryObj;

    /**
     * Create a new job instance.
     *
     * CalculateVariousPropertyListsJob constructor.
     * @param array $model_arr
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
            $this->ClientRepositoryObj = App::make(ClientRepository::class);
            $this->UserRepositoryObj   = App::make(UserRepository::class);
            /** @var Client $ClientObj */
            $ClientObj = $this->ClientRepositoryObj->find($this->client_id);
            /** @var User $UserObj */
            foreach ($ClientObj->users as $UserObj)
            {
                if (
                    $UserObj->last_login_date &&
                    $UserObj->active_status == User::ACTIVE_STATUS_ACTIVE &&
                    $UserObj->last_login_date->format('U') < (time() - $ClientObj->dormant_user_ttl)
                )
                {
                    $this->UserRepositoryObj->deactivateUsers([$UserObj]);
                    $this->UserRepositoryObj->update(
                        [
                            'dormant_user_date' => Carbon::now()->format('Y-m-d H:i:s'),
                        ],
                        $UserObj->id
                    );
                }
            }
        }
        catch (Exception $e)
        {
            throw new JobException(__CLASS__, 404, $e);
        }
    }
}
