<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\CurlServiceTrait;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\NotificationLog;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroupProperty;
use App\Waypoint\Models\RelatedUserType;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Notifications\AdvancedVarianceApprovedNotification;
use App\Waypoint\Notifications\AdvancedVarianceLineItemExplanationNotification;
use App\Waypoint\Notifications\AdvancedVarianceLineItemFlaggedNotification;
use App\Waypoint\Notifications\AdvancedVarianceLineItemResolvedNotification;
use App\Waypoint\Notifications\AdvancedVarianceLockedNotification;
use App\Waypoint\Notifications\Facades\Notification;
use App\Waypoint\Repositories\AccessListDetailRepository;
use App\Waypoint\Repositories\AdvancedVarianceApprovalRepository;
use App\Waypoint\Repositories\AdvancedVarianceLineItemRepository;
use App\Waypoint\Repositories\AdvancedVarianceRepository;
use App\Waypoint\Repositories\NotificationLogRepository;
use App\Waypoint\Repositories\PropertyGroupPropertyRepository;
use App\Waypoint\Repositories\UserRepository;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListPropertyTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeAccessListUserTrait;
use App\Waypoint\Tests\Generated\MakeAdvancedVarianceApprovalTrait;
use App\Waypoint\Tests\Generated\MakeAdvancedVarianceTrait;
use App\Waypoint\Tests\Generated\MakePropertyGroupPropertyTrait;
use App\Waypoint\Tests\Generated\MakePropertyGroupTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\Generated\MakeRelatedUserTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\MakeAttachmentTrait;
use App\Waypoint\Tests\TestCase;
use DB;
use function preg_quote;

/**
 * Class AdvancedVarianceApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class AdvancedVarianceApiTest extends TestCase
{
    use MakeAccessListTrait, ApiTestTrait;
    use MakePropertyTrait;
    use MakeUserTrait;
    use MakeAccessListUserTrait;
    use MakeAccessListPropertyTrait;
    use MakeAdvancedVarianceTrait;
    use MakeRelatedUserTrait;
    use MakeAdvancedVarianceApprovalTrait;
    use CurlServiceTrait;
    use MakeAttachmentTrait;
    use MakePropertyGroupPropertyTrait;
    use MakePropertyGroupTrait;

    protected $ClientRepositoryObj;
    /** @var  AdvancedVarianceRepository */
    protected $AdvancedVarianceRepositoryObj;
    /** @var  AdvancedVarianceLineItemRepository */
    protected $AdvancedVarianceLineItemRepositoryObj;
    /** @var  AdvancedVarianceApprovalRepository */
    protected $AdvancedVarianceApprovalRepositoryObj;
    /** @var  UserRepository */
    protected $UserRepositoryObj;
    /** @var  Property */
    protected $PropertyRepositoryObj;
    /** @var  AccessListDetailRepository */
    protected $AccessListDetailRepositoryObj;
    /** @var  PropertyGroupPropertyRepository */
    protected $PropertyGroupPropertyRepositoryObj;
    /** @var User */
    protected $FirstGenericUserObj;
    /** @var User */
    protected $SecondGenericUserObj;
    /** @var User */
    protected $ThirdGenericUserObj;
    /** @var User */
    protected $FirstAdminUserObj;
    /** @var User */
    protected $SecondAdminUserObj;
    /** @var Client */
    protected $ClientObj;
    /** @var Property */
    protected $FirstPropertyObj;
    /** @var Property */
    protected $SecondPropertyObj;
    /** @var NotificationLogRepository */
    protected $NotificationLogRepositoryObj;
    /** @var AdvancedVariance */
    protected $FirstPropertyFirstAdvancedVarianceObj;
    /** @var int */
    protected $original_num_advanced_variances_first_property;

    protected $num_line_items_flagged_via_policy;
    protected $unflagged_via_policy_advanced_variance_line_item_id;
    protected $flagged_via_policy_advanced_variance_line_item_id;
    protected $flagged_manually_advanced_variance_line_item_id;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        parent::setUp();

        $this->ClientRepositoryObj->initAdvancedVariance($this->ClientObj->id, true);
        $this->getLoggedInUserObj()->setAllAdvancedVarianceNotificationConfigs(true);

        $this->ClientObj->addUserToAllAccessList($this->getLoggedInUserObj()->id);
        /** @var User $this ->FirstGenericUserObj */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->ClientObj->addUserToAllAccessList($this->FirstGenericUserObj->id);
        $this->FirstGenericUserObj->setAllAdvancedVarianceNotificationConfigs(true);
        $this->FirstGenericUserObj->refresh();
        $this->FirstGenericUserObj = $this->UserRepositoryObj->find($this->FirstGenericUserObj->id);

        $this->logInUser($this->FirstGenericUserObj);

        /** @var User $this ->SecondGenericUserObj */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->SecondGenericUserObj->setAllAdvancedVarianceNotificationConfigs(true);
        /** @var User $this ->ThirdGenericUserObj */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->ThirdGenericUserObj->setAllAdvancedVarianceNotificationConfigs(true);

        /** @var User $this ->FirstAdminUserObj */
        /** @noinspection PhpUndefinedMethodInspection */
        $this->FirstAdminUserObj->setAllAdvancedVarianceNotificationConfigs(true);
        $this->FirstAdminUserObj->refresh();

        $this->FifthGenericUserObj->setAllAdvancedVarianceNotificationConfigs(true);

        $this->ClientObj->fresh();

        $this->FirstPropertyFirstAdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->getAdvancedVariancesWithClientId($this->ClientObj->id)->first();
        $this->FirstPropertyFirstAdvancedVarianceObj->add_reviewer($this->SecondGenericUserObj->id);
        $this->FirstPropertyFirstAdvancedVarianceObj->add_reviewer($this->ThirdGenericUserObj->id);
        $this->FirstPropertyFirstAdvancedVarianceObj->add_reviewer($this->FirstAdminUserObj->id);
        $this->FirstPropertyFirstAdvancedVarianceObj->fresh();
        $this->FirstPropertyObj = $this->FirstPropertyFirstAdvancedVarianceObj->property;

        $this->original_num_advanced_variances_first_property = $this->FirstPropertyObj->advancedVariances->count();
        $this->logInUser($this->FirstGenericUserObj);
        $this->update_pointers_and_counters();
    }

    /**
     * @test
     */
    public function it_can_check_client_config()
    {
        /**
         * no point in running tests if queues are on, unless your running workers but even then,
         * you might get a nasty race condition
         */
        $this->assertTrue(config('queue.driver', 'sync') == 'sync');

        $client_config_arr = $this->ClientObj->getConfigJSON(true);
        $this->assertTrue(isset($client_config_arr['ADVANCED_VARIANCE_REVIEWERS']));
        $this->assertTrue(is_array($client_config_arr['ADVANCED_VARIANCE_REVIEWERS']));
        $this->assertTrue(isset($client_config_arr['ADVANCED_VARIANCE_THRESHOLD_MODE']));
        $this->assertTrue(isset($client_config_arr['ADVANCED_VARIANCE_CLOSE_DATE_DAYS']));
    }

    /**
     * @test
     */
    public function it_can_get_advanced_variances()
    {
        /**
         * no point in running tests if queues are on, unless your running workers but even then,
         * you might get a nasty race condition
         */
        $this->assertTrue(config('queue.driver', 'sync') == 'sync');

        /*
         * query AdvancedVariance
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id . '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id
        );
        $this->assertApiSuccess();

        /*
         * query advancedVariancesDetails
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id . '/advancedVariancesDetail/' . $this->FirstPropertyFirstAdvancedVarianceObj->id
        );
        $this->assertApiSuccess();
        $this->assertEquals(0, $this->FirstPropertyFirstAdvancedVarianceObj->advancedVarianceApprovals->count());
        $this->assertFalse($this->FirstPropertyFirstAdvancedVarianceObj->approved());
    }

    /**
     * @test
     */
    public function it_can_process_reviewers()
    {
        /**
         * no point in running tests if queues are on, unless your running workers but even then,
         * you might get a nasty race condition
         */
        $this->assertTrue(config('queue.driver', 'sync') == 'sync');

        $this->FirstPropertyFirstAdvancedVarianceObj->refresh();
        $this->FirstPropertyFirstAdvancedVarianceObj->add_reviewer($this->FifthAdminUserObj->id);

        /*************************************************************
         * reviewers
         */
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/reviewers',
            [
                'user_id' => $this->ThirdGenericUserObj->id,
            ]
        );
        $this->assertApiFailure();

        $this->ClientObj->addUserToAllAccessList($this->ThirdGenericUserObj->id);
        $this->ThirdGenericUserObj->updateConfig('NOTIFICATIONS', 1);
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/reviewers',
            [
                'user_id' => $this->ThirdGenericUserObj->id,
            ]
        );
        $this->assertApiSuccess();

        $reviewer_user_id = null;
        foreach ($this->getFirstDataObject()['relatedUserTypes'] as $relatedUserType)
        {
            if ($relatedUserType['related_object_subtype'] == AdvancedVariance::REVIEWER)
            {
                foreach ($relatedUserType['users'] as $user_arr)
                {
                    $reviewer_user_id = $user_arr['related_user_id'];
                    break 2;
                }
            }
        }
        $this->assertNotNull($reviewer_user_id);
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id
        );
        $this->assertApiSuccess();
        $this->FirstPropertyFirstAdvancedVarianceObj->refresh();

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/reviewers/' . $reviewer_user_id
        );
        $this->assertApiSuccess();
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id
        );
        $this->assertApiSuccess();

        $reviewer_user_id = null;
        foreach ($this->getFirstDataObject()['relatedUserTypes'] as $relatedUserType)
        {
            if ($relatedUserType['related_object_subtype'] == AdvancedVariance::REVIEWER)
            {
                foreach ($relatedUserType['users'] as $user_arr)
                {
                    $reviewer_user_id = $user_arr['related_user_id'];
                    break 2;
                }
            }
        }
        $this->assertNotNull($reviewer_user_id);

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/reviewers/' . $reviewer_user_id
        );
    }

    /**
     * @test
     */
    public function it_cannot_delete_last_reviewer()
    {
        /**
         * no point in running tests if queues are on, unless your running workers but even then,
         * you might get a nasty race condition
         */
        $this->assertTrue(config('queue.driver', 'sync') == 'sync');

        $ReviewerObjArr = $this->FirstPropertyFirstAdvancedVarianceObj->getRelatedUsers();
        $cnt            = 0;
        foreach ($ReviewerObjArr as $ReviewerObj)
        {
            $this->json(
                'DELETE',
                '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
                '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/reviewers/' . $ReviewerObj->id
            );
            $cnt++;
            if ($cnt == $ReviewerObjArr->count())
            {
                $this->assertApiFailure();
            }
            else
            {
                $this->assertApiSuccess();
            }
        }
    }

    /**
     * @test
     */
    public function it_can_flag()
    {

        /**
         * no point in running tests if queues are on, unless your running workers but even then,
         * you might get a nasty race condition
         */
        $this->assertTrue(config('queue.driver', 'sync') == 'sync');

        /*************************************************************
         * flags
         *
         * check inital state and check
         */
        $this->update_pointers_and_counters();
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/advancedVarianceLineItems/flagged'
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getDataObjectArr()) == $this->num_line_items_flagged_via_policy);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/advancedVarianceLineItems/flaggedManually'
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getDataObjectArr()) == 0);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/advancedVarianceLineItems/flaggedByPolicy'
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getDataObjectArr()) == $this->num_line_items_flagged_via_policy);

    }

    /**
     * @test
     */
    public function it_can_flag1()
    {
        /**
         * no point in running tests if queues are on, unless your running workers but even then,
         * you might get a nasty race condition
         */
        $this->assertTrue(config('queue.driver', 'sync') == 'sync');

        /**
         * flag something and check
         */
        $this->turn_on_fake_notifications();
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/advancedVarianceLineItems/' . $this->unflagged_via_policy_advanced_variance_line_item_id . '/flag',
            [
                'flagger_user_id' => $this->FirstGenericUserObj->id,
            ]
        );
        $this->assertApiSuccess();
        $AdvancedVarianceLineItemObj = AdvancedVarianceLineItem::find($this->unflagged_via_policy_advanced_variance_line_item_id);

        /**
         * no point in running this is not queue.driver = 'sync'. This an happen when we use unittests to generate queue activity.
         * queue.driver should always be 'sync'in unittest context
         */
        if (config('queue.driver', 'sync') == 'sync')
        {
            /** @var AdvancedVarianceLineItemFlaggedNotification $AdvancedVarianceLineItemFlaggedNotificationObj */
            /** @noinspection PhpUndefinedMethodInspection */
            Notification::assertSentTo(
                $this->FirstPropertyFirstAdvancedVarianceObj->getExpectedRecipiants(),
                AdvancedVarianceLineItemFlaggedNotification::class,
                function (AdvancedVarianceLineItemFlaggedNotification $AdvancedVarianceLineItemFlaggedNotificationObj) use (
                    $AdvancedVarianceLineItemObj
                )
                {
                    foreach ($AdvancedVarianceLineItemObj->advancedVariance->getExpectedRecipiants() as $ExpectedRecipiantUserObj)
                    {
                        $mail_arr = $AdvancedVarianceLineItemFlaggedNotificationObj->toMail($ExpectedRecipiantUserObj)->toArray();
                        $this->assertEquals('View Report', $mail_arr['actionText']);

                        // @Todo - Alex to enforce this test
                        //if ($AdvancedVarianceLineItemObj->nativeAccount)
                        //{
                        //    $pattern = '/' . preg_quote(
                        //            $AdvancedVarianceLineItemObj->nativeAccount->native_account_name
                        //        ) . '/';
                        //}
                        //else
                        //{
                        //    $pattern = '/' . preg_quote(
                        //            $AdvancedVarianceLineItemObj->reportTemplateAccountGroup->report_template_account_group_name
                        //        ) . '/';
                        //}
                        //$this->assertRegExp($pattern, $mail_arr['greeting']);

                        $pattern = '/' . preg_quote($AdvancedVarianceLineItemObj->advancedVariance->property->name) . '/';
                        $this->assertRegExp($pattern, $mail_arr['subject']);

                        $pattern = '/' .
                                   preg_quote(
                                       $AdvancedVarianceLineItemFlaggedNotificationObj->getBaseNotificationUrl() . '#/property/variance/reports/' .
                                       $AdvancedVarianceLineItemObj->advancedVariance->id . '?pureid=' . $AdvancedVarianceLineItemObj->advancedVariance->property_id,
                                       '/'
                                   ) .
                                   '/';
                        $this->assertRegExp($pattern, $mail_arr['actionUrl']);

                        $this->assertTrue(TestCase::is_syntactially_valid_url($mail_arr['actionUrl']));

                        /** @var App\Waypoint\Notifications\Notification $NotificatioLogObj */
                        if ( ! $NotificationLogObj = $this->NotificationLogRepositoryObj->findWhere(
                            ['notification_uuid' => $AdvancedVarianceLineItemFlaggedNotificationObj->id]
                        )->first())
                        {
                            $this->assertTrue(false);
                        }
                        $this->assertEquals($AdvancedVarianceLineItemFlaggedNotificationObj->id, $NotificationLogObj->notification_uuid);
                        $this->assertTrue(
                            in_array(
                                $NotificationLogObj->channel, $AdvancedVarianceLineItemFlaggedNotificationObj->via($ExpectedRecipiantUserObj)
                            )
                        );
                        break;
                    }
                    return $AdvancedVarianceLineItemFlaggedNotificationObj->getAdvancedVarianceLineItemObj()->id === $AdvancedVarianceLineItemObj->id;
                }
            );
        }

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/advancedVarianceLineItems/flagged'
        );
        $this->assertApiSuccess();
        /**
         * @todo fix this
         */
        //$this->assertTrue(count($this->getDataObjectArr()) == $this->num_line_items_flagged_via_policy + 1);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/advancedVarianceLineItems/flaggedManually'
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getDataObjectArr()) == 1);

        $this->FirstPropertyFirstAdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->find($this->FirstPropertyFirstAdvancedVarianceObj->id);

        $this->update_pointers_and_counters();
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/advancedVarianceLineItems/flaggedByPolicy'
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getDataObjectArr()) == $this->num_line_items_flagged_via_policy);

    }

    /**
     * @test
     */
    public function it_can_flag2()
    {
        /**
         * no point in running tests if queues are on, unless your running workers but even then,
         * you might get a nasty race condition
         */
        $this->assertTrue(config('queue.driver', 'sync') == 'sync');

        /**
         * @todo No UnflaggedNotification????? Talk to Peter
         *
         * unflag something and check
         */
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/advancedVarianceLineItems/' . $this->unflagged_via_policy_advanced_variance_line_item_id . '/unflag',
            []
        );
        $this->assertApiSuccess();

        $this->FirstPropertyFirstAdvancedVarianceObj = $this->AdvancedVarianceRepositoryObj->find($this->FirstPropertyFirstAdvancedVarianceObj->id);
        $this->update_pointers_and_counters();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/advancedVarianceLineItems/flagged'
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getDataObjectArr()) == $this->num_line_items_flagged_via_policy);
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/advancedVarianceLineItems/flaggedManually'
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getDataObjectArr()) == 0);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/advancedVarianceLineItems/flaggedByPolicy'
        );
        $this->assertApiSuccess();
        $this->assertTrue(count($this->getDataObjectArr()) == $this->num_line_items_flagged_via_policy);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id
        );

        $this->assertApiSuccess();

    }

    /**
     * @test
     */
    public function it_can_flag2_1()
    {
        /**
         * no point in running tests if queues are on, unless your running workers but even then,
         * you might get a nasty race condition
         */
        $this->assertTrue(config('queue.driver', 'sync') == 'sync');
        /**
         * lets find a $FlaggedAndUnlockedAdvancedVarianceObj
         */
        /** @var AdvancedVariance $FlaggedAndUnlockedAdvancedVarianceObj */
        foreach ($this->AdvancedVarianceRepositoryObj->getAdvancedVariancesWithClientId($this->ClientObj->id) as $FlaggedAndUnlockedAdvancedVarianceObj)
        {
            if (
                ! $FlaggedAndUnlockedAdvancedVarianceObj->getFlagged()->count() ||
                $FlaggedAndUnlockedAdvancedVarianceObj->locked())
            {
                continue;
            }
            break;
        }

        $FlaggedAndUnlockedAdvancedVarianceObj->add_reviewer($this->FourthGenericUserObj->id);
        if ($FlaggedAndUnlockedAdvancedVarianceObj->getFlagged()->count() == 0)
        {
            /** @var AdvancedVarianceLineItem $AdvancedVarianceLineItemObj */
            $i = 1;
            foreach ($FlaggedAndUnlockedAdvancedVarianceObj->advancedVarianceLineItems as $AdvancedVarianceLineItemObj)
            {
                if ($AdvancedVarianceLineItemObj->nativeAccount)
                {
                    $AdvancedVarianceLineItemObj->monthly_budgeted = 1000;
                    $AdvancedVarianceLineItemObj->monthly_actual   = 10000000000;
                    $AdvancedVarianceLineItemObj->flagged_manually = true;
                    $AdvancedVarianceLineItemObj->save();
                    if ($i++ > 5)
                    {
                        break;
                    }
                }
            }

            $this->post_job_to_queue(
                [
                    'advanced_variance_id'           => $AdvancedVarianceLineItemObj->advancedVariance->id,
                    'advanced_variance_line_item_id' => $AdvancedVarianceLineItemObj->id,
                    'as_of_month'                    => $AdvancedVarianceLineItemObj->advancedVariance->as_of_month,
                    'as_of_year'                     => $AdvancedVarianceLineItemObj->advancedVariance->as_of_year,
                    'recipient_id_arr'               => $AdvancedVarianceLineItemObj->advancedVariance->getExpectedRecipiants()->pluck('id')->toArray(),
                ],
                App\Waypoint\Jobs\AdvancedVarianceLineItemRefreshJob::class,
                config('queue.queue_lanes.AdvancedVarianceLineItemRefresh', false)
            );
        }
        $FlaggedAndUnlockedAdvancedVarianceObj->refresh();
        $this->assertGreaterThan(0, $FlaggedAndUnlockedAdvancedVarianceObj->getFlagged()->count(), 'No flagged $FlaggedAndUnlockedAdvancedVarianceObj found');
        $this->assertFalse($FlaggedAndUnlockedAdvancedVarianceObj->locked(), 'No unlocked $FlaggedAndUnlockedAdvancedVarianceObj found');

        /*************************************************************
         * completion
         */
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id . '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/lock',
            [
                'locker_user_id' => $this->FirstGenericUserObj->id,
            ]
        );
        $this->assertApiFailure();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/workflow'
        );
        $this->assertApiSuccess();

        $line_item_arr_wf = $this->JSONContent['data']['AdvancedVarianceLineItemWorkflows'];

        /**
         * now add some comments
         */
        $CommentAdvancedVarianceLineItemObj = $FlaggedAndUnlockedAdvancedVarianceObj->advancedVarianceLineItems->random();
        /**
         * without mentions
         */
        $this->turn_on_fake_notifications();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/advancedVarianceLineItems/' . $CommentAdvancedVarianceLineItemObj->id . '/comments',
            [
                'commentable_id'   => $CommentAdvancedVarianceLineItemObj->id,
                'commentable_type' => 'App\\Waypoint\\Models\\AdvancedVarianceLineItem',
                'comment'          => Seeder::getFakerObj()->words(20, true) . '[~' . $this->FifthGenericUserObj->id . ']',
            ]
        );
        $this->assertApiSuccess();

        /**
         * with mentions
         */
        $this->turn_on_fake_notifications();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/advancedVarianceLineItems/' . $CommentAdvancedVarianceLineItemObj->id . '/comments',
            [
                'commentable_id'   => $CommentAdvancedVarianceLineItemObj->id,
                'commentable_type' => 'App\\Waypoint\\Models\\AdvancedVarianceLineItem',
                'comment'          => Seeder::getFakerObj()->words(20, true) . '[~' . $this->FifthGenericUserObj->id . ']',
                'mentions'         => [$this->FifthGenericUserObj->id],
            ]
        );
        $this->assertApiSuccess();
        /** @var AdvancedVarianceLineItemExplanationNotification $AdvancedVarianceLineItemExplanationNotificationObj */
        $FlaggedAndUnlockedAdvancedVarianceObj->refresh();

        $ExpectedRecipiants = $FlaggedAndUnlockedAdvancedVarianceObj->getExpectedRecipiants();
        $ExpectedRecipiants[] = $this->FifthGenericUserObj;
        $ExpectedRecipiants[] = $this->getLoggedInUserObj();
        /** @noinspection PhpUndefinedMethodInspection */
        Notification::assertSentTo(
            $ExpectedRecipiants,
            App\Waypoint\Notifications\AdvancedVarianceLineItemCommentNotification::class,
            function (App\Waypoint\Notifications\AdvancedVarianceLineItemCommentNotification $AdvancedVarianceLineItemCommentNotificationObj
            ) use ($FlaggedAndUnlockedAdvancedVarianceObj, $CommentAdvancedVarianceLineItemObj)
            {
                foreach ($FlaggedAndUnlockedAdvancedVarianceObj->getExpectedRecipiants() as $ExpectedRecipiantUserObj)
                {
                    $mail_arr = $AdvancedVarianceLineItemCommentNotificationObj->toMail(
                        $ExpectedRecipiantUserObj
                    )->toArray();

                    $pattern = '/View Report/';
                    $this->assertRegExp($pattern, $mail_arr['actionText']);
                    break;
                }
                return $AdvancedVarianceLineItemCommentNotificationObj->getAdvancedVarianceLineItemObj()->id === $CommentAdvancedVarianceLineItemObj->id;
            }
        );

        $i = 0;

        $rtag_tested_flag = false;
        $na_tested_flag   = false;
        $cf_tested_flag   = false;
        /**
         * no point in running this is not queue.driver = 'sync'. This an happen when we use unittests to generate queue activity.
         * queue.driver should always be 'sync'in unittest context
         */

        if (config('queue.driver', 'sync') == 'sync')
        {
            foreach ($line_item_arr_wf as $line_item)
            {
                if ($line_item['flagged_via_policy'] || $line_item['flagged_manually'])
                {

                    $this->turn_on_fake_notifications();

                    if (
                        ($line_item['report_template_account_group_id'] && $rtag_tested_flag) ||
                        ($line_item['native_account_id'] && $na_tested_flag) ||
                        ($line_item['calculated_field_id'] && $cf_tested_flag)
                    )
                    {
                        continue;
                    }

                    $this->json(
                        'PUT',
                        '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id .
                        '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/advancedVarianceLineItems/' . $line_item['id'] . '/explanation/',
                        [
                            'explanation' => Seeder::getFakerObj()->words(5, true),
                        ]
                    );
                    $this->assertApiSuccess();
                    $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->find($line_item['id']);
                    $this->assertNotNull($AdvancedVarianceLineItemObj->explanation_update_date);
                    $this->assertNotNull($AdvancedVarianceLineItemObj->explainer_id);

                    $this->json(
                        'GET',
                        '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceExplanationTypes'
                    );
                    $this->assertApiSuccess();
                    $this->assertGreaterThan(1, count($this->getJSONContent()['data']));

                    $advanced_variance_explanation_type_id = $this->getFirstDataObject()['id'];

                    $this->json(
                        'PUT',
                        '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id .
                        '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/advancedVarianceLineItems/' . $line_item['id'] . '/explanation/',
                        [
                            'advanced_variance_explanation_type_id' => $advanced_variance_explanation_type_id,
                        ]
                    );
                    $this->assertApiSuccess();
                    $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->find($line_item['id']);
                    $this->assertNotNull($AdvancedVarianceLineItemObj->explanation_type_date);
                    $this->assertNotNull($AdvancedVarianceLineItemObj->explanation_type_user_id);
                    $this->assertNotNull($AdvancedVarianceLineItemObj->advanced_variance_explanation_type_id);

                    if ($i == 0)
                    {
                        /**
                         * now remove explanation type
                         */
                        $this->json(
                            'PUT',
                            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id .
                            '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/advancedVarianceLineItems/' . $line_item['id'] . '/explanation/',
                            [
                                'advanced_variance_explanation_type_id' => null,
                            ]
                        );
                        $this->assertApiSuccess();
                        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->find($line_item['id']);
                        $this->assertNull($AdvancedVarianceLineItemObj->explanation_type_date);
                        $this->assertNull($AdvancedVarianceLineItemObj->explanation_type_user_id);
                        $this->assertNull($AdvancedVarianceLineItemObj->advanced_variance_explanation_type_id);

                        /**
                         * now put it back
                         */
                        $this->json(
                            'PUT',
                            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id .
                            '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/advancedVarianceLineItems/' . $line_item['id'] . '/explanation/',
                            [
                                'advanced_variance_explanation_type_id' => $advanced_variance_explanation_type_id,
                            ]
                        );
                        $this->assertApiSuccess();
                        $AdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->find($line_item['id']);
                        $this->assertNotNull($AdvancedVarianceLineItemObj->explanation_type_date);
                        $this->assertNotNull($AdvancedVarianceLineItemObj->explanation_type_user_id);
                        $this->assertNotNull($AdvancedVarianceLineItemObj->advanced_variance_explanation_type_id);
                    }

                    /**
                     * no point in running this is not queue.driver = 'sync'. This an happen when we use unittests to generate queue activity.
                     * queue.driver should always be 'sync'in unittest context
                     */
                    if ($i == 0 && config('queue.driver', 'sync') == 'sync')
                    {
                        /** @var AdvancedVarianceLineItemExplanationNotification $AdvancedVarianceLineItemExplanationNotificationObj */
                        /** @noinspection PhpUndefinedMethodInspection */
                        Notification::assertSentTo(
                            $FlaggedAndUnlockedAdvancedVarianceObj->getExpectedRecipiants(),
                            AdvancedVarianceLineItemExplanationNotification::class,
                            function (AdvancedVarianceLineItemExplanationNotification $AdvancedVarianceLineItemExplanationNotificationObj
                            ) use ($AdvancedVarianceLineItemObj)
                            {
                                foreach ($AdvancedVarianceLineItemObj->advancedVariance->getExpectedRecipiants() as $ExpectedRecipiantUserObj)
                                {
                                    $mail_arr = $AdvancedVarianceLineItemExplanationNotificationObj->toMail(
                                        $ExpectedRecipiantUserObj
                                    )->toArray();

                                    $pattern = '/View Report/';
                                    $this->assertRegExp($pattern, $mail_arr['actionText']);

                                    $pattern = '/' . preg_quote($AdvancedVarianceLineItemObj->advancedVariance->property_id) . '/';
                                    $this->assertRegExp($pattern, $mail_arr['actionUrl']);

                                    $pattern = '/ entered an explanation for /';
                                    $this->assertRegExp($pattern, $mail_arr['greeting']);

                                    $pattern = '/' . preg_quote($AdvancedVarianceLineItemObj->advancedVariance->property->name) . '/';
                                    $this->assertRegExp($pattern, $mail_arr['subject']);

                                    $pattern = '/' . preg_quote(
                                            $AdvancedVarianceLineItemExplanationNotificationObj->getBaseNotificationUrl() . '#/property/variance/reports/' . $AdvancedVarianceLineItemObj->advancedVariance->id . '?pureid=' . $AdvancedVarianceLineItemObj->advancedVariance->property_id,
                                            '/'
                                        ) . '/';
                                    $this->assertRegExp($pattern, $mail_arr['actionUrl']);

                                    $this->assertTrue(TestCase::is_syntactially_valid_url($mail_arr['actionUrl']));

                                    /** @var App\Waypoint\Notifications\Notification $NotificatioLogObj */
                                    if ( ! $NotificationLogObj = $this->NotificationLogRepositoryObj->findWhere(
                                        ['notification_uuid' => $AdvancedVarianceLineItemExplanationNotificationObj->id]
                                    )->first())
                                    {
                                        $this->assertTrue(false);
                                    }
                                    $this->assertEquals(
                                        $AdvancedVarianceLineItemExplanationNotificationObj->id, $NotificationLogObj->notification_uuid
                                    );
                                    $this->assertTrue(
                                        in_array(
                                            $NotificationLogObj->channel,
                                            $AdvancedVarianceLineItemExplanationNotificationObj->via($ExpectedRecipiantUserObj)
                                        )
                                    );
                                    break;
                                }
                                return $AdvancedVarianceLineItemExplanationNotificationObj->getAdvancedVarianceLineItemObj()->id === $AdvancedVarianceLineItemObj->id;
                            }
                        );
                    }
                    $this->assertApiSuccess();

                    $this->turn_on_fake_notifications();
                    $this->json(
                        'PUT',
                        '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id .
                        '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/advancedVarianceLineItems/' . $line_item['id'] . '/resolve',
                        [
                            'resolver_user_id' => $this->FirstAdminUserObj->id,
                        ]
                    );
                    $this->assertApiSuccess();

                    $AdvancedVarianceLineItemObj = AdvancedVarianceLineItem::find($line_item['id']);

                    /**
                     * no point in running this is not queue.driver = 'sync'. This an happen when we use unittests to generate queue activity.
                     * queue.driver should always be 'sync'in unittest context
                     */
                    if ($i == 0 && config('queue.driver', 'sync') == 'sync')
                    {
                        /** @var AdvancedVarianceLineItemResolvedNotification $AdvancedVarianceLineItemResolvedNotificationObj */
                        /** @noinspection PhpUndefinedMethodInspection */
                        Notification::assertSentTo(
                            $FlaggedAndUnlockedAdvancedVarianceObj->getExpectedRecipiants(),
                            AdvancedVarianceLineItemResolvedNotification::class,
                            function (AdvancedVarianceLineItemResolvedNotification $AdvancedVarianceLineItemResolvedNotificationObj) use (
                                $AdvancedVarianceLineItemObj
                            )
                            {
                                foreach ($AdvancedVarianceLineItemObj->advancedVariance->getExpectedRecipiants() as $ExpectedRecipiantUserObj)
                                {
                                    $mail_arr = $AdvancedVarianceLineItemResolvedNotificationObj->toMail(
                                        $ExpectedRecipiantUserObj
                                    )->toArray();
                                    $this->assertEquals('View Report', $mail_arr['actionText']);

                                    $pattern = '/' . preg_quote($AdvancedVarianceLineItemObj->resolverUser->getDisplayName()) . '/';
                                    $this->assertRegExp($pattern, $mail_arr['greeting']);

                                    $pattern = '/' . preg_quote($AdvancedVarianceLineItemObj->advancedVariance->property->name) . '/';
                                    $this->assertRegExp($pattern, $mail_arr['subject']);

                                    $pattern = '/' .
                                               preg_quote(
                                                   $AdvancedVarianceLineItemResolvedNotificationObj->getBaseNotificationUrl() . '#/property/variance/reports/' .
                                                   $AdvancedVarianceLineItemObj->advancedVariance->id . '?pureid=' . $AdvancedVarianceLineItemObj->advancedVariance->property_id,
                                                   '/'
                                               ) .
                                               '/';
                                    $this->assertRegExp($pattern, $mail_arr['actionUrl']);

                                    $this->assertTrue(TestCase::is_syntactially_valid_url($mail_arr['actionUrl']));

                                    /** @var App\Waypoint\Notifications\Notification $NotificatioLogObj */
                                    if ( ! $NotificationLogObj = $this->NotificationLogRepositoryObj->findWhere(
                                        ['notification_uuid' => $AdvancedVarianceLineItemResolvedNotificationObj->id]
                                    )->first())
                                    {
                                        $this->assertTrue(false);
                                    }
                                    /** to foil the 10 minute rule in codeship */
                                    $this->assertEquals(
                                        $AdvancedVarianceLineItemResolvedNotificationObj->id, $NotificationLogObj->notification_uuid
                                    );
                                    $this->assertTrue(
                                        in_array(
                                            $NotificationLogObj->channel,
                                            $AdvancedVarianceLineItemResolvedNotificationObj->via($ExpectedRecipiantUserObj)
                                        )
                                    );
                                    break;
                                }
                                return $AdvancedVarianceLineItemResolvedNotificationObj->getAdvancedVarianceLineItemObj()->id === $AdvancedVarianceLineItemObj->id;
                            }
                        );
                    }
                    if ($i == 0)
                    {
                        $this->json(
                            'PUT',
                            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id .
                            '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/advancedVarianceLineItems/' . $line_item['id'] . '/unresolve',
                            [
                                'resolver_user_id' => $this->FirstAdminUserObj->id,
                            ]
                        );
                        $this->assertApiSuccess();

                        $this->json(
                            'PUT',
                            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id .
                            '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/advancedVarianceLineItems/' . $line_item['id'] . '/resolve',
                            [
                                'resolver_user_id' => $this->FirstAdminUserObj->id,
                            ]
                        );
                        $this->assertApiSuccess();
                    }
                    $i++;

                    if ($line_item['report_template_account_group_id'])
                    {
                        $rtag_tested_flag = true;
                    }
                    elseif ($line_item['native_account_id'])
                    {
                        $na_tested_flag = true;
                    }
                    elseif ($line_item['calculated_field_id'])
                    {
                        $cf_tested_flag = true;
                    }
                }
            }
        }

        /*************************************************************
         * approval
         */
        /** @var  array $advanced_variance_approvals_arr */
        $advanced_variance_approvals_arr = $this->fakeAdvancedVarianceApprovalData(
            [
                'advanced_variance_id' => $FlaggedAndUnlockedAdvancedVarianceObj->id,
            ]
        );
        unset($advanced_variance_approvals_arr['approving_user_id']);

        $this->turn_on_fake_notifications();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id . '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/approvals',
            $advanced_variance_approvals_arr
        );
        $this->assertApiSuccess();

        /**
         * no point in running this is not queue.driver = 'sync'. This an happen when we use unittests to generate queue activity.
         * queue.driver should always be 'sync'in unittest context
         */
        if (config('queue.driver', 'sync') == 'sync')
        {
            /** @var AdvancedVarianceApprovedNotification $AdvancedVarianceApprovedNotificationObj */
            /** @noinspection PhpUndefinedMethodInspection */
            $NotificationAdvancedVarianceObj = $FlaggedAndUnlockedAdvancedVarianceObj;
            Notification::assertSentTo(
                $FlaggedAndUnlockedAdvancedVarianceObj->getExpectedRecipiants(),
                AdvancedVarianceApprovedNotification::class,
                function (AdvancedVarianceApprovedNotification $AdvancedVarianceApprovedNotificationObj) use ($NotificationAdvancedVarianceObj)
                {
                    foreach ($NotificationAdvancedVarianceObj->getExpectedRecipiants() as $ExpectedRecipiantUserObj)
                    {
                        $mail_arr = $AdvancedVarianceApprovedNotificationObj->toMail($ExpectedRecipiantUserObj)->toArray();
                        $this->assertEquals('View Report', $mail_arr['actionText']);

                        $pattern = '/' . preg_quote($AdvancedVarianceApprovedNotificationObj->approver_display_name) . '/';
                        $this->assertRegExp($pattern, $mail_arr['greeting']);

                        $pattern = '/' . preg_quote($NotificationAdvancedVarianceObj->property->name) . '/';
                        $this->assertRegExp($pattern, $mail_arr['subject']);

                        $pattern = '/' .
                                   preg_quote(
                                       $AdvancedVarianceApprovedNotificationObj->getBaseNotificationUrl() . '#/property/variance/reports/' . $NotificationAdvancedVarianceObj->id . '?pureid=' . $NotificationAdvancedVarianceObj->property_id,
                                       '/'
                                   ) .
                                   '/';
                        $this->assertRegExp($pattern, $mail_arr['actionUrl']);

                        $this->assertTrue(TestCase::is_syntactially_valid_url($mail_arr['actionUrl']));
                        /** @var NotificationLog $NotificatioLogObj */
                        if ( ! $NotificatioLogObj = $this->NotificationLogRepositoryObj->findWhere(
                            ['notification_uuid' => $AdvancedVarianceApprovedNotificationObj->id]
                        )->first())
                        {
                            $this->assertTrue(false);
                        }

                        $this->assertEquals($AdvancedVarianceApprovedNotificationObj->id, $NotificatioLogObj->notification_uuid);
                        $this->assertTrue(in_array($NotificatioLogObj->channel, $AdvancedVarianceApprovedNotificationObj->via($ExpectedRecipiantUserObj)));

                        break;
                    }
                    return $AdvancedVarianceApprovedNotificationObj->getAdvancedVarianceObj()->id === $NotificationAdvancedVarianceObj->id;
                }
            );
        }

        $advanced_variance_approval_id = $this->JSONContent['data']['id'];
        $this->turn_on_fake_notifications();
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/approvals/' . $advanced_variance_approval_id,
            $advanced_variance_approvals_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $FlaggedAndUnlockedAdvancedVarianceObj->property_id . '/advancedVariances/' . $FlaggedAndUnlockedAdvancedVarianceObj->id . '/approvals',
            $advanced_variance_approvals_arr
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     */
    public function it_can_flag2_2()
    {
        $PropertyGroupObj = $this->getLoggedInUserObj()->allPropertyGroup;
        DB::update(
            DB::raw(
                '
                    UPDATE advanced_variance_line_items 
                        SET 
                            is_summary = 1
                        WHERE advanced_variance_id  = :ADVANCED_VARIANCE_ID
                '
            ),
            [
                'ADVANCED_VARIANCE_ID' => $this->FirstPropertyFirstAdvancedVarianceObj->id,
            ]
        );
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' . $PropertyGroupObj->id . '/advancedVariances/workflow?as_of_month=' . $this->FirstPropertyFirstAdvancedVarianceObj->as_of_month . '&as_of_year=' . $this->FirstPropertyFirstAdvancedVarianceObj->as_of_year
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyObj->id . '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/workflow'
        );
        $this->assertApiSuccess();

        $saved_responce_property_arr = $this->getJSONContent()['data'];

        /**
         * no point in running tests if queues are on, unless your running workers but even then,
         * you might get a nasty race condition
         */
        $this->assertTrue(config('queue.driver', 'sync') == 'sync');

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/advancedVarianceExplanationTypes'
        );
        $this->assertApiSuccess();
        $this->assertGreaterThan(1, count($this->getJSONContent()['data']));

        $advanced_variance_explanation_type_id = $this->getFirstDataObject()['id'];

        foreach ($this->FirstPropertyFirstAdvancedVarianceObj->advancedVarianceLineItems as $AdvancedVarianceLineItemsObj)
        {
            DB::update(
                DB::raw(
                    'UPDATE advanced_variance_line_items 
                                    SET 
                                        explanation = :EXPLANATION,
                                        explanation_update_date = NOW(),
                                        explainer_id = :EXPLAINER_ID,
                                        advanced_variance_explanation_type_id = :ADVANCED_VARIANCE_EXPLANATION_TYPE_ID,
                                        explanation_type_date = NOW(),
                                        resolver_user_id = :RESOLVER_USER_ID
                                WHERE id  = :ADVANCED_VARIANCE_LINE_ITEM_ID
                            '
                ),
                [
                    'EXPLANATION'                           => Seeder::getFakerObj()->words(5, true),
                    'EXPLAINER_ID'                          => $this->getLoggedInUserObj()->id,
                    'ADVANCED_VARIANCE_EXPLANATION_TYPE_ID' => $advanced_variance_explanation_type_id,
                    'RESOLVER_USER_ID'                      => $this->getLoggedInUserObj()->id,
                    'ADVANCED_VARIANCE_LINE_ITEM_ID'        => $AdvancedVarianceLineItemsObj->id,
                ]
            );
        }

        $this->turn_on_fake_notifications();
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/lock',
            [
                'locker_user_id' => $this->FirstGenericUserObj->id,
            ]
        );
        $this->assertApiSuccess();

        DB::update(
            DB::raw(
                '
                    UPDATE advanced_variance_line_items 
                        SET 
                            is_summary = 1
                        WHERE advanced_variance_id  = :ADVANCED_VARIANCE_ID
                '
            ),
            [
                'ADVANCED_VARIANCE_ID' => $this->FirstPropertyFirstAdvancedVarianceObj->id,
            ]
        );
        $this->FirstPropertyFirstAdvancedVarianceObj->refresh();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' . $PropertyGroupObj->id . '/advancedVariances/workflow?as_of_month=' . $this->FirstPropertyFirstAdvancedVarianceObj->as_of_month . '&as_of_year=' . $this->FirstPropertyFirstAdvancedVarianceObj->as_of_year
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyObj->id . '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/workflow'
        );
        $this->assertApiSuccess();

        $this->assertGreaterThan(0, count($this->getJSONContent()['data']['AdvancedVarianceLineItemWorkflows']));
        $this->assertNotEquals(
            $this->getJSONContent()['data']['locker_user_id'],
            $saved_responce_property_arr['locker_user_id']
        );
        $this->assertNotEquals(
            $this->getJSONContent()['data']['locked_date'],
            $saved_responce_property_arr['locked_date']
        );
        $this->assertNotNull(
            $this->getJSONContent()['data']['locker_user_id']
        );
        $this->assertNotNull(
            $this->getJSONContent()['data']['locked_date']
        );

        /**
         * no point in running this is not queue.driver = 'sync'. This an happen when we use unittests to generate queue activity.
         * queue.driver should always be 'sync'in unittest context
         */
        if (config('queue.driver', 'sync') == 'sync')
        {
            /** @var AdvancedVarianceLockedNotification $AdvancedVarianceLockedNotificationObj */
            $LocalAdvancedVarianceObj = $this->FirstPropertyFirstAdvancedVarianceObj;
            Notification::assertSentTo(
                $this->FirstPropertyFirstAdvancedVarianceObj->getExpectedRecipiants(),
                AdvancedVarianceLockedNotification::class,
                function (AdvancedVarianceLockedNotification $AdvancedVarianceLockedNotificationObj) use (
                    $LocalAdvancedVarianceObj
                )
                {
                    foreach ($LocalAdvancedVarianceObj->getExpectedRecipiants() as $ExpectedRecipiantUserObj)
                    {
                        $mail_arr = $AdvancedVarianceLockedNotificationObj->toMail($ExpectedRecipiantUserObj)->toArray();

                        $this->assertEquals('View Report', $mail_arr['actionText']);

                        $pattern = '/' . preg_quote($LocalAdvancedVarianceObj->lockerUser->getDisplayName(), '/') . '/';
                        $this->assertRegExp($pattern, $mail_arr['greeting']);

                        $pattern = '/' . preg_quote($LocalAdvancedVarianceObj->property->name) . '/';
                        $this->assertRegExp($pattern, $mail_arr['subject']);

                        $pattern = '/' .
                                   preg_quote(
                                       $AdvancedVarianceLockedNotificationObj->getBaseNotificationUrl() . '#/property/variance/reports/' .
                                       $LocalAdvancedVarianceObj->id . '?pureid=' . $LocalAdvancedVarianceObj->property_id,
                                       '/'
                                   ) .
                                   '/';
                        $this->assertRegExp($pattern, $mail_arr['actionUrl']);

                        $this->assertTrue(TestCase::is_syntactially_valid_url($mail_arr['actionUrl']));

                        /** @var App\Waypoint\Notifications\Notification $NotificatioLogObj */
                        if ( ! $NotificationLogObj = $this->NotificationLogRepositoryObj->findWhere(
                            ['notification_uuid' => $AdvancedVarianceLockedNotificationObj->id]
                        )->first())
                        {
                            $this->assertTrue(false);
                        }
                        $this->assertEquals(
                            $AdvancedVarianceLockedNotificationObj->id, $NotificationLogObj->notification_uuid
                        );
                        $this->assertTrue(
                            in_array(
                                $NotificationLogObj->channel,
                                $AdvancedVarianceLockedNotificationObj->via($ExpectedRecipiantUserObj)
                            )
                        );
                        break;
                    }
                    return $AdvancedVarianceLockedNotificationObj->getAdvancedVarianceObj()->id === $LocalAdvancedVarianceObj->id;
                }
            );
        }
        unset($LocalAdvancedVarianceObj);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id
        );
        $this->assertApiSuccess();

        $this->turn_on_fake_notifications();
        $this->json(
            'PUT',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id . '/unlock',
            []
        );
        $this->assertApiSuccess();

        unset($LocalAdvancedVarianceObj);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/' . $this->FirstPropertyFirstAdvancedVarianceObj->id
        );
        $this->assertApiSuccess();

        /** @var  PropertyGroupProperty $propertyGroupPropertyObj */
        $PropertyGroupPropertyObj = $this->makePropertyGroupProperty(
            [
                'property_id' => $this->FirstPropertyFirstAdvancedVarianceObj->property_id,
            ]
        );

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/propertyGroups/' .
            $PropertyGroupPropertyObj->property_group_id . '/advancedVariances'
        );
        $this->assertApiSuccess();
        $this->assertEquals(count($this->getDataObjectArr()), $this->original_num_advanced_variances_first_property);

        $AdvancedVarianceSummary_arr = $this->getFirstDataObject();

        $this->assertTrue(is_array($AdvancedVarianceSummary_arr['advancedVarianceLineItemsSlim']));
        foreach ($AdvancedVarianceSummary_arr['advancedVarianceLineItemsSlim'] as $AdvancedVarianceLineItems_arr)
        {
            $this->assertNotNull($AdvancedVarianceLineItems_arr['report_template_account_group_id']);
            $this->assertNull($AdvancedVarianceLineItems_arr['native_account_id']);
        }

    }

    /**
     * @test
     */
    public function it_can_flag4()
    {
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/advancedVariances/triggerJobs',
            []
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/uniqueAdvancedVarianceDates'
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyFirstAdvancedVarianceObj->property_id .
            '/uniqueAdvancedVarianceDates'
        );
        $this->assertApiSuccess();
    }

    /**
     * @param $expected_num_of_reviewers
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function check_related_counts($expected_num_of_reviewers)
    {
        $actual_num_of_reviewers = 0;
        foreach ($this->getFirstDataObject()['relatedUserTypes'] as $related_user_type)
        {
            if (
                $related_user_type['model_name'] == RelatedUserType::class &&
                $related_user_type['related_object_type'] == AdvancedVariance::class &&
                $related_user_type['related_object_subtype'] == AdvancedVariance::REVIEWER
            )
            {
                $actual_num_of_reviewers = count($related_user_type['relatedUsers']);
            }
            else
            {
                $this->assertTrue(false);
            }
        }
        $this->assertEquals($expected_num_of_reviewers, $actual_num_of_reviewers, 'expected num reviewers');
    }

    public function update_pointers_and_counters()
    {
        $this->num_line_items_flagged_via_policy                   = 0;
        $this->num_line_items_flagged_via_manually                 = 0;
        $this->unflagged_via_policy_advanced_variance_line_item_id = null;
        $this->flagged_via_policy_advanced_variance_line_item_id   = null;
        $this->flagged_manually_advanced_variance_line_item_id     = null;
        foreach ($this->FirstPropertyFirstAdvancedVarianceObj->advancedVarianceLineItems as $AdvancedVarianceLineItemObj)
        {
            if ($AdvancedVarianceLineItemObj->flagged_via_policy)
            {
                $this->num_line_items_flagged_via_policy++;
            }
            if ($AdvancedVarianceLineItemObj->flagged_manually)
            {
                $this->num_line_items_flagged_via_manually++;
            }

            if ( ! $this->unflagged_via_policy_advanced_variance_line_item_id && ! $AdvancedVarianceLineItemObj->flagged_via_policy)
            {
                $this->unflagged_via_policy_advanced_variance_line_item_id = $AdvancedVarianceLineItemObj->id;
            }
            if ($this->flagged_via_policy_advanced_variance_line_item_id && $AdvancedVarianceLineItemObj->flagged_via_policy)
            {
                $this->flagged_via_policy_advanced_variance_line_item_id = $AdvancedVarianceLineItemObj->id;
            }
            if ( ! $this->flagged_manually_advanced_variance_line_item_id && $AdvancedVarianceLineItemObj->flagged_manually)
            {
                $this->flagged_manually_advanced_variance_line_item_id = $AdvancedVarianceLineItemObj->id;
            }
        }
    }
}
