<?php

use App\Waypoint\Models\Lease;
use App\Waypoint\Models\NativeAccountAmount;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\Suite;
use App\Waypoint\Models\Tenant;
use App\Waypoint\Repositories\PropertyRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\FactoryBuilder;
use App\Waypoint\Tests\TestCase;
use Carbon\Carbon;

/**
 * Class PropertySeeder
 */
class PropertySeeder extends Seeder
{
    /**
     * PropertySeeder constructor.
     * @param array $attributes
     * @param int $count
     * @param string $factory_name
     * @throws Exception
     */
    public function __construct($attributes = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($attributes, $count, $factory_name);
        $this->setResultingClass(Property::class);
        $this->ModelRepositoryObj = App::make(PropertyRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function run()
    {
        $PropertyObjArr = parent::run();
        /**
         * the factories below need a refreshed ClientObj
         * to access the newly created properties
         */
        $this->ClientObj = TestCase::getUnitTestClient(1);
        foreach ($PropertyObjArr as $PropertyObj)
        {
            $PropertyObj->updateConfig('NOTIFICATIONS', true);

            echo 'Seeding EcmProjects for ' . $PropertyObj->name . PHP_EOL;
            $EcmProjectSeederObj = new EcmProjectSeeder(
                [
                    'property_id' => $PropertyObj->id,
                ],
                Seeder::ECM_PROJECTS_PER_PROPERTY
            );
            $EcmProjectSeederObj->run();

            $i = 0;
            echo 'Seeding Suites/Leases/Tenants for ' . $PropertyObj->name . PHP_EOL;
            while ($i < Seeder::LEASED_SUITES_PER_PROPERTY)
            {
                /** @var FactoryBuilder $FactoryObj */
                $SuiteFactoryObj            = $this->factory(
                    Suite::class,
                    Seeder::PHPUNIT_FACTORY_NAME,
                    1
                );
                $suite_attrs                = $SuiteFactoryObj->raw()[0];
                $suite_attrs['property_id'] = $PropertyObj->id;
                $SuiteObj                   = $this->SuiteRepositoryObj->create($suite_attrs);

                /** @var FactoryBuilder $FactoryObj */
                $LeaseFactoryObj            = $this->factory(
                    Lease::class,
                    Seeder::PHPUNIT_FACTORY_NAME,
                    1
                );
                $lease_attrs                = $LeaseFactoryObj->raw()[0];
                $lease_attrs['property_id'] = $PropertyObj->id;
                $LeaseObj                   = $this->LeaseRepositoryObj->create($lease_attrs);

                /** @var FactoryBuilder $FactoryObj */
                $TenantFactoryObj          = $this->factory(
                    Tenant::class,
                    Seeder::PHPUNIT_FACTORY_NAME,
                    1
                );
                $tenant_attrs              = $TenantFactoryObj->raw()[0];
                $tenant_attrs['client_id'] = $PropertyObj->client_id;
                $TenantObj                 = $this->TenantRepositoryObj->create($tenant_attrs);

                foreach ($PropertyObj->client->tenantAttributes as $TenantAtributeObj)
                {
                    $this->TenantTenantAttributeRepositoryObj->create(
                        [
                            'tenant_attribute_id' => $TenantAtributeObj->id,
                            'tenant_id'           => $TenantObj->id,
                        ]
                    );
                    if (Seeder::trueSomeOfTheTime(30))
                    {
                        break;
                    }
                }

                $this->SuiteLeaseRepositoryObj->create(
                    [
                        'suite_id' => $SuiteObj->id,
                        'lease_id' => $LeaseObj->id,
                    ]
                );

                $this->SuiteTenantRepositoryObj->create(
                    [
                        'suite_id'  => $SuiteObj->id,
                        'tenant_id' => $TenantObj->id,
                    ]
                );

                $this->LeaseTenantRepositoryObj->create(
                    [
                        'lease_id'  => $LeaseObj->id,
                        'tenant_id' => $TenantObj->id,
                    ]
                );
                $i++;
            }
            /**
             * suiteless leases
             *
             * @var FactoryBuilder $FactoryObj
             */
            $LeaseFactoryObj = $this->factory(
                Lease::class,
                Seeder::PHPUNIT_FACTORY_NAME,
                10
            );
            foreach ($LeaseFactoryObj->raw() as $lease_attrs)
            {
                $lease_attrs['property_id'] = $PropertyObj->id;
                $this->LeaseRepositoryObj->create($lease_attrs);
            }

            echo 'Seeding Opportunities for ' . $PropertyObj->name . PHP_EOL;
            $OpportunitySeederObj = new OpportunitySeeder(
                [
                    'client_id'           => $PropertyObj->client_id,
                    'property_id'         => $PropertyObj->id,
                    'created_by_user_id'  => $PropertyObj->client->users->random(),
                    'client_category_id'  => $PropertyObj->client->clientCategories->random()->id,
                    'assigned_to_user_id' => $PropertyObj->client->users->random()->id,
                ]
            );
            $OpportunitySeederObj->run();

            echo 'Seeding NativeAccountAmounts for ' . $PropertyObj->name . PHP_EOL;
            /** @var \App\Waypoint\Models\NativeCoa $NativeCoaObj */
            $NativeCoaObj = $this->ClientObj->nativeCoas->first();
            /** @var \App\Waypoint\Models\NativeAccount $NativeAccountObj */
            foreach ($NativeCoaObj->nativeAccounts as $NativeAccountObj)
            {
                $FromDateObj = Carbon::create(2017, 1, 1);
                while ($FromDateObj->year <= 2018)
                {
                    $bulk_update_arr [] =
                        [
                            "client_id"            => $PropertyObj->client_id,
                            "property_id"          => $PropertyObj->id,
                            "native_account_id"    => $NativeAccountObj->id,
                            "month"                => $FromDateObj->format('m'),
                            "year"                 => $FromDateObj->format('Y'),
                            "month_year_timestamp" => $FromDateObj->format('Y-m-d H:i:s'),
                            "actual"               => mt_rand(0,10000),
                            "budget"               => mt_rand(0,10000),
                        ];

                    $FromDateObj->addMonth(1);
                }
            }
            if ($bulk_update_arr)
            {
                NativeAccountAmount::insert($bulk_update_arr);
                $bulk_update_arr = [];
            }
        }
        $this->ClientObj = TestCase::getUnitTestClient(1);

        return $PropertyObjArr;
    }
}