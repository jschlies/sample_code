<?php

namespace App\Waypoint\Jobs;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\JobException;
use App\Waypoint\Models\Client;
use App\Waypoint\Repositories\PreCalcRepository;
use DB;
use Exception;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App;
use Log;

/**
 * Class CalculateVariousPropertyListsJob
 * @package App\Waypoint\Jobs
 *
 * See https://laravel.com/docs/5.4/events
 * See https://laravel.com/docs/5.4/queues
 */
class PreCalcClientJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /** @var integer */
    private $client_id;
    /** @var  PreCalcRepository */
    public $PreCalcRepositoryObj;

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
            /**
             * make sure we have something to do
             */
            $resultObjArr = DB::select(
                DB::raw(
                    "
                    SELECT * FROM pre_calc_status where is_soiled and client_id = :CLIENT_ID
                "
                ),
                [
                    'CLIENT_ID' => $this->client_id,
                ]
            );

            if (count($resultObjArr) == 0)
            {
                return;
            }

            if ( ! Client::find($this->client_id))
            {
                Log::error('No Client ' . $this->property_id . ' at ' . __CLASS__ . ':' . __LINE__);
            }
            $this->PreCalcRepositoryObj = App::make(PreCalcRepository::class)->setSuppressEvents(true);
            $this->PreCalcRepositoryObj->PreCalcClientJobProcessor($this->client_id);
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
