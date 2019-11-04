<?php

namespace App\Waypoint;

use App;
use function strtoupper;

/**
 * Class CanImageJSONTrait
 */
trait CanImageJSONTrait
{
    /**
     * @param bool $return_format_arr
     * @return array|object
     */
    public function getImageJSON($return_format_arr = false)
    {
        if ($return_format_arr)
        {
            return stdToArray(json_decode($this->image_json));
        }
        return (object) json_decode($this->image_json);
    }

    /**
     * @param array $ClientImageJSON
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws \Exception
     */
    public function setImageJSON($image_json = [])
    {
        if ( ! is_array($image_json))
        {
            $image_json = stdToArray($image_json);
        }
        /**
         * enforce rule that all config values (at top level) be UPPER CASE
         */
        foreach ($image_json as $key => $value)
        {
            if ( ! preg_match("/^[A-Z_0-9]*$/", $key))
            {
                continue;
            }
            $image_json[strtoupper($key)] = $value;
        }
        $this->image_json = json_encode($image_json);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->save();
    }

    /**
     * @param $image_name
     * @param $image_value
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws \Exception
     */
    public function updateImage($image_name, $image_value)
    {
        $image_json = $this->getImageJSON(true);
        if ($image_value !== null)
        {
            $image_json[strtoupper($image_name)] = $image_value;
        }
        else
        {
            if (isset($image_json[strtoupper($image_name)]))
            {
                unset($image_json[strtoupper($image_name)]);
            }
        }
        $this->setImageJSON($image_json);
    }
}