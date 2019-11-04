<?php

namespace App\Waypoint\Http\Requests\Api;

use App\Waypoint\Http\ApiRequest as BaseApiRequest;

/**
 * Class CreateImageRequest
 * @package App\Waypoint\Http\Requests\ApiRequest
 */
class CreateImageRequest extends BaseApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        /**
         * we do not want to validate this request!! Remember the diff
         * between validating the request and validating the model
         */
        return [];
    }
}
