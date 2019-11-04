<?php

namespace App\Waypoint\Http\Controllers\Api;

use App;
use App\Waypoint\Console\Commands\FlushCacheCommand;
use App\Waypoint\Console\Commands\FlushNonSessionCacheCommand;
use App\Waypoint\Console\Commands\RefreshGeneratedListsAndGroupsCommand;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Http\ApiController as BaseApiController;
use App\Waypoint\Repositories\ClientRepository;
use Artisan;
use Exception;
use Illuminate\Http\Request;

class ArtisanController extends BaseApiController
{
    /**
     * @todo - either merge this (and other Report controllers) into app/Http/Controllers/ApiRequest or
     *       come up w/ a naming system for all controllers
     */
    /** @var  ClientRepository */
    private $ClientRepositoryObj;

    /**
     * @var boolean
     */
    protected $skip_policies = true;

    /**
     * WaypointMasterBridgeController constructor.
     * @param ClientRepository $ClientRepositoryObj
     */
    public function __construct(
        ClientRepository $ClientRepositoryObj
    ) {
        $this->ClientRepositoryObj = $ClientRepositoryObj;

        parent::__construct($this->ClientRepositoryObj);
    }

    /**
     * @param Request $RequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function flushAllCache(Request $RequestObj)
    {
        try
        {
            $FlushCacheCommandObj = new FlushCacheCommand();

            if ( ! $FlushCacheCommandObj->flushCache('All'))
            {
                return $this->sendResponse(null, 'flushAllCache Successful');
            }
            throw new GeneralException('An error has occurred');
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occurred', 400, $e);
        }
    }

    /**
     * @param Request $RequestObj
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function flushAllNonSessionCache(Request $RequestObj)
    {
        try
        {
            $FlushNonSessionCacheCommandObj = new FlushNonSessionCacheCommand();

            if ( ! $FlushNonSessionCacheCommandObj->flushNonSessionCache())
            {
                return $this->sendResponse(null, 'flushAllNonSessionCache Successful');
            }
            throw new GeneralException('An error has occured');
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 400, $e);
        }
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function triggerGroupCalc(Request $RequestObj, $client_id)
    {
        try
        {
            $result = Artisan::call(
                'waypoint:trigger:property_group_calc',
                [
                    '--client_ids' => $client_id,
                ]
            );
            if ( ! $result)
            {
                return $this->sendResponse(null, 'triggerGroupCalc Successful');
            }
            throw new GeneralException('An error has occured');
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 404, $e);
        }
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @throws GeneralException
     */
    public function blockGroupCalc(Request $RequestObj, $client_id)
    {
        try
        {

            throw new GeneralException('An error has occured');
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 400, $e);
        }
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @param $dropdown_name
     * @param $dropdown_value
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function setFilterDefaultValue(Request $RequestObj, $client_id, $dropdown_name, $dropdown_value)
    {
        try
        {
            $result = Artisan::call(
                'waypoint:set_filter_default_value',
                [
                    '--client_id'     => $client_id,
                    '--dropdown_name' => $dropdown_name,
                    '--default_value' => $dropdown_value,
                ]
            );

            if ( ! $result)
            {
                return $this->sendResponse(null, 'setFilterDefaultValue Successful');
            }
            throw new GeneralException('An error has occured');
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 404, $e);
        }
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @param $filter_name
     * @param $filter_options
     * @return \Illuminate\Http\JsonResponse|null
     * @throws GeneralException
     */
    public function filter_alter(Request $RequestObj, $client_id, $filter_name, $filter_options)
    {
        try
        {
            $result = Artisan::call(
                'waypoint:filter:alter',
                [
                    '--client_id'      => $client_id,
                    '--filter_name'    => $filter_name,
                    '--filter_options' => $filter_options,
                ]
            );

            if ( ! $result)
            {
                return $this->sendResponse(null, 'filter_alter Successful');
            }
            throw new GeneralException('An error has occured');
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 500, $e);
        }
    }

    /**
     * @param Request $RequestObj
     * @param integer $client_id
     * @param $config_name
     * @param $config_value
     * @throws GeneralException
     */
    public function setClientConfigValue(Request $RequestObj, $client_id, $config_name, $config_value)
    {
        try
        {

            if ( ! $client_id)
            {
                throw new GeneralException("No client_id found");
            }
            if ( ! $ClientObj = $this->ClientRepositoryObj->find($client_id))
            {
                throw new GeneralException("no client_id found");
            }
            $ClientObj->updateConfig($config_name, $config_value);

            throw new GeneralException('An error has occured');
        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 500, $e);
        }
    }

    /**
     * @param Request $RequestObj
     * @param string $client_id_string
     * @throws GeneralException
     */
    public function refreshGeneratedListsAndGroups(Request $RequestObj, $client_id_string)
    {
        try
        {
            /** @var RefreshGeneratedListsAndGroupsCommand $RefreshGeneratedListsAndGroupsCommandObj */
            $RefreshGeneratedListsAndGroupsCommandObj = App::make(RefreshGeneratedListsAndGroupsCommand::class);
            $RefreshGeneratedListsAndGroupsCommandObj->RefreshGeneratedListsAndGroups($client_id_string);

        }
        catch (GeneralException $e)
        {
            throw $e;
        }
        catch (Exception $e)
        {
            throw new GeneralException('An error has occured', 500, $e);
        }
    }
}