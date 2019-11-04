<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\SpreadsheetCollection;
use Carbon\Carbon;
use DB;
use Exception;
use function get_class;
use function in_array;
use Throwable;
use Webpatser\Uuid\Uuid;

/**
 * Class PropertyGroupCalcDaemonCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class PropertyGroupCalcDaemonCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:daemon:property_group_calc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs the PropertyGroupCalcDaemonCommand daemon';

    /**
     * PropertyGroupCalcDaemonCommand constructor.
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

        if ( ! config('waypoint.group_calc_on', false))
        {
            $this->alert("GROUP_CALC_ON is off");
            exit;
        }
        $amIAlreadyRunning = exec('ps -ef | grep daemon | grep property_group_calc | grep -v grep| grep -v JETBRAINS | wc -l');
        $amIRunningArtisan = exec('ps -ef | grep daemon | grep property_group_calc | grep -v grep| grep -v JETBRAINS | grep -c artisan');
        if ($amIAlreadyRunning > 1 && $amIRunningArtisan > 1)
        {
            $this->alert("already running");
            exit;
        }

        while (1)
        {
            try
            {
                /**
                 * @todo eager load $ClientObj
                 */
                /** @var Client $ClientObj */
                foreach ($this->ClientRepositoryObj->all() as $ClientObj)
                {
                    if (
                        $ClientObj->getConfigValue('FEATURE_GROUP_CALC') === false
                        ||
                        $ClientObj->getConfigValue('FEATURE_GROUP_CALC') === 'false'
                    )
                    {
                        $this->alert('*************************************');
                        $this->alert($ClientObj->name  . ' has group calc turned off so will be skipped');
                        $this->alert('*************************************');
                        continue;
                    }

                    $property_group_ids_arr = [];

                    if ($ClientObj->property_group_force_calc_property_group_ids)
                    {
                        $property_group_ids_arr = explode(',', $ClientObj->property_group_force_calc_property_group_ids);
                    }
                    /**
                     * See HER-1677
                     * just in case
                     */
                    $ClientObj->refresh();

                    if (
                        ! is_subclass_of($ClientObj, Client::class) &&
                        ! ($ClientObj instanceof Client) ||
                        ! method_exists($ClientObj, 'propertyGroups')
                    )
                    {
                        $this->alert('*************************************');
                        $this->alert('*************************************');
                        $this->alert('* client object appears to be of class ' . get_class($ClientObj) . '*****');
                        $this->alert('*************************************');
                        $this->alert('*************************************');
                        throw new GeneralException('* client object appears to be of class ' . get_class($ClientObj) . '*****', 500);
                    }

                    $this->alert('*************************************');
                    $this->alert('Checking Group Calc for ' . $ClientObj->name);
                    if ($ClientObj->property_group_calc_status == Client::PROPERTY_GROUP_CALC_STATUS_IDLE)
                    {
                        $this->alert('Skipping Group Calc because ' . $ClientObj->name . ' property_group_calc_status = PROPERTY_GROUP_CALC_STATUS_IDLE');
                        continue;
                    }
                    if ( ! $ClientObj->propertyGroups->count())
                    {
                        $this->alert('Skipping Group Calc because ' . $ClientObj->name . ' PropertyGroups()->count()=0');
                        continue;
                    }
                    if ( ! $ClientObj->client_id_old)
                    {
                        $this->alert('Skipping Group Calc because ' . $ClientObj->name . ' no old client id');
                        continue;
                    }
                    if ($this->PropertyGroupCalcStatusRepositoryObj->getPropertyGroupCalcStatusArr($ClientObj->id)->count())
                    {
                        $this->alert('Skipping Group Calc because ' . $ClientObj->name . ' getPropertyGroupCalcStatusArr is true ');
                        continue;
                    }

                    /**
                     * we now know know that we need to process $ClientObj and all other
                     * clients that share $ClientObj->client_id_old
                     */
                    $this->alert('Processing Group Calc for ' . $ClientObj->name);

                    $return_me            = new  SpreadsheetCollection();
                    $file_name            = 'PropertyGroupCalcStatusCommand' . Uuid::generate()->__get('string');
                    $property_id_md5_hash = [];

                    /**
                     * shameless hack when requesting a group calc - we need to
                     * send all clients that share a client_id_old. Remember that clients
                     * can share client_id_old. Thus if clientA and clientB have an client_id_old of 6 AND if clientA has
                     * property_group_calc_status = Client::PROPERTY_GROUP_CALC_STATUS_WAITING, both clientA and clientB need to
                     * be processed unless if $property_group_ids_arr. When $property_group_ids_arr is not empty, limit
                     * processing to EXACTLY those property_groups
                     * per Peter B
                     */
                    $InnerClientObjArr = $this->ClientRepositoryObj->with('properties')->findWhere(
                        [
                            'client_id_old' => $ClientObj->client_id_old,
                        ]
                    );

                    /** @var Client $InnerClientObj */
                    foreach ($InnerClientObjArr as $InnerClientObj)
                    {
                        /** @var PropertyGroup $PropertyGroupObj */
                        foreach ($InnerClientObj->propertyGroups as $PropertyGroupObj)
                        {
                            $this->alert('Processing Group Calc for ' . $InnerClientObj->name . ' Property Group ' . $PropertyGroupObj->name . ' Remember that clients can share client_id_old\'s');
                            if ( ! $PropertyGroupObj->properties->count())
                            {
                                $this->alert('Skipping (no properties) Group Calc for ' . $InnerClientObj->name . ' Property Group ' . $PropertyGroupObj->name . ' Remember that clients can share client_id_old\'s');
                                continue;
                            }
                            if ( ! isset($property_id_md5_hash[$PropertyGroupObj->property_id_md5]))
                            {
                                $property_id_md5_hash[$PropertyGroupObj->property_id_md5] = $PropertyGroupObj->id;
                            }
                            $property_id_md5_prev_ref = $property_id_md5_hash[$PropertyGroupObj->property_id_md5] !== $PropertyGroupObj->id ? $property_id_md5_hash[$PropertyGroupObj->property_id_md5] : null;

                            /**
                             * we want to ref any client_groups that match (in terms of $PropertyGroupObj->property_id_md5)
                             */
                            /** @var Property $PropertyObj */

                            if ($ClientObj->property_group_force_recalc)
                            {
                                if ($property_group_ids_arr)
                                {
                                    if (in_array($PropertyGroupObj->id, $property_group_ids_arr))
                                    {
                                        $property_id_md5 = 666;
                                    }
                                    else
                                    {
                                        $property_id_md5 = $PropertyGroupObj->property_id_md5;
                                    }
                                }
                                else
                                {
                                    $property_id_md5 = 666;
                                }
                            }
                            elseif ($ClientObj->property_group_force_first_time_calc)
                            {
                                $property_id_md5 = 555;
                            }
                            else
                            {
                                $property_id_md5 = $PropertyGroupObj->property_id_md5;
                            }

                            foreach ($PropertyGroupObj->properties as $PropertyObj)
                            {
                                $return_me[] = [
                                    'FK_ACCOUNT_CLIENT_ID'     => $InnerClientObj->client_id_old,
                                    'REF_GROUP_ID'             => $PropertyGroupObj->id,
                                    'FK_PROPERTY_ID'           => $PropertyObj->property_id_old,
                                    'property_id_md5'          => $property_id_md5,
                                    'property_id_md5_prev_ref' => $property_id_md5_prev_ref,
                                    'client_id'                => $InnerClientObj->id,
                                    'property_id'              => $PropertyGroupObj->id,
                                    'property_name'            => $PropertyObj->name,
                                    'property_group_name'      => $PropertyGroupObj->name,
                                    'Real_MD5sum'              => $PropertyGroupObj->property_id_md5,
                                ];
                            }
                        }
                    }

                    if ( ! file_exists(realpath(storage_path('exports'))))
                    {
                        mkdir(realpath(storage_path('exports')));
                    }

                    /**
                     * create csv and drop file at realpath(storage_path('exports')) . '/' . $file_name;
                     */
                    $csv_file_name = realpath(storage_path('exports')) . '/' . $file_name . '.csv';
                    $this->alert('Outputting ' . $file_name);
                    $return_me->toCSVFile($file_name, true);
                    unset($return_me);
                    $this->alert('Finished outputting ' . $file_name);

                    /**
                     * This sucks but I can't figure out how to suppress quotes in the
                     * context of "maatwebsite/excel" without suppressing quotes for all
                     * CSV's without heavy lifting. And you know how I feel about heavy lifting.
                     * So I'm left with this.
                     * See http://www.maatwebsite.nl/laravel-excel/docs
                     */
                    $csv_file_contents = file_get_contents($csv_file_name);
                    $csv_file_contents = preg_replace("/\"/", '', $csv_file_contents);
                    file_put_contents($csv_file_name, $csv_file_contents);

                    $script_local = resource_path('bin') . '/copy_groups.sh ';
                    $cmd1         = $script_local . $csv_file_name;

                    if ($sysout = system($cmd1))
                    {
                        throw new GeneralException($cmd1, 500);
                    }

                    /**
                     * @todo - Peter B - please review
                     * ./resources/bin/copy_groups.sh /home/vagrant/repos/toto/prod-portfolio-aug-2016.csv
                     * ./resources/bin/run_groups.sh ./prod-portfolio-aug-2016.csv <GROUP_CALC_BASE_DIR> -s <WAYPOINT_GROUP_CLIENT_'ID'_DB_HOST> -p 3306 -u <WAYPOINT_GROUP_CLIENT_'ID'_DB_USER>  -x '<WAYPOINT_GROUP_CLIENT_'ID'_DB_PASSWORD>' -c '<CLIENT_ID>'
                     */
                    $script_local = resource_path('bin') . '/run_groups.sh';
                    $db_conf      = config('database.connections.mysql_GROUPS_FOR_CLIENT_' . $ClientObj->client_id_old);

                    $group_calc_base_dir = config('waypoint.group_calc_base_dir', null);

                    $cmd2 = $script_local . ' ./' . $file_name . '.csv' . ' ' . $group_calc_base_dir . ' -s ' . $db_conf['host'] . ' -p 3306 ' . ' -u ' . $db_conf['username'] . ' -x ' . $db_conf['password'] . ' -c ' . $ClientObj->client_id_old;

                    // @todo fix this to check return code
                    // ensuring we don't fail as we still have some erroneous output
                    system($cmd2);
                    //if ($sysout = system($cmd2))
                    //{
                    //    throw new GeneralException($cmd2);
                    //}

                    try
                    {
                        foreach ($InnerClientObjArr as $InnerClientObj)
                        {
                            /**
                             * we do not want to trigger ClientUpdateEvent
                             */
                            DB::update(
                                DB::raw(
                                    '
                                        UPDATE clients  
                                            SET 
                                                property_group_calc_status = :PROPERTY_GROUP_CALC_STATUS,
                                                property_group_calc_last_requested = :PROPERTY_GROUP_CALC_LAST_REQUESTED,
                                                property_group_force_recalc = :PROPERTY_GROUP_FORCE_RECALC,
                                                property_group_force_first_time_calc = :PROPERTY_GROUP_FORCE_FIRST_TIME_CALC,
                                                property_group_force_calc_property_group_ids = :PROPERTY_GROUP_FORCE_CALC_PROPERTY_GROUP_IDS,
                                                created_at = NOW(),
                                                updated_at = NOW()
                                            WHERE clients.id = :CLIENT_ID
                                    '
                                ),
                                [
                                    'PROPERTY_GROUP_CALC_STATUS'                   => Client::PROPERTY_GROUP_CALC_STATUS_IDLE,
                                    'PROPERTY_GROUP_CALC_LAST_REQUESTED'           => Carbon::now(),
                                    'PROPERTY_GROUP_FORCE_RECALC'                  => 0,
                                    'PROPERTY_GROUP_FORCE_FIRST_TIME_CALC'         => 0,
                                    'PROPERTY_GROUP_FORCE_CALC_PROPERTY_GROUP_IDS' => null,
                                    'CLIENT_ID'                                    => $InnerClientObj->id,
                                ]
                            );
                        }

                        DB::commit();
                    }
                    catch (GeneralException $e)
                    {
                        $this->alert($e->getMessage());
                        $this->alert($e->getExceptionAsString());
                        $this->reportException($e);
                    }
                    catch (Exception $e)
                    {
                        $this->alert($e->getMessage());
                        $this->alert($e->getExceptionAsString());
                        $this->reportException($e);
                    }
                    catch (Throwable $e)
                    {
                        $this->alert($e->getMessage());
                        $this->alert($e->getExceptionAsString());
                        $this->reportException($e);
                    }
                    unset($InnerClientObjArr);
                }
            }
            catch (GeneralException $e)
            {
                /**
                 * report to sysout, report to RollBar, recover
                 */
                $this->alert($e->getMessage());
                $this->alert($e->getExceptionAsString());
                $this->reportException($e);
            }
            catch (Exception $e)
            {
                /**
                 * report to sysout, report to RollBar, recover
                 */
                $this->alert($e->getMessage());
                $this->alert($e->getExceptionAsString());
                $this->reportException($e);
            }
            catch (Throwable $e)
            {
                /**
                 * report to sysout, report to RollBar, recover
                 */
                $this->alert($e->getMessage());
                $this->alert($e->getExceptionAsString());
                $this->reportException($e);
            }

            /**
             * See HER-1677
             */
            DB::disconnect();

            $this->alert('Sleeping for 30 seconds ');
            sleep(30);
        }
    }
}
