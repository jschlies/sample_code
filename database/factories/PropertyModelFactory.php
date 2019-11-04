<?php

use App\Waypoint\Models\AssetType;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Property;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\TestCase;

/**
 * @var $factory \App\Waypoint\Tests\Factory
 */
$factory->define(
    Property::class,
    function ()
    {
        /**
         * leave this here for when we are generating properties that need to
         * work w/ ledger like when we generate 10,000 properties
         */
        $property_code_arr = [
            'BXNTUS',
            'BXRBEL',
            'BXRCAR',
            'BXRDOW',
            'BXRDUP',
            'BXRFIC',
            'BXRHUN',
            'BXRLAP',
            'BXRLIL',
            'BXRSHE',
            'GHBAL',
            'GHCAM',
            'GSORRE',
            'R2BRO',
            'R2W263',
            'R2W305',
            'R2W323',
            'R3BEL',
            'R3D100',
            'R3D104',
            'R3D100',
            'R3D102',
            'R3D104',
            'R3DEN',
            'R3LAK',
            'R3PAC',
            'R4EAS',
            'R4EAS',
            'R4FRA',
            'R52AVE',
            'R5CORI',
            'R5CORO',
            'R5N71',
            'R5REY',
            'R5ROSS',
            'R5ROSS',
            'R5SJK',
            'R5SONY',
            'VL510',
            'VLCOM',
            'VLGARN',
            'VLLOA',
            'VLMAG',
            'VLONT',
            'VLPAL',
        ];

        $year_built = (integer) Seeder::getFakerObj()->dateTimeBetween($startDate = '-100 years', $endDate = '-30 years')->format('Y');
        return [
            'name'                        => Seeder::getFakePropertyName(),
            'display_name'                => Seeder::getFakerObj()->words(4, true),
            'property_code'               => Seeder::getFakerObj()->shuffleString('abcdefghijk01234567890'),
            'description'                 => Seeder::getFakeDescription(),
            'accounting_system'           => Seeder::getFakerObj()->randomElement(Property::$accounting_system_value_arr),
            'active_status'               => Property::ACTIVE_STATUS_ACTIVE,
            'active_status_date'          => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '+30 months')->format('Y-m-d H:i:s'),

            /**
             * @todo - get a good zip interfase like http://geocoder-php.org/GeocoderLaravel/ or http://stackoverflow.com/questions/4749706/lookup-city-and-state-by-zip-google-geocode-api
             *       or https://github.com/antonioribeiro/zipcode
             */
            'street_address'              => Seeder::getFakerObj()->address,
            'display_address'             => Seeder::getFakerObj()->address,
            'city'                        => 'San Francisco',
            'state'                       => 'California',
            'state_abbr'                  => 'CA',
            'country'                     => Property::THE_LAND_OF_THE_FREE,
            'country_abbr'                => Property::THE_LAND_OF_THE_FREE_ABBR,
            'postal_code'                 => 94117,
            'census_tract'                => Seeder::getFakerObj()->countryCode . Seeder::getFakerObj()->postcode,
            'longitude'                   => Seeder::getFakerObj()->longitude,
            'latitude'                    => Seeder::getFakerObj()->latitude,
            'time_zone'                   => Seeder::getFakerObj()->timezone,
            'suppress_address_validation' => false,
            'address_validation_failed'   => false,

            'square_footage'      => Seeder::getFakerObj()->randomFloat($nbMaxDecimals = 2, $min = 10000, $max = 10000000000000),
            'year_built'          => $year_built,
            'management_type'     => Seeder::getFakerObj()->randomElement(Property::$management_type_value_arr),
            'lease_type'          => Seeder::getFakerObj()->randomElement(Property::$lease_type_value_arr),
            'property_class'      => Seeder::getFakerObj()->randomElement(Property::$property_class_value_arr),
            'year_renovated'      => $year_built - mt_rand(5, 20),
            'number_of_buildings' => mt_rand(20, 50),
            'number_of_floors'    => mt_rand(20, 50),
            'custom_attributes'   => json_encode(
                [
                    Seeder::getFakerObj()->word . mt_rand() => Seeder::getFakerObj()->words(3, true),
                    Seeder::getFakerObj()->word . mt_rand() => Seeder::getFakerObj()->words(4, true),
                    Seeder::getFakerObj()->word . mt_rand() => Seeder::getFakerObj()->words(5, true),
                    Seeder::getFakerObj()->word . mt_rand() => Seeder::getFakerObj()->words(6, true),
                ]
            ),
            'region'              => Seeder::getFakerObj()->word . mt_rand(20, 50),
            'sub_region'          => Seeder::getFakerObj()->word . mt_rand(20, 50),
            'acquisition_date'    => Seeder::getFakerObj()->dateTimeBetween($startDate = '-30 months', $endDate = '+30 months')->format('Y-m-d H:i:s'),
            'investment_type'     => Seeder::getFakerObj()->word . mt_rand(20, 50),
            'fund'                => Seeder::getFakerObj()->word . mt_rand(20, 50),
            'property_sub_type'   => Seeder::getFakerObj()->word . mt_rand(20, 50),
            'ownership_entity'    => Seeder::getFakerObj()->word . mt_rand(20, 50),

            'smartystreets_metadata' => json_encode(new ArrayObject()),
            "raw_upload"             => json_encode(new ArrayObject()),
            'config_json'            => json_encode(new ArrayObject()),
            'image_json'             => json_encode(new ArrayObject()),
        ];
    },
    Seeder::DEFAULT_FACTORY_NAME
);

$factory->defineAs(
    Property::class,
    Seeder::PHPUNIT_FACTORY_NAME,
    function (array $seeder_provided_attributes_arr) use ($factory)
    {
        $property_code_arr = [
            'BXNTUS',
            'BXRBEL',
            'BXRCAR',
            'BXRDOW',
            'BXRDUP',
            'BXRFIC',
            'BXRHUN',
            'BXRLAP',
            'BXRLIL',
            'BXRSHE',
            'GHBAL',
            'GHCAM',
            'GSORRE',
            'R2BRO',
            'R2W263',
            'R2W305',
            'R2W323',
            'R3BEL',
            'R3D100',
            'R3D104',
            'R3D100',
            'R3D102',
            'R3D104',
            'R3DEN',
            'R3LAK',
            'R3PAC',
            'R4EAS',
            'R4EAS',
            'R4FRA',
            'R52AVE',
            'R5CORI',
            'R5CORO',
            'R5N71',
            'R5REY',
            'R5ROSS',
            'R5ROSS',
            'R5SJK',
            'R5SONY',
            'VL510',
            'VLCOM',
            'VLGARN',
            'VLLOA',
            'VLMAG',
            'VLONT',
            'VLPAL',
        ];
        /** @var Client $ClientObj */
        $ClientObj = TestCase::getUnitTestClient();

        if (isset($seeder_provided_attributes_arr['asset_type_id']))
        {
            $AssetTypeObj = AssetType::find($seeder_provided_attributes_arr['asset_type_id']);
        }
        elseif ( ! $AssetTypeObj = $ClientObj->assetTypes->random())
        {
            $AssetTypeSeederObj = new AssetTypeSeeder(['client_id' => $ClientObj->id], 1, Seeder::PHPUNIT_FACTORY_NAME);
            $AssetTypeObj       = $AssetTypeSeederObj->run()->first();
        }

        /**
         * nasty constraint issue
         */
        do
        {
            $property_code = Seeder::getFakerObj()->randomElement($property_code_arr);
        } while (Property::where('client_id', $ClientObj->id)
                         ->where('property_code', $property_code)
                         ->get()->count()
        );

        return array_merge(
            $factory->raw(Property::class),
            [
                'client_id'     => $ClientObj->id,
                'asset_type_id' => $AssetTypeObj->id,
                'property_code' => $property_code,
            ],
            $seeder_provided_attributes_arr
        );
    }
);