<?php

namespace App\Waypoint;

use App;

/**
 * Class CanStyleJSONTrait
 */
trait CanStyleJSONTrait
{
    /**
     * @param bool $return_format_arr
     * @return array|object
     */
    public function getStyleJSON($return_format_arr = false)
    {
        if ($return_format_arr)
        {
            return stdToArray(json_decode($this->style_json));
        }
        return (object) json_decode($this->style_json);
    }

    /**
     * @param array $ClientStyleJSON
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws \Exception
     */
    public function setStyleJSON($style_json = [])
    {
        if ( ! is_array($style_json))
        {
            $style_json = stdToArray($style_json);
        }
        /**
         * enforce rule that all config values (at top level) be UPPER CASE
         */
        foreach ($style_json as $key => $value)
        {
            if ( ! preg_match("/^[A-Z_0-9]*$/", $key))
            {
                continue;
            }
            $style_json[strtoupper($key)] = $value;
        }

        $this->style_json = json_encode($style_json);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->save();
    }

    /**
     * @param $style_name
     * @param $style_value
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws \Exception
     */
    public function updateStyle($style_name, $style_value)
    {
        $style_json                          = $this->getStyleJSON(true);
        $style_json[strtoupper($style_name)] = $style_value;
        $this->setStyleJSON($style_json);
    }
}