<?php

namespace App\Waypoint\Tests\Mocks;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Ledger\RentRoll;
use App\Waypoint\Repositories\PropertyRepository;
use Carbon\Carbon;
use \Faker\Factory as FakerFactory;
use \Faker\Provider\en_US\Company as FakerProvideren_USCompany;

class RentRollMockRepository
{
    public function __construct()
    {
        if (App::environment() === 'production')
        {
            throw new \Exception('What, you crazy!!!!! No RentRollMockRepository in production context ' . __FILE__);
        }
        $this->PropertyRepositoryObj = App::make(PropertyRepository::class);
    }

    /**
     * @param int $property_id
     * @param array $native_account_codes_array
     * @param Carbon $date
     * @param bool $quarterly
     * @return array
     */
    public function getRentRoll(int $property_id, $client_asof_date = null): Collection
    {
        if (empty($property_id))
        {
            throw new GeneralException('missing property id' . ' ' . __FILE__ . ':' . __LINE__);
        }

        if ( ! $PropertyObj =
            $this->PropertyRepositoryObj
                ->with('nativeCoas.nativeAccounts.nativeAccountType.nativeAccountTypeTrailers')
                ->find($property_id))
        {
            throw new GeneralException('Could not find property from property_id = ' . $property_id . ' ' . __FILE__ . ':' . __LINE__);
        }

        $RentRollObjArr = new Collection();
        $i              = 0;

        $CompanyFakerObj = FakerFactory::create();
        $CompanyFakerObj->addProvider(new FakerProvideren_USCompany($CompanyFakerObj));
        while ($i++ < mt_rand(5, 15))
        {
            $this->payload = [
                'rent_roll_id'           => mt_rand(10000, 10000000),
                'property_name'          => $CompanyFakerObj->company() . ' ' . $CompanyFakerObj->address,
                'property_code'          => $CompanyFakerObj->shuffleString('abcdefghijk01234567890'),
                'as_of_date'             => $CompanyFakerObj->dateTimeBetween($startDate = '-1 months', $endDate = '+1 months')->format('Y-m-d H:i:s'),
                'original_property_code' => $CompanyFakerObj->shuffleString('abcdefghijk01234567890'),

                'rent_unit_id'          => mt_rand(10000, 10000000),
                'suite_id_code'         => $CompanyFakerObj->company,
                'lease_id_code'         => $CompanyFakerObj->catchPhrase(),
                'lease_name'            => $CompanyFakerObj->catchPhrase(),
                'least_id_staging'      => mt_rand(10000, 10000000),
                'lease_type'            => $CompanyFakerObj->randomElement(['A', 'M']),
                'square_footage'        => $CompanyFakerObj->randomFloat($nbMaxDecimals = 2, $min = 10000, $max = 10000000),
                'lease_start_date'      => $CompanyFakerObj->dateTimeBetween($startDate = '-30 months', $endDate = '+10 months')->format('Y-m-d H:i:s'),
                'lease_expiration_date' => $CompanyFakerObj->dateTimeBetween($startDate = '+30 months', $endDate = '+90 months')->format('Y-m-d H:i:s'),
                'lease_term'            => mt_rand(12, 360),
                'tenancy_year'          => $CompanyFakerObj->numberBetween(1900, 2025),
                'monthly_rent'          => $CompanyFakerObj->randomFloat($nbMaxDecimals = 2, $min = 10000, $max = 10000000),
                'monthly_rent_area'     => $CompanyFakerObj->randomFloat($nbMaxDecimals = 2, $min = 10000, $max = 10000000),
                'annual_rent'           => $CompanyFakerObj->randomFloat($nbMaxDecimals = 2, $min = 10000, $max = 10000000),
                'annual_rent_area'      => $CompanyFakerObj->randomFloat($nbMaxDecimals = 2, $min = 10000, $max = 10000000),
                'annual_rec_area'       => $CompanyFakerObj->randomFloat($nbMaxDecimals = 2, $min = 10000, $max = 10000000),
                'annual_misc_area'      => $CompanyFakerObj->randomFloat($nbMaxDecimals = 2, $min = 10000, $max = 10000000),
                'security_deposit'      => $CompanyFakerObj->randomFloat($nbMaxDecimals = 2, $min = 10000, $max = 10000000),
                'updated_datetime'      => $CompanyFakerObj->dateTimeBetween($startDate = '-90 days', $endDate = '-30 days')->format('Y-m-d H:i:s'),
                'letter_cr_amt'         => $CompanyFakerObj->randomFloat($nbMaxDecimals = 2, $min = 10000, $max = 10000000),
            ];

            $RentRollObj      = new RentRoll($this->payload);
            $RentRollObjArr[] = $RentRollObj;

        }
        return $RentRollObjArr;
    }

    /**
     * @return mixed
     **/
    public function model()
    {
        return RentRoll::class;
    }
}
