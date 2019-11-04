<?php

namespace App\Waypoint\Http\Requests\Api;

use App\Waypoint\Http\Requests\Generated\Api\CreateEntityTagEntityRequest;

/**
 * Class CreateFavoriteRequest
 * @package App\Waypoint\Http\Requests\ApiRequest
 */
class CreateFavoriteRequest extends CreateEntityTagEntityRequest
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
     * Get the validation rules that apply to the request.
     *
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
