<?php

namespace App\Waypoint;

use App\Waypoint\Exceptions\SmartyStreetsException;
use App\Waypoint\Models\Property;
use ArrayObject;
use FireEngineRed\SmartyStreetsLaravel\SmartyStreetsService;

class SmartyStreets
{
    /**
     * @var null|SmartyStreetsService
     */
    private $SmartyStreetsServiceObj = null;

    /**
     * SmartyStreets constructor.
     */
    public function __construct()
    {
        $this->SmartyStreetsServiceObj = $this->getSmartyStreetsServiceObj();
    }

    /**
     * @param $attributes
     * @throws SmartyStreetsException
     */
    public function updateWithCleanAddress(&$attributes)
    {
        $attributes['smartystreets_metadata'] = json_encode(new ArrayObject());

        if ( ! config('smartystreets.smartystreets', false))
        {
            return;
        }

        if (
            ! isset($attributes['street_address']) ||
            ! isset($attributes['city']) ||
            ! isset($attributes['state'])
        )
        {
            return;
        }
        /**
         * @todo - this needs work - need a more robust geocode solution but must be driven by the business
         *
         * see https://github.com/FireEngineRed/smartystreets-laravel this plugin needs work and is open to pull requests.
         *       We should fork if we see this as a long term solution.
         * see https://github.com/smartystreets/LiveAddressSamples/blob/master/php/get_zipcode_api.php though we are not using this
         * see https://smartystreets.com/
         */

        $SmartyStreetsServiceObj = $this->getSmartyStreetsServiceObj();
        $response                = $SmartyStreetsServiceObj->addressQuickVerify(
            [
                'street' => $attributes['street_address'],
                'city'   => $attributes['city'],
                'state'  => $attributes['state'],
            ]
        );
        /**
         * REMEMBER this is the response from smartystreets
         */
        if ($response)
        {
            if (isset($response['components']['state_abbreviation']))
            {
                $attributes['state'] = Property::STATE_ABBR_TO_STATE_NAME[$response['components']['state_abbreviation']];
            }
            $attributes['street_address']         = isset($response['delivery_line_1']) ? $response['delivery_line_1'] : $attributes['street_address'];
            $attributes['postal_code']            = isset($response['components']['zipcode']) ? $response['components']['zipcode'] : $attributes['postal_code'];
            $attributes['city']                   = isset($response['components']['city']) ? $response['components']['city'] : $attributes['city'];
            $attributes['state_abbr']             = isset($response['components']['state_abbreviation']) ? $response['components']['state_abbreviation'] : $attributes['state_abbr'];
            $attributes['state']                  = isset($response['components']['state_abbreviation']) ? Property::STATE_ABBR_TO_STATE_NAME[$attributes['state_abbr']] : $attributes['state'];
            $attributes['longitude']              = isset($response['metadata']['longitude']) ? $response['metadata']['longitude'] : $attributes['longitude'];
            $attributes['latitude']               = isset($response['metadata']['latitude']) ? $response['metadata']['latitude'] : $attributes['latitude'];
            $attributes['time_zone']              = isset($response['metadata']['time_zone']) ? $response['metadata']['time_zone'] : $attributes['time_zone'];
            $attributes['city']                   = isset($response['components']['city_name']) ? $response['components']['city_name'] : $attributes['city'];
            $attributes['smartystreets_metadata'] = isset($response) ? json_encode($response) : json_encode(new ArrayObject());

            $attributes['country']                   = Property::THE_LAND_OF_THE_FREE;
            $attributes['country_abbr']              = Property::THE_LAND_OF_THE_FREE_ABBR;
            $attributes['address_validation_failed'] = false;
        }
        else
        {
            $attributes['address_validation_failed'] = true;
            $attributes['smartystreets_metadata']    = json_encode(['status' => 'failed']);
            if (config('smartystreets.fail_on_failure', true))
            {
                throw new SmartyStreetsException('property validation failed ' . implode(' ', $attributes));
            }
        }
    }

    /**
     * @return null|SmartyStreetsService
     */
    private function getSmartyStreetsServiceObj()
    {
        if ( ! $this->SmartyStreetsServiceObj)
        {
            $this->setSmartyStreetsServiceObj(new SmartyStreetsService());
        }
        return $this->SmartyStreetsServiceObj;
    }

    /**
     * @param SmartyStreetsService $SmartyStreetsServiceObj
     */
    private function setSmartyStreetsServiceObj(SmartyStreetsService $SmartyStreetsServiceObj)
    {
        $this->SmartyStreetsServiceObj = $SmartyStreetsServiceObj;
    }
}
