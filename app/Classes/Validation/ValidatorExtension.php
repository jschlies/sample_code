<?php

namespace App\Waypoint;

use Felixkiss\UniqueWithValidator\Validator as ValidatorExtensionBase;

class ValidatorExtension extends ValidatorExtensionBase
{
    public function replaceUniqueWith($message, $attribute, $rule, $parameters, $translator)
    {
        // remove trailing ID param if present
        $this->getIgnore($parameters);

        // merge primary field with conditional fields
        $fields = [$attribute] + $parameters;

        // get full language support due to mapping to validator getDisplayableAttribute
        // function
        $fields = array_map([$this, 'getDisplayableAttribute'], $fields);

        // fields to string
        $fields = implode(', ', $fields);

        return str_replace(':fields', $fields, $message);
    }

    /**
     * Returns an array with value and column name for an optional ignore.
     * Shaves of the ignore_id from the end of the array, if there is one.
     *
     * @param array $parameters
     * @return array [$ignoreId, $ignoreColumn]
     *
     * @todo remove this method when this
     *       has been pulled https://github.com/felixkiss/uniquewith-validator/pull/67/files
     */
    private function getIgnore(&$parameters)
    {
        $lastParam = end($parameters);
        $lastParam = array_map('trim', explode('=', $lastParam));

        // An ignore_id is only specified if the last param starts with a
        // number greater than 1 (a valid id in the database)
        if ( ! preg_match('/^[1-9][0-9]*$/', $lastParam[0]))
        {
            return [null, null];
        }

        $ignoreId     = $lastParam[0];
        $ignoreColumn = (sizeof($lastParam) > 1) ? end($lastParam) : null;

        // Shave of the ignore_id from the array for later processing
        array_pop($parameters);

        return [$ignoreId, $ignoreColumn];
    }
}