<?php

namespace App\Waypoint\Console\Commands;

use App;
use App\Waypoint\Command;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\EcmProject;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;

/**
 * Class GenerateAuditActivityCommand
 * @package App\Console\Commands
 *
 * See https://laravel.com/docs/5.1/artisan
 * @codeCoverageIgnore
 */
class GenerateAuditActivityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:generate_audit_activity ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate audit activity to database';

    /**
     * ClientSeederCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
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

        /** @var Client $ClientObj */
        foreach ($this->ClientRepositoryObj->with('users')->with('properties')->all() as $ClientObj)
        {
            if ($ClientObj->name == Client::DUMMY_CLIENT_NAME)
            {
                continue;
            }
            $old_client_name = $ClientObj->name;
            $ClientObj->name = Seeder::getFakerObj()->company . Seeder::getFakerObj()->words(2, true) . mt_rand(1000, 9999999);
            $ClientObj->save();
            /** @var User $UserObj */
            foreach ($ClientObj->users as $UserObj)
            {
                $old_user_firstname = $UserObj->firstname;
                $old_user_lastname  = $UserObj->lastname;
                $UserObj->firstname = Seeder::getFakerObj()->firstName;
                $UserObj->lastname  = Seeder::getFakerObj()->lastName;
                $UserObj->save();
                sleep(.8);
                $UserObj->firstname = $old_user_firstname;
                $UserObj->lastname  = $old_user_lastname;
                $UserObj->save();
            }
            sleep(.5);
            $ClientObj->description = Seeder::getFakerObj()->paragraph(2);
            $ClientObj->save();
            /** @var Property $PropertyObj */
            foreach ($ClientObj->properties as $PropertyObj)
            {
                $old_property_name = $PropertyObj->name;
                $PropertyObj->name = Seeder::getFakerObj()->company . ' ' . Seeder::getFakerObj()->companySuffix . ' Building ' . mt_rand(1000000, 9999999);
                $PropertyObj->save();
                sleep(.5);
                $PropertyObj->name = $old_property_name;
                $PropertyObj->save();
            }
            $ClientObj->description = Seeder::getFakerObj()->paragraph(2);
            $ClientObj->save();

            /** @var User $UserObj */
            foreach ($ClientObj->users as $UserObj)
            {
                $old_user_firstname = $UserObj->firstname;
                $old_user_lastname  = $UserObj->lastname;
                $UserObj->user_name = Seeder::getFakerObj()->shuffleString('abcdefghijk01234567890');
                $UserObj->save();
                sleep(.5);
                $UserObj->firstname = $old_user_firstname;
                $UserObj->lastname  = $old_user_lastname;
                $UserObj->save();
            }
            sleep(1.1);
            /** @var Property $PropertyObj */
            foreach ($ClientObj->properties as $PropertyObj)
            {
                $old_property_code          = $PropertyObj->property_code;
                $PropertyObj->property_code = Seeder::getFakerObj()->shuffleString('abcdefghijk01234567890');
                $PropertyObj->save();
                sleep(.5);
                $PropertyObj->property_code = $old_property_code;
                $PropertyObj->save();
            }

            /** @var PropertyGroup $PropertyGroupObj */
            foreach ($ClientObj->propertyGroups as $PropertyGroupObj)
            {
                $old_property_group_name = $PropertyGroupObj->name;
                $PropertyGroupObj->name  = Seeder::getFakerObj()->shuffleString('abcdefghijk01234567890');
                $PropertyGroupObj->save();
                sleep(.5);
                $PropertyGroupObj->name = $old_property_group_name;
                $PropertyGroupObj->save();
            }

            /** @var AccessList $AccessListObj */
            foreach ($ClientObj->accessLists as $AccessListObj)
            {
                $old_access_list_name = $AccessListObj->name;
                $AccessListObj->name  = Seeder::getFakerObj()->shuffleString('abcdefghijk01234567890');
                $AccessListObj->save();
                sleep(.5);
                $AccessListObj->name = $old_access_list_name;
                $AccessListObj->save();
            }

            /** @var EcmProject $EcmProjectObj */
            foreach ($ClientObj->getEcmProjects() as $EcmProjectObj)
            {
                $old_name            = $EcmProjectObj->name;
                $old_property_id     = $EcmProjectObj->property_id;
                $EcmProjectObj->name = Seeder::getFakerObj()->shuffleString('abcdefghijk01234567890');
                $EcmProjectObj->save();
                foreach ($ClientObj->properties as $PropertyObj)
                {
                    $EcmProjectObj->property_id = $PropertyObj->id;
                    $EcmProjectObj->save();
                    sleep(1.1);
                    $EcmProjectObj->property_id = $old_property_id;
                    $EcmProjectObj->save();
                    break;
                }
                $EcmProjectObj->name = $old_name;
                $EcmProjectObj->save();
            }

            $ClientObj->name = $old_client_name;
            $ClientObj->save();
        }
        return true;
    }
}