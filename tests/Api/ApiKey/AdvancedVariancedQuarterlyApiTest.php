<?php

namespace App\Waypoint\Tests\Api\ApiKey;

use App;
use App\Waypoint\CurlServiceTrait;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\ApiKey;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\ClientRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAdvancedVarianceThresholdTrait;
use App\Waypoint\Tests\Generated\MakeAdvancedVarianceTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\Mocks\NativeCoaLedgerMockRepository;
use App\Waypoint\Tests\TestCase;

/**
 * Class BridgeApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 *
 */
class AdvancedVariancedQuarterlyApiTest extends TestCase
{
    use ApiTestTrait;
    use MakeUserTrait;
    use CurlServiceTrait;
    use MakeAdvancedVarianceTrait;
    use MakeAdvancedVarianceThresholdTrait;

    /** @var ApiKey */
    protected $ApiKeyObj;
    /** @var User */
    protected $UserObj;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        parent::setUp();

        /**
         * @todo I know, I know, there is no 'logged in user' for apiKey routes
         */
        /** @var User $UserObj */
        $this->UserObj = $this->getLoggedInUserObj();
        if ( ! $this->UserObj->apiKey)
        {
            ApiKey::make($this->UserObj->id);
        }
        /** @var User $User2Obj */
        $this->ApiKeyObj = $this->getLoggedInUserObj()->apiKey;

        App::make(ClientRepository::class)->initAdvancedVariance($this->ClientObj->id);

        $this->getLoggedInUserObj()->setAllAdvancedVarianceNotificationConfigs(true);
        $this->ClientObj->updateConfig('ADVANCED_VARIANCE_REVIEWERS', $this->ClientObj->users->getArrayOfGivenFieldValues('email'));
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \Exception
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_quarterly_advanced_variance()
    {
        /**
         * no point in running tests if queues are on, unless your running workers but even then,
         * you might get a nasty race condition
         */
        $this->assertTrue(config('queue.driver', 'sync') == 'sync');

        /** @noinspection PhpParamsInspection */
        AdvancedVarianceRepository::setNativeCoaLedgerRepositoryObj(App::make(NativeCoaLedgerMockRepository::class));

        $advanced_variance_data_arr                = $this->fakeAdvancedVarianceData(
            [
                'client_id'   => $this->ClientObj->id,
                'property_id' => $this->ClientObj->properties->first()->id,
            ],
            Seeder::PHPUNIT_FACTORY_NAME
        );
        $advanced_variance_data_arr['period_type'] = 'quarterly';
        $advanced_variance_data_arr['as_of_month'] = Seeder::getFakerObj()->randomElement([3, 6, 9, 12]);
        $advanced_variance_data_arr['as_of_year']  = Seeder::getFakerObj()->randomElement([2014, 2015, 2016, 2017]);
        /**
         * nasty constraint issue
         */
        while (AdvancedVariance::where('property_id', $advanced_variance_data_arr['property_id'])
                               ->where('as_of_month', $advanced_variance_data_arr['as_of_month'])
                               ->where('as_of_year', $advanced_variance_data_arr['as_of_year'])
                               ->get()->count()
        )
        {
            $advanced_variance_data_arr['as_of_month'] = Seeder::getFakerObj()->randomElement([3, 6, 9, 12]);
            $advanced_variance_data_arr['as_of_year']  = Seeder::getFakerObj()->randomElement([2014, 2015, 2016, 2017]);
        }

        $this->turn_on_fake_notifications();

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id .
            '/properties/' . $advanced_variance_data_arr['property_id'] . '/advancedVariances',
            $advanced_variance_data_arr,
            [
                'Content-Type' => 'application/jsonn',
            ]
        );

        $this->assertApiSuccess();
        $advanced_variance_id = $this->getFirstDataObject()['id'];
        $AdvancedVarianceObj  = App::make(AdvancedVarianceRepository::class)->find($advanced_variance_id);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id .
            '/properties/' . $advanced_variance_data_arr['property_id'] . '/advancedVariances',
            [],
            [
                'Content-Type' => 'application/json',
            ]
        );
        $this->assertApiSuccess();
        $this->asserttrue($this->getJSONContent()['success']);
        $this->assertTrue(count($this->getDataObjectArr()) > 0);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id .
            '/properties/' . $advanced_variance_data_arr['property_id'] . '/advancedVariances/' . $AdvancedVarianceObj->id,
            [],
            [
                'Content-Type' => 'application/json',
            ]
        );
        $this->assertApiSuccess();
        $this->asserttrue($this->getJSONContent()['success']);
        $this->assertTrue(count($this->getDataObjectArr()) > 0);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        unset($this->ApiKeyObj);
        unset($this->UserObj);
        parent::tearDown();
    }
}
