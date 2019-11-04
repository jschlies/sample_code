<?php
/**
 * waypoint helper functions. Note that these functions override the functions
 * defined by Laravel's vendor/laravel/framework/src/Illuminate/Support/helpers.php
 */

use App\Waypoint\Collection;
use App\Waypoint\SpreadsheetCollection;

if ( ! function_exists('isZero'))
{
    function isZero($value)
    {
        return $value == 0;
    }
}

if ( ! function_exists('collect_waypoint'))
{
    /**
     * Create a collection from the given value.
     *
     * @param mixed $value
     * @return Collection
     */
    function collect_waypoint($value = null)
    {
        try
        {
            if (
                is_object($value) &&
                (
                    is_subclass_of($value, Collection::class) ||
                    get_class($value) == Collection::class
                )
            )
            {
                return $value;
            }
        }
        catch (Exception$e)
        {
            throw $e;
        }

        return new Collection($value);
    }
}

if ( ! function_exists('collect_waypoint_spreadsheet'))
{
    /**
     * Create a collection from the given value.
     *
     * @param mixed $value
     * @return SpreadsheetCollection
     */
    function collect_waypoint_spreadsheet($value = null)
    {
        try
        {
            if (
                is_object($value) &&
                (
                    is_subclass_of($value, SpreadsheetCollection::class) || get_class($value) == SpreadsheetCollection::class)
            )
            {
                return $value;
            }
        }
        catch (Exception$e)
        {
            throw $e;
        }

        return new SpreadsheetCollection($value);
    }
}

if ( ! function_exists('stdToArray'))
{
    /**
     * @param $obj
     * @return array
     */
    function stdToArray($obj)
    {
        $reaged = (array) $obj;
        foreach ($reaged as $key => &$field)
        {
            if (is_object($field))
            {
                $field = stdToArray($field);
            }
        }
        return $reaged;
    }
}

if ( ! function_exists('nullOrEmpty'))
{
    /**
     * @param $field
     * @return bool
     */
    function nullOrEmpty($field)
    {
        return empty($field) || strtolower($field) == 'null';
    }
}

if ( ! function_exists('get_current_user_rollbar'))
{
    function get_current_user_rollbar()
    {
        $LoggedInUserObj = Auth::getUser();
        if ($LoggedInUserObj)
        {
            return [
                'id'          => (string) $LoggedInUserObj->id,
                'username'    => $LoggedInUserObj->firstname . ' ' . $LoggedInUserObj->lastname . ' (' . $LoggedInUserObj->client_id . ')',
                'email'       => $LoggedInUserObj->email,
                'client_name' => $LoggedInUserObj->client_id,
            ];
        }
        $LoggedInUserObj = \App\Waypoint\Http\ApiGuardAuth::getUser();
        if ($LoggedInUserObj)
        {
            return [
                'id'          => (string) $LoggedInUserObj->id,
                'username'    => $LoggedInUserObj->firstname . ' ' . $LoggedInUserObj->lastname . ' (' . $LoggedInUserObj->client_id . ')',
                'email'       => $LoggedInUserObj->email,
                'client_name' => $LoggedInUserObj->client_id,
            ];
        }
        return null;
    }
}

if ( ! function_exists('is_valid_json'))
{
    /**
     * Validate the attribute is a valid JSON string.
     *
     * @param $value
     * @return bool
     */
    function is_valid_json($value)
    {
        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }
}

if ( ! function_exists('waypoint_generate_uuid'))
{
    /**
     * Validate the attribute is a valid JSON string.
     *
     * @param $value
     * @return bool
     */
    function waypoint_generate_uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}

if ( ! function_exists('waypoint_merge_collections'))
{
    function waypoint_merge_collections(Collection $Collection1Obj, Collection $Collection2Obj): Collection
    {
        $return_me = new Collection();
        foreach ($Collection1Obj as $SomethingObj)
        {
            $return_me[] = $SomethingObj;
        }
        foreach ($Collection2Obj as $SomethingObj)
        {
            $return_me[] = $SomethingObj;
        }
        return $return_me;
    }
}

if ( ! function_exists('in_arrayi'))
{
    /**
     * Case-insensitive in_array() wrapper.
     *
     * @param mixed $needle   Value to seek.
     * @param array $haystack Array to seek in.
     *
     * @return bool
     */
    function in_arrayi($needle, $haystack)
    {
        return in_array(strtolower($needle), array_map('strtolower', $haystack));
    }
}

if ( ! function_exists('waypoint_gzCompressFile'))
{
    /**
     * GZIPs a file on disk (appending .gz to the name)
     *
     * From http://stackoverflow.com/questions/6073397/how-do-you-create-a-gz-file-using-php
     * Based on function by Kioob at:
     * http://www.php.net/manual/en/function.gzwrite.php#34955
     *
     * @param string $source Path to file that should be compressed
     * @param integer $level GZIP compression level (default: 9)
     * @return string New filename (with .gz appended) if success, or false if operation fails
     */
    function waypoint_gzCompressFile($source, $level = 9)
    {
        $dest  = $source . '.gz';
        $mode  = 'wb' . $level;
        $error = false;
        if ($fp_out = gzopen($dest, $mode))
        {
            if ($fp_in = fopen($source, 'rb'))
            {
                while ( ! feof($fp_in))
                {
                    gzwrite($fp_out, fread($fp_in, 1024 * 512));
                }
                fclose($fp_in);
            }
            else
            {
                $error = true;
            }
            gzclose($fp_out);
        }
        else
        {
            $error = true;
        }
        if ($error)
        {
            return false;
        }
        else
        {
            return $dest;
        }
    }
}

if ( ! function_exists('stri_contains'))
{
    /**
     * @param $haystack
     * @param $needle
     * @return bool
     *
     * Substring existance check (case insensitive)
     */
    function stri_contains(string $haystack, string $needle): bool
    {
        return stripos($haystack, $needle) !== false;
    }
}

if ( ! function_exists('stri_equal'))
{
    /**
     * @param $string_one
     * @param $string_two
     * @return bool
     *
     * String characters' equality (case insensitive) which is useful for checking
     * code flags and constants against db data where case may not be standardized
     */
    function stri_equal(string $string_one, string $string_two): bool
    {
        return strtolower($string_one) === strtolower($string_two);
    }
}

if ( ! function_exists('isRegularExpression'))
{
    function isRegularExpression($string)
    {
        return @preg_match($string, '') !== false;
    }
}

if ( ! function_exists('snakeToCapitalCase'))
{
    function snakeToCapitalCase($string): string
    {
        return title_case(str_replace('_', ' ', $string));
    }
}

/**
 * See https://www.if-not-true-then-false.com/2009/php-tip-convert-stdclass-object-to-multidimensional-array-and-convert-multidimensional-array-to-stdclass-object/
 */
if ( ! function_exists('arrayToObject'))
{
    function arrayToObject($d)
    {
        if (is_array($d))
        {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return (object) array_map(__FUNCTION__, $d);
        }
        else
        {
            // Return object
            return $d;
        }
    }
}