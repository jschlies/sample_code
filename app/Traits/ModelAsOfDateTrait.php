<?php

namespace App\Waypoint;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\Lease;
use App\Waypoint\Repositories\Ledger\LedgerRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

trait ModelAsOfDateTrait
{

    /** @var Carbon|null $model_as_of_date */
    public static $model_as_of_date = null;

    /** @var Carbon|null $model_from_date */
    public static $model_from_date = null;

    /** @var Carbon|null $model_from_date */
    public static $model_to_date = null;

    /** @var bool|null $use_as_of_date */
    public static $use_as_of_date = null;

    /**
     * @return Carbon|null
     */
    public static function get_model_as_of_date($client_id = null): Carbon
    {
        if ( ! self::$model_as_of_date)
        {
            self::set_model_as_of_date(null, $client_id);
        }

        return self::$model_as_of_date;
    }

    /**
     * @param mixed $model_as_of_date
     * @param null $client_id
     * @return Carbon|mixed
     * @throws GeneralException
     * @throws \InvalidArgumentException
     */
    public static function set_model_as_of_date($model_as_of_date = null, $client_id = null)
    {
        /**
         * once self::$use_as_of_date is flipped )to true(explisitly or via call to set_model_as_of_date(),
         * all calculations involving self::$model_to_date and self::$model_from_date will fail.
         */
        self::$use_as_of_date = true;

        if (is_object($model_as_of_date))
        {
            /** simple case */
            if (is_subclass_of($model_as_of_date, Carbon::class) || get_class($model_as_of_date) == Carbon::class)
            {
                self::$model_as_of_date = $model_as_of_date;
                return;
            }
            if (is_subclass_of($model_as_of_date, Request::class) || get_class($model_as_of_date) == Request::class)
            {
                $input = $model_as_of_date->all();
                if (isset($input['lease_as_of_date']))
                {
                    $model_as_of_date = $input['lease_as_of_date'];
                }
                elseif (isset($input['advanced_variance_as_of_date']))
                {
                    $model_as_of_date = $input['advanced_variance_as_of_date'];
                }
                elseif (isset($input['as_of_date']))
                {
                    $model_as_of_date = $input['as_of_date'];
                }
                else
                {
                    $model_as_of_date = null;
                }
            }
        }
        /**
         * at this point $model_as_of_date better be a string or null
         * since no $as_of_date passed , get clients as of date from ledger
         */
        if ( ! $model_as_of_date && $client_id)
        {
            try
            {
                /** @var LedgerRepository $LedgerRepositoryObj */
                $LedgerRepositoryObj    = App::make(LedgerRepository::class);
                self::$model_as_of_date = $LedgerRepositoryObj->get_client_asof_date($client_id);
            }
            catch (Exception $e)
            {
                self::$model_as_of_date = Carbon::now();
            }
        }
        /** nothing passed??? ok, today */
        elseif ( ! $model_as_of_date && ! $client_id)
        {
            self::$model_as_of_date = Carbon::now();
        }
        /** $model_as_of_date is a string soooooo */
        elseif (is_string($model_as_of_date))
        {
            $as_of_date_arr = explode('-', $model_as_of_date);

            if (count($as_of_date_arr) !== 3)
            {
                throw new GeneralException('Invalid date format mm-dd-yyyy at ' . __FILE__ . ':' . __LINE__);
            }
            self::$model_as_of_date = Carbon::create($as_of_date_arr[2], $as_of_date_arr[0], $as_of_date_arr[1]);
        }
        else
        {
            throw new GeneralException('Invalid date format mm-dd-yyyy at ' . __FILE__ . ':' . __LINE__);
        }
        return self::$model_as_of_date;
    }

    /**
     * @return Carbon|null
     */
    public static function get_model_from_date(): Carbon
    {
        if (self::$use_as_of_date === true)
        {
            throw new GeneralException(
                'You may not update model_from_date while use_as_of_date switch is true. 
                Once self::$use_as_of_date is flipped (explisitly or via call to set_model_as_of_date() )
                to true, calls to self::get_model_to_date and self::get_model_from_date will return null. 
                further self::set_model_to_date and self::set_model_from_date will fail'
            );
        }

        /**
         * if self::$use_as_of_date or $model_from_date is unset, return self::get_model_as_of_date()
         */
        if (self::$use_as_of_date || ! self::$model_from_date)
        {
            return self::get_model_as_of_date();
        }

        return self::$model_from_date;
    }

    /**
     * @param Carbon|null $model_from_date
     * @return Carbon|mixed
     * @throws GeneralException
     * @throws \InvalidArgumentException
     */
    public static function set_model_from_date($model_from_date = null)
    {
        /**
         * once self::$use_as_of_date is flipped (explisitly or via call to set_model_as_of_date() )to true,
         * all calculations involving self::$model_to_date and self::$model_from_date will fail.
         */
        if (self::$use_as_of_date === true)
        {
            throw new GeneralException(
                'You may not update model_from_date while use_as_of_date switch is true. 
                Once self::$use_as_of_date is flipped (explisitly or via call to set_model_as_of_date() )
                to true, calls to self::get_model_to_date and self::get_model_from_date will return null. 
                further self::set_model_to_date and self::set_model_from_date will fail'
            );
        }
        self::$use_as_of_date = false;

        if (is_object($model_from_date))
        {
            /** simple case */
            if (is_subclass_of($model_from_date, Carbon::class) || get_class($model_from_date) == Carbon::class)
            {
                self::$model_from_date = $model_from_date;
                return;
            }
            if (is_subclass_of($model_from_date, Request::class) || get_class($model_from_date) == Request::class)
            {
                $input = $model_from_date->all();
                if (isset($input['lease_from_date']))
                {
                    $model_from_date = $input['lease_from_date'];
                }
                elseif (isset($input['advanced_variance_from_date']))
                {
                    $model_from_date = $input['advanced_variance_from_date'];
                }
                elseif (isset($input['from_date']))
                {
                    $model_from_date = $input['from_date'];
                }
                else
                {
                    self::$model_from_date = Carbon::create(1970, 1, 1);
                    return self::$model_from_date;
                }
            }
        }
        /**
         * at this point $model_from_date better be a string
         */
        if (is_string($model_from_date))
        {
            $date_arr = explode('-', $model_from_date);

            if (count($date_arr) !== 3)
            {
                throw new GeneralException('Invalid date format mm-dd-yyyy at ' . __FILE__ . ':' . __LINE__);
            }
            self::$model_from_date = Carbon::create($date_arr[2], $date_arr[0], $date_arr[1]);
        }
        else
        {
            throw new GeneralException('Invalid date format mm-dd-yyyy at ' . __FILE__ . ':' . __LINE__);
        }
        return self::$model_from_date;
    }

    /**
     * @return Carbon|null
     */
    public static function get_model_to_date(): Carbon
    {
        if (self::$use_as_of_date === true)
        {
            throw new GeneralException(
                'You may not update model_from_date while use_as_of_date switch is true. 
                Once self::$use_as_of_date is flipped (explisitly or via call to set_model_as_of_date() )
                to true, calls to self::get_model_to_date and self::get_model_from_date will return null. 
                further self::set_model_to_date and self::set_model_from_date will fail'
            );
        }
        /**
         * if self::$use_as_of_date or $model_to_date is unset, return self::get_model_as_of_date()
         */
        if (self::$use_as_of_date || ! self::$model_from_date)
        {
            return self::get_model_as_of_date();
        }

        return self::$model_to_date;
    }

    /**
     * @param Carbon|null $model_from_date
     * @return Carbon|mixed
     * @throws GeneralException
     * @throws \InvalidArgumentException
     */
    public static function set_model_to_date($model_to_date = null)
    {
        /**
         * once self::$use_as_of_date is flipped )to true(explisitly or via call to set_model_as_of_date(),
         * all calculations involving self::$model_to_date and self::$model_from_date will fail.
         */
        if (self::$use_as_of_date === true)
        {
            throw new GeneralException(
                'You may not update model_from_date while use_as_of_date switch is true. 
                Once self::$use_as_of_date is flipped (explisitly or via call to set_model_as_of_date() )
                to true, calls to self::get_model_to_date and self::get_model_from_date will return null. 
                further self::set_model_to_date and self::set_model_from_date will fail'
            );
        }
        self::$use_as_of_date = false;

        if (is_object($model_to_date))
        {
            /** simple case */
            if (is_subclass_of($model_to_date, Carbon::class) || get_class($model_to_date) == Carbon::class)
            {
                self::$model_to_date = $model_to_date;
                return;
            }
            elseif (is_subclass_of($model_to_date, Request::class) || get_class($model_to_date) == Request::class)
            {
                $input = $model_to_date->all();
                if (isset($input['lease_to_date']))
                {
                    $model_to_date = $input['lease_to_date'];
                }
                elseif (isset($input['advanced_variance_to_date']))
                {
                    $model_to_date = $input['advanced_variance_to_date'];
                }
                elseif (isset($input['to_date']))
                {
                    $model_to_date = $input['to_date'];
                }
                else
                {
                    self::$model_to_date = Carbon::create(2100, 1, 1);
                    return self::$model_to_date;
                }
            }
        }

        /**
         * at this point $model_from_date better be a string with format of mm-dd-yyyy
         */
        if (is_string($model_to_date))
        {
            $date_arr = explode('-', $model_to_date);

            if (count($date_arr) !== 3)
            {
                throw new GeneralException('Invalid date format mm-dd-yyyy at ' . __FILE__ . ':' . __LINE__);
            }
            self::$model_to_date = Carbon::create($date_arr[2], $date_arr[0], $date_arr[1]);
        }
        else
        {
            throw new GeneralException('Invalid date format mm-dd-yyyy at ' . __FILE__ . ':' . __LINE__);
        }
        return self::$model_to_date;
    }

    /**
     * the presumption here is that a null $this->model_start_date is presumed to by Jan 1, 1970 and a
     * null is presumed to be Dec 31,9999
     *
     * @return bool
     */
    public static function check_model_date_range(Model $ModelInQuestionObj)
    {
        if (is_subclass_of($ModelInQuestionObj, Lease::class) || get_class($ModelInQuestionObj) == Lease::class)
        {
            if (Carbon::make($ModelInQuestionObj->lease_start_date))
            {
                $ModelFromDateObj = Carbon::make($ModelInQuestionObj->lease_start_date);
            }
            if (Carbon::make($ModelInQuestionObj->lease_expiration_date))
            {
                $ModelToDateObj = Carbon::make($ModelInQuestionObj->lease_expiration_date);
            }
            $use_as_of_date = Lease::$use_as_of_date;

            if ($use_as_of_date || $use_as_of_date === null)
            {
                $QueryAsOfDateObj = Lease::get_model_as_of_date();
            }
            else
            {
                $QueryToDateObj   = Lease::get_model_to_date();
                $QueryFromDateObj = Lease::get_model_from_date();
            }
        }
        elseif (is_subclass_of($ModelInQuestionObj, AdvancedVariance::class) || get_class($ModelInQuestionObj) == AdvancedVariance::class)
        {
            if (Carbon::make($ModelInQuestionObj->advanced_variance_start_date))
            {
                $ModelFromDateObj = Carbon::make($ModelInQuestionObj->advanced_variance_start_date);
                $ModelToDateObj   = Carbon::make($ModelInQuestionObj->advanced_variance_start_date)->firstOfMonth()->endOfMonth();
            }
            $use_as_of_date = AdvancedVariance::$use_as_of_date;
            if ($use_as_of_date || $use_as_of_date === null)
            {
                $QueryAsOfDateObj = AdvancedVariance::get_model_as_of_date();
            }
            else
            {
                $QueryToDateObj   = AdvancedVariance::get_model_to_date();
                $QueryFromDateObj = AdvancedVariance::get_model_from_date();
            }
        }
        else
        {
            throw new GeneralException('Invalid invocation of check_model_date_range');
        }

        if ( ! isset($ModelFromDateObj))
        {
            $ModelFromDateObj = Carbon::create(1900, 1, 1, 0, 0, 0);
        }
        if ( ! isset($ModelToDateObj))
        {
            $ModelToDateObj = Carbon::create(2100, 1, 1, 0, 0, 0);
        }

        /**
         * if $use_as_of_date === null , we presume one of there:
         * A. that either this method was called in the context of
         *      a Controller and neither Model::set_model_from_date($RequestObj)
         *      nor Model::set_model_to_date($RequestObj) nor  Model::set_model_as_of_date($RequestObj)
         *      were called
         * B. this wan called in context of a unittest, an artisan command or a job
         *
         * In both cases, presume Model::get_model_as_of_date() should be used
         */
        if ($use_as_of_date || $use_as_of_date === null)
        {
            $start_switch      = $ModelFromDateObj ? $ModelFromDateObj->lessThanOrEqualTo($QueryAsOfDateObj) : true;
            $expiration_switch = $ModelToDateObj ? $ModelToDateObj->greaterThanOrEqualTo($QueryAsOfDateObj) : true;
            return
                $start_switch &&
                $expiration_switch;
        }
        else
        {
            $start_switch_1A = $ModelFromDateObj ? $ModelFromDateObj->greaterThanOrEqualTo($QueryFromDateObj) : true;
            $start_switch_2A = $ModelFromDateObj ? $ModelFromDateObj->lessThanOrEqualTo($QueryToDateObj) : true;

            $expiration_switch_1B = $ModelToDateObj ? $ModelToDateObj->greaterThanOrEqualTo($QueryFromDateObj) : true;
            $expiration_switch_2B = $ModelToDateObj ? $ModelToDateObj->lessThanOrEqualTo($QueryToDateObj) : true;

            $start_switch_1C = $ModelFromDateObj ? $ModelFromDateObj->lessThanOrEqualTo($QueryFromDateObj) : true;
            $start_switch_2C = $ModelToDateObj ? $ModelToDateObj->greaterThanOrEqualTo($QueryToDateObj) : true;

            $return_me =
                $start_switch_1A &&
                $start_switch_2A
                ||
                $expiration_switch_1B &&
                $expiration_switch_2B
                ||
                $start_switch_1C &&
                $start_switch_2C;

            return $return_me;
        }
    }
}