<?php

namespace App\Waypoint\Http;

use App;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Waypoint\Repository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Route;

/**
 * Class Controller
 * @package App\Waypoint\Http
 */
class Controller extends BaseController
{
    use  AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /** @var \InfyOm\Generator\Request\APIRequest */
    protected $RequestObj;

    /** @var  \Illuminate\Routing\Route */
    protected $CurrentRoute;

    /** @var Repository */
    protected $RepositoryObj;

    const DEFAULT_START_DATE = '1900-01-01';
    const DEFAULT_END_DATE   = '2099-12-31';

    public function __construct(Repository $Repository)
    {
        $this->setRepositoryObj($Repository);
        /**
         * here for future use
         */
    }

    /**
     * @return \Illuminate\Routing\Route
     */
    public function getCurrentRoute(): \Illuminate\Routing\Route
    {
        if ($this->CurrentRoute)
        {
            return $this->CurrentRoute;
        }
        else
        {
            $this->CurrentRoute = $CurrentRoute = Route::current();
        }
        return $this->CurrentRoute;
    }

    /**
     * @param \Illuminate\Routing\Route $CurrentRoute
     */
    public function setCurrentRoute(\Illuminate\Routing\Route $CurrentRoute)
    {
        $this->CurrentRoute = $CurrentRoute;
    }

    /**
     * @return \Illuminate\Http\Request
     */
    public function getRequestObj()
    {
        if ( ! $this->RequestObj)
        {
            $this->RequestObj = request();
        }
        return $this->RequestObj;
    }

    /**
     * @param \Illuminate\Http\Request $RequestObj
     */
    public function setRequestObj(\Illuminate\Http\Request $RequestObj)
    {
        $this->RequestObj = $RequestObj;
    }

    /**
     * @return Repository
     */
    public function getRepositoryObj(): Repository
    {
        return $this->RepositoryObj;
    }

    /**
     * @param Repository $RepositoryObj
     */
    public function setRepositoryObj(Repository $RepositoryObj)
    {
        $this->RepositoryObj = $RepositoryObj;
    }

    /**
     * @param $input
     * @return mixed
     */
    public function init_start_and_end_date($input)
    {
        if ( ! isset($input['start_date']))
        {
            $input['start_date'] = self::DEFAULT_START_DATE;
        }

        if ( ! isset($input['end_date']))
        {
            $input['end_date'] = self::DEFAULT_END_DATE;
        }
        return $input;
    }
}
