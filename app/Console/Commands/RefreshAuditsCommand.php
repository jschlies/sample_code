<?php

namespace App\Waypoint\Console\Commands;

use App\Waypoint\Command;
use App;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceExplanationType;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\AdvancedVarianceThreshold;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use Carbon\Carbon;
use Exception;

/**
 * Class AlterClientConfigCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 */
class RefreshAuditsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:audits:refresh
                        {--client_ids= : Comma separated list client IDs or \'All\' } 
                        ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh audits to ensure all fields are captured';

    /**
     * AlterClientConfigCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
        /**
         * NOTE you cannot populate $this->ClientRepositoryObj in parent::__construct. Messes up code generator
         */
    }

    /**
     * Execute the console command.
     *
     * @todo push this logic into a repository
     */
    public function handle()
    {
        $this->alert('Starting command ' . $this->getName());
        parent::handle();
        $this->processRefreshAuditsCommand($this->option('client_ids'));
        return true;
    }

    /**
     * @param null|integer $client_id
     * @param null|integer $property_id
     * @param bool $wipe_out_all_leases
     * @param bool $wipe_out_all_suites_and_leases
     * @param bool $wipe_out_all_tenants_and_leases
     * @param bool $do_not_refresh
     * @throws Exception
     */
    public function processRefreshAuditsCommand($client_ids_string)
    {
        foreach ($this->getClientsFromArray($client_ids_string) as $ClientObj)
        {
            $this->alert('processing ' . $ClientObj->name);
            /** @var Client $ClientObj */
            /** @var AccessList $AccessListObj */
            /** @var Property $PropertyObj */
            /** @var AdvancedVariance $AdvancedVarianceObj */
            /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
            /** @var AdvancedVarianceThreshold $AdvancedVarianceThresholdObj */
            /** @var AdvancedVarianceExplanationType $AdvancedVarianceExplanationTypesObj */
            /** @var App\Waypoint\Models\ReportTemplate $ReportTemplatesObj */
            /** @var App\Waypoint\Models\CalculatedField $CalculatedFieldObj */
            /** @var App\Waypoint\Models\CalculatedFieldEquation $CalculatedFieldEquationObj */
            $this->processObject($ClientObj);
            foreach ($ClientObj->accessLists as $AccessListObj)
            {
                $this->alert('processing ' . $ClientObj->name . ' accessList' . $AccessListObj->name);
                $this->processObject($AccessListObj);
                foreach ($AccessListObj->accessListProperties as $AccessListPropertyObj)
                {
                    $this->processObject($AccessListPropertyObj);
                }
                foreach ($AccessListObj->accessListUsers as $AccessListUserObj)
                {
                    $this->processObject($AccessListUserObj);
                }
            }
            foreach ($ClientObj->advancedVarianceThresholds as $AdvancedVarianceThresholdObj)
            {
                $this->alert('processing ' . $ClientObj->name . ' AdvancedVarianceThreshold ' . $AdvancedVarianceThresholdObj->name);
                $this->processObject($AdvancedVarianceThresholdObj);
            }

            foreach ($ClientObj->properties as $PropertyObj)
            {
                $this->alert('processing ' . $ClientObj->name . ' Property ' . $PropertyObj->name);
                $this->processObject($PropertyObj);
                foreach ($PropertyObj->advancedVariances as $AdvancedVarianceObj)
                {
                    $this->processObject($AdvancedVarianceObj);
                    foreach ($AdvancedVarianceObj->advancedVarianceLineItems as $AdvancedVarianceLineItemObj)
                    {
                        $this->processObject($AdvancedVarianceLineItemObj);
                    }
                    foreach ($AdvancedVarianceObj->advancedVarianceApprovals as $AdvancedVarianceApprovalObj)
                    {
                        $this->processObject($AdvancedVarianceApprovalObj);
                    }
                }
                foreach ($PropertyObj->ecmProjects as $EcmProjectObj)
                {
                    $this->processObject($EcmProjectObj);
                }
                foreach ($PropertyObj->leases as $LeaseObj)
                {
                    $this->processObject($LeaseObj);
                }
                foreach ($PropertyObj->opportunities as $OpportunityObj)
                {
                    $this->processObject($OpportunityObj);
                }
                foreach ($PropertyObj->propertyGroupProperties as $PropertyGroupPropertyObj)
                {
                    $this->processObject($PropertyGroupPropertyObj);
                }
            }
            foreach ($ClientObj->advancedVarianceExplanationTypes as $AdvancedVarianceExplanationTypesObj)
            {
                $this->alert('processing ' . $ClientObj->name . ' AdvancedVarianceExplanationTypes  ' . $AdvancedVarianceExplanationTypesObj->name);
                $this->processObject($AdvancedVarianceExplanationTypesObj);
            }
            foreach ($ClientObj->reportTemplates as $ReportTemplatesObj)
            {
                $this->alert('processing ' . $ClientObj->name . ' ReportTemplates  ' . $ReportTemplatesObj->name);
                foreach ($ReportTemplatesObj->calculatedFields as $CalculatedFieldObj)
                {
                    $this->processObject($CalculatedFieldObj);
                    foreach ($CalculatedFieldObj->calculatedFieldEquations as $CalculatedFieldEquationObj)
                    {
                        $this->processObject($CalculatedFieldEquationObj);
                    }
                    foreach ($CalculatedFieldObj->calculatedFieldEquations as $CalculatedFieldEquationObj)
                    {
                        $this->processObject($CalculatedFieldEquationObj);
                        foreach ($CalculatedFieldEquationObj->calculatedFieldVariables as $CalculatedFieldVariablesObj)
                        {
                            $this->processObject($CalculatedFieldVariablesObj);
                        }
                    }
                }
            }
            foreach ($ClientObj->users as $UserObj)
            {
                $this->processObject($UserObj);
            }
        }

        foreach ($this->AuthenticatingEntityRepositoryObj->all() as $AuthenticatingEntityObj)
        {
            $this->processObject($AuthenticatingEntityObj);
        }
        $ClientObj = null;
        unset($ClientObj);
    }

    public function processObject($Object)
    {

        $Object->updated_at = Carbon::now()->format('Y-m-d H:i:s');
        $Object->save();
    }
}
