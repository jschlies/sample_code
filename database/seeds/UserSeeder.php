<?php

use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Seeder;

/**
 * Class UserSeeder
 */
class UserSeeder extends Seeder
{
    /**
     * UserSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(User::class);
        $this->ModelRepositoryObj = App::make(UserRepository::class)->setSuppressEvents(true)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        $UserObjArr = parent::run();
        /**
         * note that ModelRepositoryObj->create() can create several
         * objects by practice, NativeCoa, ClientCategory and others so
         * understand whats already taken care of in ModelRepositoryObj->create()
         * @var User $UserObj
         */
        /** @var User $UserObj */
        foreach ($UserObjArr as $UserObj)
        {
            $UserObj->updateConfig('NOTIFICATIONS', true);
            $UserObj->updateConfig(strtoupper(Seeder::getFakerObj()->word), true);
            $UserObj->updateConfig(strtoupper(Seeder::getFakerObj()->word), mt_rand());
            $UserObj->updateImage(strtoupper(Seeder::getFakerObj()->word), mt_rand());

            if ( ! $UserObj->cachedRoles()->count())
            {
                $UserObj->attachRole(Role::where('name', Role::CLIENT_GENERIC_USER_ROLE)->first());
            }

            /**
             * all seeded users get notifications
             */
            $UserObj->updateConfig('NOTIFICATIONS', true);

            $UserObj->updateConfig(User::DEFAULT_ANALYTICS_REPORT_TEMPLATE_FLAG, $UserObj->client->getBomaReportTemplateObj()->id);

            $this->UserRepositoryObj->initNativeAccountDropdownDefaults($UserObj);

            /**
             * make some Property groups
             *
             */
            $PropertyGroupSeederObj = new PropertyGroupSeeder(
                [
                    'user_id'               => $UserObj->id,
                    'is_all_property_group' => false,
                ]
            );
            $PropertyGroupSeederObj->run();
        }

        return $UserObjArr;
    }
}