<?php

namespace App\Waypoint;

use App;
use App\Waypoint\Events\PreCalcHitEvent;
use App\Waypoint\Events\PreCalcMissEvent;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\User;
use DB;
use Exception;
use Throwable;

/**
 * Class CanPreCalcJSONTrait
 */
trait CanPreCalcJSONTrait
{
    use S3Trait;

    /**
     * @param string $pre_calc_name
     * @param mixed $pre_calc_value
     * @return void|null
     */
    public function updatePreCalcValue($pre_calc_name, $pre_calc_value)
    {
        if ($this->suppress_pre_calc_usage())
        {
            return;
        }

        if ( ! is_array($pre_calc_value))
        {
            throw new GeneralException('pre_calc_value must be an array');
        }

        /**
         * might be empty array
         */
        if (
            $pre_calc_value ||
            is_array($pre_calc_value)
        )
        {
            $pre_calc_value = json_encode($pre_calc_value);
        }

        if ($pre_calc_value !== null)
        {
            $s3_location = $pre_calc_name . '_' . waypoint_generate_uuid() . '.json';
            $this->send_to_s3($s3_location, $pre_calc_value, config('waypoint.pre_calc_json_data_store_disc', 's3_pre_calcs'));
        }
        else
        {
            return null;
        }

        try
        {
            $this->updatePreCalcTable($pre_calc_name, $s3_location);
            return;
        }
        catch (GeneralException $e)
        {
            return;
        }
        catch (Exception $e)
        {
            return;
        }
        catch (Throwable $e)
        {
            return;
        }

    }

    /**
     * @param bool $return_format_arr
     * @return array|boolean
     */
    public function getPreCalcValue($pre_calc_name = null, bool $soil_test = false)
    {
        /**
         * $soil_test means "is there an unsoiled preCalc there????? When
         * $soil_test, no need to return data
         */
        if ($this->suppress_pre_calc_usage())
        {
            return null;
        }

        $result = $this->readPreCalcTable($pre_calc_name);
        if ( ! $result)
        {
            if ($soil_test)
            {
                return false;
            }
            else
            {
                event(
                    new PreCalcMissEvent(
                        arrayToObject(
                            [
                                'var1' => 1,
                                'var2' => 1,
                            ]
                        ),
                        [
                                'option1' => 1,
                                'option2' => 1,
                        ]
                    )
                );
                return null;
            }
        }
        if ($soil_test)
        {
            if ($result->is_soiled)
            {
                return false;
            }
            return true;
        }

        $json_decode = json_decode($this->get_from_s3($result->s3_location, config('waypoint.pre_calc_json_data_store_disc', 's3_pre_calcs')));
        if (is_object($json_decode))
        {
        event(
            new PreCalcHitEvent(
                arrayToObject(
                    [
                        'var1' => 1,
                        'var2' => 1,
                    ]
                ),
                [
                                'option1' => 1,
                                'option2' => 1,
                ]
            )
        );
            return stdToArray($json_decode);
        }
        event(
            new PreCalcMissEvent(
                arrayToObject(
                    [
                        'var1' => 1,
                        'var2' => 1,
                    ]
                ),
                [
                                'option1' => 1,
                                'option2' => 1,
                ]
            )
        );
        return $json_decode;
    }

    /**
     * @param bool $return_format_arr
     * @return string
     */
    public function resetPreCalcValueByPatternArr($pre_calc_name_pattern_arr = null)
    {
        if ( ! $pre_calc_name_pattern_arr)
        {
            return;
        }
        list($client_id, $property_id, $property_group_id, $user_id, $where_clause) = $this->get_ids();

        DB::update(
            DB::raw(
                '
                    UPDATE pre_calc_status 
                        SET is_soiled = true,
                            SET soiled_at = now()
                        where
                              pre_calc_status.client_id = :CLIENT_ID AND
                              pre_calc_status.property_id = :PROPERTY_ID AND
                              pre_calc_status.property_group_id = :PROPERTY_GROUP_ID AND
                              pre_calc_status.user_id = :USER_ID AND ' . $where_clause . '
                '
            ),
            [
                'CLIENT_ID'         => $client_id,
                'PROPERTY_ID'       => $property_id,
                'PROPERTY_GROUP_ID' => $property_group_id,
                'USER_ID'           => $user_id,
            ]
        );
        return;
    }

    /**
     * @return array
     */
    public function get_ids()
    {
        $client_id         = null;
        $property_id       = null;
        $property_group_id = null;
        $user_id           = null;
        if (
            get_class($this) == Property::class ||
            is_subclass_of($this, Property::class)
        )
        {
            $property_id = $this->id;
        }
        elseif (
            get_class($this) == Client::class ||
            is_subclass_of($this, Client::class)
        )
        {
            $client_id = $this->id;
        }
        elseif (
            get_class($this) == User::class ||
            is_subclass_of($this, User::class)
        )
        {
            $user_id = $this->id;
        }
        elseif (
            get_class($this) == PropertyGroup::class ||
            is_subclass_of($this, PropertyGroup::class)
        )
        {
            $property_group_id = $this->id;
        }
        else
        {
            throw new App\Waypoint\Exceptions\GeneralException('Invalid class type in :' . __FILE__ . ':' . __LINE__);
        }
        /** @noinspection PhpUndefinedFieldInspection */
        $where_clause = '  ';
        if ($client_id == null)
        {
            $where_clause .= ' ISNULL(pre_calc_status.client_id) AND';
        }
        else
        {
            $where_clause .= ' pre_calc_status.client_id =  ' . $client_id . ' AND ';
        }
        if ($property_id == null)
        {
            $where_clause .= ' ISNULL(pre_calc_status.property_id) AND';
        }
        else
        {
            $where_clause .= ' pre_calc_status.property_id =  ' . $property_id . ' AND ';
        }
        if ($property_group_id == null)
        {
            $where_clause .= ' ISNULL(pre_calc_status.property_group_id) AND';
        }
        else
        {
            $where_clause .= ' pre_calc_status.property_group_id =  ' . $property_group_id . ' AND ';
        }
        if ($user_id == null)
        {
            $where_clause .= ' ISNULL(pre_calc_status.user_id) ';
        }
        else
        {
            $where_clause .= ' pre_calc_status.user_id =  ' . $user_id;
        }
        return [$client_id, $property_id, $property_group_id, $user_id, $where_clause];
    }

    /**
     * @param $pre_calc_name
     * @param $s3_location
     */
    public function updatePreCalcTable($pre_calc_name, $s3_location)
    {
        list($client_id, $property_id, $property_group_id, $user_id, $where_clause) = $this->get_ids();
        $sql = '
                    REPLACE pre_calc_status
                        SET pre_calc_status.client_id = :CLIENT_ID,
                         pre_calc_status.property_id = :PROPERTY_ID,
                         pre_calc_status.property_group_id = :PROPERTY_GROUP_ID,
                         pre_calc_status.user_id = :USER_ID,
                         pre_calc_status.pre_calc_name = :PRE_CALC_NAME,
                         pre_calc_status.is_soiled = false,
                         pre_calc_status.soiled_at = NOW(),
                         pre_calc_status.s3_location = :S3_LOCATION,
                         pre_calc_status.updated_at = NOW()
                ';
        DB::update(
            DB::raw($sql),
            [
                'CLIENT_ID'         => $client_id,
                'PROPERTY_ID'       => $property_id,
                'PROPERTY_GROUP_ID' => $property_group_id,
                'USER_ID'           => $user_id,
                'PRE_CALC_NAME'     => strtoupper($pre_calc_name),
                'S3_LOCATION'       => $s3_location,
            ]
        );
        return;
    }

    /**
     * @param $pre_calc_name
     * @return |null
     */
    public function readPreCalcTable($pre_calc_name)
    {
        list($client_id, $property_id, $property_group_id, $user_id, $where_clause) = $this->get_ids();

        $sql = 'SELECT * from pre_calc_status WHERE ' . $where_clause . " and pre_calc_status.pre_calc_name = '" . strtoupper($pre_calc_name) . "' ";

        $results = DB::select(
            DB::raw($sql)
        );
        if (count($results) > 1)
        {
            throw new GeneralException('PreCalc error at ' . __CLASS__ . ':' . __LINE__);
        }
        if (count($results) == 0)
        {
            return null;
        }
        return $results[0];
    }
}