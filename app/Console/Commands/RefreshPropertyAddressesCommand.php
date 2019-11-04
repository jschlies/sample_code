<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\SmartyStreetsException;
use App\Waypoint\Models\Property;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\SpreadsheetCollection;
use ArrayObject;
use DB;
use Exception;
use FireEngineRed\SmartyStreetsLaravel\SmartyStreetsService;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;

/**
 * Class AlterClientConfigCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class RefreshPropertyAddressCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:property_address:refresh  
                        {--client_id= : client_id} 
                        {--property_id= : property_id} 
                        {--dry_run=0 : dry_run (1 or 0)} 
                        ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh property addresses per client and (optionally) per property.';

    /**
     * AlterClientConfigCommand constructor.
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
     * @todo push this logic into a repository
     */
    public function handle()
    {
        parent::handle();

        if ( ! $client_id = $this->option('client_id'))
        {
            $client_id = null;
        }
        if ( ! $property_id = $this->option('property_id'))
        {
            $property_id = null;
        }
        if ($this->option('dry_run'))
        {
            if (
                $this->option('dry_run') !== 0 ||
                $this->option('dry_run') !== 1
            )
            {
                throw new GeneralException("dry_run = 1 or 0", 500);
            }
        }
        else
        {
            $dry_run = $this->option('dry_run');
        }

        if ( ! $client_id and $property_id)
        {
            throw new GeneralException("no client_id / property_id found", 404);
        }
        $this->processRefreshPropertyAddressCommand($client_id, $property_id, $dry_run);

        return true;

    }

    /**
     * @param null $client_id
     * @param null $property_id
     * @param null $month
     * @param null $year
     * @throws GeneralException
     */
    public function processRefreshPropertyAddressCommand($client_id = null, $property_id = null, $dry_run = true)
    {
        /** @var PropertyRepository $PropertyRepositoryObj */
        $PropertyRepositoryObj = App::make(PropertyRepository::class);

        /** @var PropertyRepository $PropertyRepositoryObj */
        $SmartyStreetsServiceObj = new SmartyStreetsService();

        if ( ! $client_id)
        {
            $PropertyObjArr = $PropertyRepositoryObj->orderBy('client_id')->all();
        }
        elseif ($client_id && ! $property_id)
        {
            if ( ! $ClientObj = $this->ClientRepositoryObj->with('properties.advancedVariances')->findWithoutFail($client_id))
            {
                throw new GeneralException("no client_id found", 500);
            }
            $PropertyObjArr = $ClientObj->properties;
        }
        elseif ($client_id && $property_id)
        {
            if ( ! $ClientObj = $this->ClientRepositoryObj->with('properties.advancedVariances')->findWithoutFail($client_id))
            {
                throw new GeneralException("no client_id found", 500);
            }

            $PropertyObjArr = $ClientObj->properties
                ->filter(
                    function ($PropertyObj) use ($property_id)
                    {
                        return $PropertyObj = $property_id;

                    }
                )
                ->first();
        }
        else
        {
            throw new GeneralException("Invalid parame", 500);
        }

        $diffArr   = new SpreadsheetCollection();
        $failedArr = new SpreadsheetCollection();
        foreach ($PropertyObjArr->filter(
            function ($PropertyObj) use ($property_id)
            {
                return $PropertyObj->country == 'United States of America' &&
                       ! $PropertyObj->suppress_address_validation;

            }
        ) as $PropertyObj)
        {
            /**
             * we want to roll back this particular command
             */
            DB::beginTransaction();

            try
            {
                $this->alert('Checking address. $PropertyObj->id = ' . $PropertyObj->id . ' ' . $PropertyObj->name);

                $response = $SmartyStreetsServiceObj->addressQuickVerify(
                    [
                        'street' => $PropertyObj->street_address,
                        'city'   => $PropertyObj->city,
                        'state'  => $PropertyObj->state,
                    ]
                );
                /**
                 * REMEMBER this is the response from smartystreets
                 */
                if ($response)
                {
                    $five_digit_smartystreets_postal_code = null;
                    $five_digit_hermes_postal_code        = null;
                    if (preg_match("/^(\d{5})\-\d{4}$/", $response['components']['zipcode'], $gleaned))
                    {
                        $five_digit_smartystreets_postal_code = $gleaned[1];
                    }
                    if (preg_match("/^(\d{5})\-\d{4}$/", $PropertyObj->postal_code, $gleaned))
                    {
                        $five_digit_hermes_postal_code = $gleaned[1];
                    }

                    if (isset($response['components']['state_abbreviation']))
                    {
                        $attributes['state'] = Property::STATE_ABBR_TO_STATE_NAME[$response['components']['state_abbreviation']];
                    }
                    else
                    {
                        throw new GeneralException('Unknown state ' . $response['components']['state_abbreviation']);
                    }

                    $attributes['street_address']         = isset($response['delivery_line_1']) ? $response['delivery_line_1'] : $PropertyObj->street_address;
                    $attributes['city']                   = isset($response['components']['city']) ? $response['components']['city'] : $PropertyObj->city;
                    $attributes['state_abbr']             = isset($response['components']['state_abbreviation']) ? $response['components']['state_abbreviation'] : $PropertyObj->state_abbr;
                    $attributes['trimmed_postal_code']    = isset($five_digit_smartystreets_postal_code) ? $five_digit_smartystreets_postal_code : $five_digit_hermes_postal_code;
                    $attributes['postal_code']            = isset($response['components']['zipcode']) ? $response['components']['zipcode'] : $PropertyObj->postal_code;
                    $attributes['longitude']              = isset($response['metadata']['longitude']) ? $response['metadata']['longitude'] : $PropertyObj->longitude;
                    $attributes['latitude']               = isset($response['metadata']['latitude']) ? $response['metadata']['latitude'] : $PropertyObj->latitude;
                    $attributes['time_zone']              = isset($response['metadata']['time_zone']) ? $response['metadata']['time_zone'] : $PropertyObj->time_zone;
                    $attributes['smartystreets_metadata'] = isset($response['metadata']) ? json_encode($response['metadata']) : json_encode(new ArrayObject());

                    $attributes['country']                   = Property::THE_LAND_OF_THE_FREE;
                    $attributes['country_abbr']              = Property::THE_LAND_OF_THE_FREE_ABBR;
                    $attributes['address_validation_failed'] = false;

                    if ( ! $dry_run)
                    {
                        $PropertyRepositoryObj->update(
                            $attributes,
                            $PropertyObj->id
                        );
                        $this->alert('   Updating $PropertyObj->id = ' . $PropertyObj->id . ' ' . $PropertyObj->name);
                    }
                }
                else
                {
                    $this->alert('Smartystreets Failed . $PropertyObj->id = ' . $PropertyObj->id . ' ' .
                                 $PropertyObj->name . ' ' .
                                 $PropertyObj->street_address . ' ' .
                                 $PropertyObj->city . ' ' .
                                 $PropertyObj->state . ' '
                    );
                    $PropertyObj->address_validation_failed = true;
                    $PropertyObj->save();

                    if (config('smartystreets.fail_on_failure', true))
                    {
                        throw new SmartyStreetsException('property validation failed ');
                    }
                    $failedArr[] = [
                        'client'         => $PropertyObj->client->name,
                        'name'           => $PropertyObj->name,
                        'property_id'    => $PropertyObj->id,
                        'street_address' => $PropertyObj->street_address,
                        'city'           => $PropertyObj->city,
                        'state'          => $PropertyObj->state,
                        'state_abbr'     => $PropertyObj->state_abbr,
                        'postal_code'    => $PropertyObj->postal_code,
                        'longitude'      => $PropertyObj->longitude,
                        'latitude'       => $PropertyObj->latitude,
                        'time_zone'      => $PropertyObj->time_zone,
                        'country'        => $PropertyObj->country,
                        'country_abbr'   => $PropertyObj->country_abbr,
                    ];
                    continue;
                }

                $diff_found = false;

                /** check diff street_address */
                if (
                    $PropertyObj->street_address !== $attributes['street_address']
                )
                {
                    $this->alert('difference detected for property ' . $PropertyObj->id . ' ' . $PropertyObj->name);
                    $this->alert('    street_address ' . $PropertyObj->street_address . ' vs smartystreets ' . $attributes['street_address']);

                    $diff_found = true;
                }

                /** check diff city */
                if (
                    $PropertyObj->city !== $attributes['city']
                )
                {
                    $this->alert('difference detected for property ' . $PropertyObj->id . ' ' . $PropertyObj->name);
                    $this->alert('    city ' . $PropertyObj->city . ' vs smartystreets ' . $attributes['city']);

                    $diff_found = true;
                }

                /** check diff state */
                if (
                    $PropertyObj->state !== $attributes['state']
                )
                {
                    $this->alert('difference detected for property ' . $PropertyObj->id . ' ' . $PropertyObj->name);
                    $this->alert('    state ' . $PropertyObj->state . ' vs smartystreets ' . $attributes['state']);

                    $diff_found = true;
                }

                /** check diff state_abbr */
                if (
                    $PropertyObj->state_abbr !== $attributes['state_abbr']
                )
                {
                    $this->alert('difference detected for property ' . $PropertyObj->id . ' ' . $PropertyObj->name);
                    $this->alert('    state_abbr ' . $PropertyObj->state_abbr . ' vs smartystreets ' . $attributes['state_abbr']);

                    $diff_found = true;
                }

                /** check diff postal_code */
                if (
                    $five_digit_hermes_postal_code !== $five_digit_smartystreets_postal_code
                )
                {
                    $this->alert('difference detected for property ' . $PropertyObj->id . ' ' . $PropertyObj->name);
                    $this->alert('    postal_code ' . $five_digit_hermes_postal_code . ' vs smartystreets ' . $five_digit_smartystreets_postal_code);

                    $diff_found = true;
                }

                /** check diff postal_code */
                if (
                    $PropertyObj->postal_code !== $attributes['postal_code']
                )
                {
                    $this->alert('difference detected for property ' . $PropertyObj->id . ' ' . $PropertyObj->name);
                    $this->alert('    postal_code ' . $PropertyObj->postal_code . ' vs smartystreets ' . $attributes['postal_code']);

                    $diff_found = true;
                }

                /** check diff longitude */
                if (
                    (integer) $PropertyObj->longitude !== (integer) $attributes['longitude']
                )
                {
                    $this->alert('difference detected for property ' . $PropertyObj->id . ' ' . $PropertyObj->name);
                    $this->alert('    longitude ' . $PropertyObj->longitude . ' vs smartystreets ' . $attributes['longitude']);

                    $diff_found = true;
                }

                /** check diff latitude */
                if (
                    (integer) $PropertyObj->latitude !== (integer) $attributes['latitude']
                )
                {
                    $this->alert('difference detected for property ' . $PropertyObj->id . ' ' . $PropertyObj->name);
                    $this->alert('    latitude ' . $PropertyObj->latitude . ' vs smartystreets ' . $attributes['latitude']);

                    $diff_found = true;
                }

                /** check diff time_zone */
                if (
                    (integer) $PropertyObj->time_zone !== (integer) $attributes['time_zone']
                )
                {
                    $this->alert('difference detected for property ' . $PropertyObj->id . ' ' . $PropertyObj->name);
                    $this->alert('    time_zone ' . $PropertyObj->time_zone . ' vs smartystreets ' . $attributes['time_zone']);

                    $diff_found = true;
                }
                if ($diff_found)
                {
                    $diffArr[] =
                        [
                            'client'      => $PropertyObj->client->name,
                            'property_id' => $PropertyObj->id,

                            'hermes street_address'        => $PropertyObj->street_address,
                            'smartystreets street_address' => $attributes['street_address'],

                            'hermes city'        => $PropertyObj->city,
                            'smartystreets city' => $attributes['city'],

                            'hermes state'        => $PropertyObj->state,
                            'smartystreets state' => $attributes['state'],

                            'hermes postal_code (5 digit)'        => $five_digit_hermes_postal_code,
                            'smartystreets postal_code (5 digit)' => $five_digit_smartystreets_postal_code,

                            'hermes postal_code (9 digit)'        => $PropertyObj->postal_code,
                            'smartystreets postal_code (9 digit)' => $attributes['postal_code'],

                            'hermes longitude'        => $PropertyObj->longitude,
                            'smartystreets longitude' => $attributes['longitude'],

                            'hermes latitude'        => $PropertyObj->latitude,
                            'smartystreets latitude' => $attributes['latitude'],
                        ];
                }
            }
            catch (GeneralException $e)
            {
                DB::rollBack();
                throw $e;
            }
            catch (Exception $e)
            {
                DB::rollBack();
                throw new GeneralException($e->getMessage(), 500, $e);
            }
            catch (Throwable $e)
            {
                $e = new FatalThrowableError($e);
                throw new GeneralException($e->getMessage(), 500, $e);
            }

        }
        $myFile = 'smartystreets_diff_report.' . date("YmdHis");
        $diffArr->toCSVFile($myFile, true);
        $this->alert('--------------------------------------------------------------------------------------------------------');
        $this->alert('----- See ' . $myFile . '  ------');

        $myFile = 'smartystreets_failed_report.' . date("YmdHis");
        $failedArr->toCSVFile($myFile, true);
        $this->alert('----- See ' . $myFile . '  ------');
        $this->alert('--------------------------------------------------------------------------------------------------------');
    }
}
