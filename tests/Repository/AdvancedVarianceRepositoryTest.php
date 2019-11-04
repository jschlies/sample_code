<?php

namespace App\Waypoint\Tests\Repository;

use App;
use App\Waypoint\Collection;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\RelatedUserType;
use App\Waypoint\Models\Spreadsheet;
use App\Waypoint\Models\User;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeAdvancedVarianceTrait;
use App\Waypoint\Tests\Generated\MakeClientTrait;
use App\Waypoint\Tests\Generated\MakePropertyGroupPropertyTrait;
use App\Waypoint\Tests\Generated\MakePropertyGroupTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class AdvancedVarianceRepositoryTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class AdvancedVarianceRepositoryTest extends TestCase
{
    use MakeAccessListTrait, ApiTestTrait;
    use MakePropertyTrait;
    use MakeUserTrait;
    use MakePropertyGroupTrait;
    use MakePropertyGroupPropertyTrait;
    use MakeClientTrait;
    use MakeAdvancedVarianceTrait;

    public function setUp()
    {
        parent::setUp();
        $this->ClientObj->updateConfig('ADVANCED_VARIANCE_REVIEWERS', $this->ClientObj->users->getArrayOfGivenFieldValues('email'));
    }

    /**
     * @test
     *
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function it_create_advance_variance()
    {
        /**
         * no point in running tests if queues are on, unless your running workers but even then,
         * you might get a nasty race condition
         */
        $this->assertTrue(config('queue.driver', 'sync') == 'sync');

        /**
         * reset our objects to clear the optimistic cache
         */
        $this->ClientObj = $this->ClientRepositoryObj->find($this->ClientObj->id);
        $this->assertTrue(count($this->ClientObj->getConfigJSON(true)['ADVANCED_VARIANCE_REVIEWERS']) > 0);

        $this->FirstAccessListObj->addUser($this->FirstGenericUserObj);
        $this->FirstAccessListObj->addUser($this->SecondGenericUserObj);
        //$this->makeAccessListUser(['access_list_id' => $this->FirstAccessListObj->id, 'user_id' => $this->FirstGenericUserObj->id]);
        //$this->makeAccessListUser(['access_list_id' => $this->FirstAccessListObj->id, 'user_id' => $this->SecondGenericUserObj->id]);

        $this->SecondAccessListObj->addUser($this->SecondGenericUserObj);
        $this->FirstAccessListObj->addUser($this->ThirdGenericUserObj);
        //$this->makeAccessListUser(['access_list_id' => $this->SecondAccessListObj->id, 'user_id' => $this->SecondGenericUserObj->id]);
        //$this->makeAccessListUser(['access_list_id' => $this->FirstAccessListObj->id, 'user_id' => $this->ThirdGenericUserObj->id]);

        $this->FirstAccessListObj->addProperty($this->FirstPropertyObj);
        $this->FirstAccessListObj->addProperty($this->SecondPropertyObj);
        $this->FirstAccessListObj->addProperty($this->ThirdPropertyObj);
        $this->FirstAccessListObj->addProperty($this->FourthPropertyObj);
        //$this->makeAccessListProperty(['access_list_id' => $this->FirstAccessListObj->id, 'property_id' => $this->FirstPropertyObj->id]);
        //$this->makeAccessListProperty(['access_list_id' => $this->FirstAccessListObj->id, 'property_id' => $this->SecondPropertyObj->id]);
        //$this->makeAccessListProperty(['access_list_id' => $this->FirstAccessListObj->id, 'property_id' => $this->ThirdPropertyObj->id]);
        //$this->makeAccessListProperty(['access_list_id' => $this->FirstAccessListObj->id, 'property_id' => $this->FourthPropertyObj->id]);

        if ( ! $this->FirstPropertyObj->propertyNativeCoas()->first())
        {
            $this->PropertyNativeCoaRepositoryObj->create(
                [
                    'property_id'   => $this->FirstPropertyObj->id,
                    'native_coa_id' => $this->FirstPropertyObj->client->nativeCoas->first()->id,
                ]
            );
        }
        $this->FirstPropertyObj->refresh();

        $this->ClientObj->updateConfig('ADVANCED_VARIANCE', true);
        $this->ClientObj->updateConfig('ADVANCED_VARIANCE_REVIEWERS', [$this->FirstGenericUserObj->email, $this->FirstAdminUserObj->email]);
        $this->ClientObj->updateConfig('ADVANCED_VARIANCE_FREQ', AdvancedVariance::PERIOD_TYPE_MONTHLY);
        $this->ClientObj->updateConfig('ADVANCED_VARIANCE_TRIGGER', AdvancedVariance::TRIGGER_MODE_MONTHLY);
        $this->ClientObj->updateConfig('ADVANCED_VARIANCE_COMPLETION_DATE_DAYS', 30);
        $this->ClientObj->updateConfig('ADVANCED_VARIANCE_THRESHOLD_MODE', AdvancedVariance::THRESHOLD_MODE_REPORT_TEMPLATE_ACCOUNT_GROUP);

        $this->ClientObj->refresh();
        /**
         * So.......
         * FirstAdminUserObj, ThirdGenericUserObj and FirstGenericUserObj have access to all buildings
         * SecondGenericUserObj has access to NO buildings
         */
        $advanced_variance_data_arr = $this->fakeAdvancedVarianceData(
            [
                'client_id'   => $this->ClientObj->id,
                'property_id' => $this->FirstPropertyObj->id,
            ],
            Seeder::PHPUNIT_FACTORY_NAME
        );

        $client_config_obj = $this->ClientObj->getConfigJSON();
        $client_id         = $this->ClientObj->id;
        $property_id       = $this->FirstPropertyObj->id;

        $expected_number_reviewers =
            count(
                array_filter(
                    $client_config_obj->ADVANCED_VARIANCE_REVIEWERS,
                    function ($item) use ($client_id, $property_id)
                    {
                        /** @var User $UserObj */
                        $UserObj = App::make(UserRepository::class)->findWhere(
                            [
                                'client_id' => $client_id,
                                'email'     => $item,
                            ]
                        )->first();
                        return $UserObj->canAccessProperty($property_id);
                    }
                )
            );
        /** @var AdvancedVariance $FirstPropertyAdvancedVarianceObj */
        $this->AdvancedVarianceRepositoryObj->setSuppressEvents(true);
        $FirstPropertyAdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->create(
            $advanced_variance_data_arr
        );
        $this->AdvancedVarianceRepositoryObj->setSuppressEvents(false);

        $this->assertFalse($FirstPropertyAdvancedVarianceObj->approved());
        /** @var Collection $ReviewerRelatedUserTypeObjArr */
        $ReviewerRelatedUserTypeObjArr = $FirstPropertyAdvancedVarianceObj->getRelatedUserTypes(
            AdvancedVariance::class,
            $FirstPropertyAdvancedVarianceObj->id,
            AdvancedVariance::REVIEWER
        );

        /**
         * check things match above config
         */
        $this->assertEquals(1, $ReviewerRelatedUserTypeObjArr->count());
        /** @var RelatedUserType $ReviewerRelatedUserTypeObj */
        $ReviewerRelatedUserTypeObj = $ReviewerRelatedUserTypeObjArr->first();
        $this->assertEquals(AdvancedVariance::REVIEWER, $ReviewerRelatedUserTypeObj->related_object_subtype);

        $this->assertEquals($expected_number_reviewers, $ReviewerRelatedUserTypeObj->getRelatedUsers()->count());
        $this->assertEquals(AdvancedVariance::REVIEWER, $ReviewerRelatedUserTypeObj->related_object_subtype);

        /**
         * add relations and check
         */
        $FirstPropertyAdvancedVarianceObj->add_reviewer($this->ThirdGenericUserObj->id);
        $FirstPropertyAdvancedVarianceObj->refresh();

        /** @var Collection $ReviewerRelatedUserTypeObjArr */
        $ReviewerRelatedUserTypeObjArr = $FirstPropertyAdvancedVarianceObj->getRelatedUserTypes(AdvancedVariance::class, null, AdvancedVariance::REVIEWER);

        $this->assertFalse($FirstPropertyAdvancedVarianceObj->approved());
        /** @var RelatedUserType $ReviewerRelatedUserTypeObj */
        $ReviewerRelatedUserTypeObj = $ReviewerRelatedUserTypeObjArr->first();
        $this->assertEquals($expected_number_reviewers + 1, $ReviewerRelatedUserTypeObj->getRelatedUsers()->count());

        /**
         * remove relations and check
         */
        $FirstPropertyAdvancedVarianceObj->remove_reviewer($this->ThirdGenericUserObj->id);
        $FirstPropertyAdvancedVarianceObj->refresh();
        $this->assertEquals($expected_number_reviewers, $FirstPropertyAdvancedVarianceObj->getReviewers()->count());

        /** @var Collection $ReviewerRelatedUserTypeObjArr */
        $ReviewerRelatedUserTypeObjArr = $FirstPropertyAdvancedVarianceObj->getRelatedUserTypes(AdvancedVariance::class, null, AdvancedVariance::REVIEWER);

        /** @var RelatedUserType $ReviewerRelatedUserTypeObj */
        $ReviewerRelatedUserTypeObj = $ReviewerRelatedUserTypeObjArr->first();
        $this->assertEquals($expected_number_reviewers, $ReviewerRelatedUserTypeObj->getRelatedUsers()->count());

        /**
         * approve it
         */
        $FirstPropertyAdvancedVarianceObj->approve($this->ThirdGenericUserObj->id);
        $this->assertTrue($FirstPropertyAdvancedVarianceObj->approved());
        $FirstPropertyAdvancedVarianceObj->refresh();

        /** @var Collection $ReviewerRelatedUserTypeObjArr */
        $ReviewerRelatedUserTypeObjArr = $FirstPropertyAdvancedVarianceObj->getRelatedUserTypes(AdvancedVariance::class, null, AdvancedVariance::REVIEWER);

        /** @var RelatedUserType $ReviewerRelatedUserTypeObj */
        $ReviewerRelatedUserTypeObj = $ReviewerRelatedUserTypeObjArr->first();
        $this->assertEquals($expected_number_reviewers + 1, $ReviewerRelatedUserTypeObj->getRelatedUsers()->count());

        /**
         * unapprove it
         */
        $FirstPropertyAdvancedVarianceObj->unapprove($this->ThirdGenericUserObj->id);
        $FirstPropertyAdvancedVarianceObj->remove_reviewer($this->ThirdGenericUserObj->id);
        $FirstPropertyAdvancedVarianceObj->refresh();

        /** @var Collection $ReviewerRelatedUserTypeObjArr */
        $ReviewerRelatedUserTypeObjArr = $FirstPropertyAdvancedVarianceObj->getRelatedUserTypes(AdvancedVariance::class, null, AdvancedVariance::REVIEWER);

        /** @var RelatedUserType $ReviewerRelatedUserTypeObj */
        $ReviewerRelatedUserTypeObj = $ReviewerRelatedUserTypeObjArr->first();
        $this->assertEquals($expected_number_reviewers, $ReviewerRelatedUserTypeObj->getRelatedUsers()->count());

        $FirstPropertyAdvancedVarianceObj->refresh();
        /**
         * set up for compleation
         */
        /** @var App\Waypoint\Models\AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
        foreach ($FirstPropertyAdvancedVarianceObj->advancedVarianceLineItems as $AdvancedVarianceLineItemObj)
        {
            if ($AdvancedVarianceLineItemObj->flagged_via_policy || $AdvancedVarianceLineItemObj->flagged_manually)
            {
                $this->AdvancedVarianceLineItemRepositoryObj->update(
                    [
                        'explanation'      => Seeder::getFakerObj()->words(5, true),
                        'resolver_user_id' => $this->FirstGenericUserObj->id,
                        'resolved_date'    => Seeder::getFakerObj()->dateTimeBetween($startDate = '+3 months', $endDate = '+4 months')->format('Y-m-d H:i:s'),
                    ],
                    $AdvancedVarianceLineItemObj->id
                );
            }
        }
        $FirstPropertyAdvancedVarianceObj->approve($this->ThirdGenericUserObj->id);

        $FirstPropertyAdvancedVarianceObj->mark_locked($this->ThirdGenericUserObj->id);
        $FirstPropertyAdvancedVarianceObj->refresh();
        $this->assertTrue($FirstPropertyAdvancedVarianceObj->locked());

        $FirstPropertyAdvancedVarianceObj->mark_unlocked($this->ThirdGenericUserObj->id);
        $FirstPropertyAdvancedVarianceObj->refresh();
        $this->assertFalse($FirstPropertyAdvancedVarianceObj->locked());
    }

    /**
     * @test
     */
    public function is_spreadsheet_data_is_valid()
    {
        /**
         * reset our objects to clear the optimistic cache
         */
        $this->ClientObj = $this->ClientRepositoryObj->find($this->ClientObj->id);

        /** @var AdvancedVariance $AdvancedVarianceObj */

        $AdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->getAdvancedVariancesWithClientId($this->ClientObj->id)->first();

        $AdvancedVarianceLineItems = Spreadsheet::packageAdvancedVarianceLineItemData($AdvancedVarianceObj);
        $this->assertNotCount(0, $AdvancedVarianceLineItems);

        foreach ($AdvancedVarianceLineItems as $AdvancedVarianceLineItem)
        {
            $this->assertArrayHasKey('Account Code', $AdvancedVarianceLineItem);
            $this->assertArrayHasKey('Account Name', $AdvancedVarianceLineItem);
            $this->assertArrayHasKey('YTD Actual ($)', $AdvancedVarianceLineItem);
            $this->assertArrayHasKey('YTD Budget ($)', $AdvancedVarianceLineItem);
            $this->assertArrayHasKey('YTD Variance ($)', $AdvancedVarianceLineItem);
            $this->assertArrayHasKey('YTD Variance (%)', $AdvancedVarianceLineItem);
            $this->assertArrayHasKey('YTD Explanation', $AdvancedVarianceLineItem);

            $this->assertNotEmpty($AdvancedVarianceLineItem['Account Code']);
            $this->assertNotEmpty($AdvancedVarianceLineItem['Account Name']);

            $this->assertNotNull($AdvancedVarianceLineItem['YTD Actual ($)']);
            $this->assertNotNull($AdvancedVarianceLineItem['YTD Budget ($)']);
            $this->assertNotNull($AdvancedVarianceLineItem['YTD Variance ($)']);
            $this->assertNotNull($AdvancedVarianceLineItem['YTD Variance (%)']);
        }
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
