<?php

namespace App\Waypoint;

/**
 * Class ModelDateFormatterTrait
 */
trait ModelDateFormatterTrait
{
    /**
     * @return string
     */
    public static function perhaps_format_date($string_or_carbon_obj)
    {
        return is_object($string_or_carbon_obj) ? $string_or_carbon_obj->format('Y-m-d H:i:s') : $string_or_carbon_obj;
    }
}