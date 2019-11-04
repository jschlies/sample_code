<?php

namespace App\Waypoint\Http;

use App\Waypoint\Exceptions\GeneralException;
use InfyOm\Generator\Request\APIRequest as BaseApiRequest;

class ApiRequest extends BaseApiRequest
{
    /**
     * Constructor.
     *
     * @param array $query             The GET parameters
     * @param array $request           The POST parameters
     * @param array $attributes        The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array $cookies           The COOKIE parameters
     * @param array $files             The FILES parameters
     * @param array $server            The SERVER parameters
     * @param string|resource $content The raw body data
     */
    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * @return array|null
     * @throws GeneralException
     */
    public function rules()
    {
        /**
         * we do not want to validate this request!! Remember the diff
         * between validating the request and validating the model
         */
        return [];
    }

    /**
     * Get all of the input and files for the request.
     *
     * @return array
     */
    public function all()
    {
        return array_replace_recursive($this->input(), $this->allFiles());
    }
}
