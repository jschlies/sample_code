<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use Auth;

/**
 * Class GenerateCommentsCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class GenerateCommentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:generate_comments ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate comments to database';

    /**
     * ClientSeederCommand constructor.
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
     */
    public function handle()
    {
        if (App::environment() === 'production')
        {
            throw new GeneralException('What!! Are you crazy!!. You can\'t run the seeder in production context', 403);
        }
        parent::handle();

        /** @var User $UserObj */
        $UserObj = Auth::getUser();

        /** @var Client $ClientObj */
        foreach ($this->ClientRepositoryObj->with('users')->with('properties')->all() as $ClientObj)
        {
            $UserObj->comment($ClientObj, Seeder::getFakerObj()->sentence());
            /** @var Property $PropertyObj */
            foreach ($ClientObj->properties as $PropertyObj)
            {
                $UserObj->comment($PropertyObj, Seeder::getFakerObj()->sentence());
                /** @var Opportunity $Opportunity */
                foreach ($PropertyObj->opportunities as $Opportunity)
                {
                    $UserObj->comment($Opportunity, Seeder::getFakerObj()->sentence());
                    $UserObj->comment($Opportunity, Seeder::getFakerObj()->sentence());
                    $UserObj->comment($Opportunity, Seeder::getFakerObj()->sentence());
                    $UserObj->comment($Opportunity, Seeder::getFakerObj()->sentence());
                    $UserObj->comment($Opportunity, Seeder::getFakerObj()->sentence());
                }
            }

            /** @var PropertyGroup $PropertyGroupObj */
            foreach ($ClientObj->propertyGroups as $PropertyGroupObj)
            {
                $UserObj->comment($PropertyGroupObj, Seeder::getFakerObj()->sentence());
            }

            /** @var AccessList $AccessListObj */
            foreach ($ClientObj->accessLists as $AccessListObj)
            {
                $UserObj->comment($AccessListObj, Seeder::getFakerObj()->sentence());
            }

            /** @var AccessList $AccessListObj */
            foreach ($ClientObj->getEcmProjects() as $EcmProjectObj)
            {
                $UserObj->comment($EcmProjectObj, Seeder::getFakerObj()->sentence());
            }

            /** @var AccessList $AccessListObj */
            foreach ($ClientObj->getEcmProjects() as $EcmProjectObj)
            {
                $UserObj->comment($EcmProjectObj, Seeder::getFakerObj()->sentence());
            }
        }

        return true;
    }
}