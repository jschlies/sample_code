<?php

namespace App\Waypoint;

use App\Waypoint\Exceptions\GeneralException;
use \Ixudra\Curl\CurlService;

/**
 * see https://github.com/ixudra/curl
 * Class CurlServiceTrait
 */
trait CurlServiceTrait
{
    /** @var CurlService|null */
    private $CurlServiceObj = null;

    /**
     * @return CurlService|null
     * @throws GeneralException
     */
    public function getCurlServiceObj()
    {
        try
        {
            if ( ! $this->CurlServiceObj)
            {
                $this->CurlServiceObj = new CurlService();
            }
            return $this->CurlServiceObj;
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (\Exception $e)
        {
            throw new GeneralException($e->getMessage(), 400, $e);
        }
    }

    /**
     * @param CurlService $CurlServiceObj
     */
    public function setCurlServiceObj(CurlService $CurlServiceObj)
    {
        $this->CurlServiceObj = $CurlServiceObj;
    }
}