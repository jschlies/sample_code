<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeOpportunityTrait;
use App\Waypoint\Notifications\Facades\Notification;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\MakeAttachmentTrait;
use App\Waypoint\Tests\TestCase;
use function basename;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;

/**
 * Class OpportunityApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class OpportunityApiTest extends TestCase
{
    use MakeOpportunityTrait, ApiTestTrait, MakeUserTrait, MakeAttachmentTrait, MakePropertyTrait;

    protected $OpportunityObj;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        parent::setUp();

        $this->turn_on_fake_notifications();
        $this->ClientObj->addUserToAllAccessList($this->getLoggedInUserObj()->id);
        $this->ClientObj->updateConfig('FEATURE_OPPORTUNITIES', true);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_create_opportunities()
    {
        /** @var User $AssignedUserObj */
        $AssignedUserObj = $this->FirstGenericUserObj;
        /** @var User $CreatedByUserObj */
        $CreatedByUserObj = $this->SecondGenericUserObj;
        /** @var User $MentionedUserObj */
        $MentionedUserObj = $this->ThirdGenericUserObj;

        $this->ClientObj->addUserToAllAccessList($AssignedUserObj->id);
        $this->ClientObj->addUserToAllAccessList($CreatedByUserObj->id);
        $this->ClientObj->addUserToAllAccessList($CreatedByUserObj->id);
        $this->ClientObj->updateConfig('FEATURE_OPPORTUNITIES', true);
        $this->ClientObj->updateConfig('NOTIFICATIONS', true);
        $this->ClientObj->updateConfig('ENABLE_AUDITS', true);

        $AssignedUserObj->setAllOpportunitiesNotificationConfigs(true);
        $AssignedUserObj->updateConfig('USER_PROFILE_NOTIFICATIONS', true);

        $CreatedByUserObj->setAllOpportunitiesNotificationConfigs(true);
        $CreatedByUserObj->updateConfig('USER_PROFILE_NOTIFICATIONS', true);

        $MentionedUserObj->setAllOpportunitiesNotificationConfigs(true);
        $MentionedUserObj->updateConfig('USER_PROFILE_NOTIFICATIONS', true);

        /** @var  array $opportunity_arr */
        $opportunity_arr                       = $this->fakeOpportunityData(
            [
                'property_id'         => $this->FirstPropertyObj->id,
                'assigned_to_user_id' => $AssignedUserObj->id,
                'created_by_user_id'  => $CreatedByUserObj->id,
            ]
        );
        $opportunity_arr['opportunity_status'] = 'open';

        /**
         * now refresh $this->ClientObj to above changes to
         */
        $this->ClientObj = Client::find($this->ClientObj->id);
        if ( ! $this->getLoggedInUserObj()->canAccessProperty($opportunity_arr['property_id']))
        {
            $this->assertTrue(false);
        }

        $this->turn_on_fake_notifications();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/opportunities',
            $opportunity_arr
        );
        $this->assertApiSuccess();

        $ExpectedRecipiantUserObjArr = collect_waypoint([$AssignedUserObj,$CreatedByUserObj, $this->getLoggedInUserObj()]);
        /** @noinspection PhpUndefinedMethodInspection */
        Notification::assertSentTo(
            $ExpectedRecipiantUserObjArr,
            App\Waypoint\Notifications\OpportunityOpenedNotification::class,
            function (App\Waypoint\Notifications\OpportunityOpenedNotification $OpportunityOpenedNotificationObj
            ) use ($ExpectedRecipiantUserObjArr)
            {
                foreach ($ExpectedRecipiantUserObjArr as $ExpectedRecipiantUserObj)
                {
                    $mail_arr = $OpportunityOpenedNotificationObj->toMail(
                        $ExpectedRecipiantUserObj
                    )->toArray();

                    $pattern = '/View Task/';
                    $this->assertRegExp($pattern, $mail_arr['actionText']);
                    break;
                }
                return true;
            }
        );

        $this->assertApiSuccess();
        $client_id      = $this->getFirstDataObject()['client_id'];
        $opportunity_id = $this->getFirstDataObject()['id'];

        $opportunity_closed_arr                       = [];
        $opportunity_closed_arr['opportunity_status'] = 'closed';

        $this->turn_on_fake_notifications();
        $this->json(
            'PUT', '/api/v1/clients/' . $this->ClientObj->id . '/opportunities/' . $opportunity_id,
            $opportunity_closed_arr
        );
        $this->assertApiSuccess();

        $ExpectedRecipiantUserObjArr = collect_waypoint([$AssignedUserObj,$CreatedByUserObj, $this->getLoggedInUserObj()]);
        /** @noinspection PhpUndefinedMethodInspection */
        Notification::assertSentTo(
            $ExpectedRecipiantUserObjArr,
            App\Waypoint\Notifications\OpportunityUpdatedNotification::class,
            function (App\Waypoint\Notifications\OpportunityUpdatedNotification $OpportunityUpdatedNotificationObj
            ) use ($ExpectedRecipiantUserObjArr)
            {
                foreach ($ExpectedRecipiantUserObjArr as $ExpectedRecipiantUserObj)
                {
                    $mail_arr = $OpportunityUpdatedNotificationObj->toMail(
                        $ExpectedRecipiantUserObj
                    )->toArray();

                    $pattern = '/View Task/';
                    $this->assertRegExp($pattern, $mail_arr['actionText']);
                    break;
                }
                return true;
            }
        );

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $client_id . '/opportunities/' . $opportunity_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $client_id . '/opportunities/' . $opportunity_id
        );
        $this->assertAPIFailure([404, 500]);

        /**
         * now re-add it
         */
        $opportunity_arr['opportunity_status'] = 'open';
        $this->json(
            'POST',
            '/api/v1/clients/' . $client_id . '/opportunities',
            $opportunity_arr
        );

        $this->assertApiSuccess();
        $client_id      = $this->getFirstDataObject()['client_id'];
        $property_id    = $this->getFirstDataObject()['property_id'];
        $opportunity_id = $this->getFirstDataObject()['id'];

        /**
         * is it there??
         */
        $this->json('GET', '/api/v1/clients/' . $client_id . '/properties/' . $this->FirstPropertyObj->id . '/opportunities/' . $opportunity_id);

        /**
         * now add some comments
         */
        $this->turn_on_fake_notifications();
        $this->json(
            'POST',
            '/api/v1/clients/' . $client_id .
            '/properties/' . $property_id . '/opportunities/' . $opportunity_id . '/comments',
            [
                'commentable_id'   => $opportunity_id,
                'commentable_type' => 'App\\Waypoint\\Models\\Opportunity',
                'comment'          => Seeder::getFakerObj()->words(20, true) . '[~' . $AssignedUserObj->id . ']',
            ]
        );
        $this->assertApiSuccess();

        $ExpectedRecipiantUserObjArr = collect_waypoint([$AssignedUserObj,$CreatedByUserObj, $this->getLoggedInUserObj()]);
        /** @noinspection PhpUndefinedMethodInspection */
        Notification::assertSentTo(
            $ExpectedRecipiantUserObjArr,
            App\Waypoint\Notifications\OpportunityCommentedNotification::class,
            function (App\Waypoint\Notifications\OpportunityCommentedNotification $OpportunityCommentedNotificationObj
            ) use ($ExpectedRecipiantUserObjArr)
            {
                foreach ($ExpectedRecipiantUserObjArr as $ExpectedRecipiantUserObj)
                {
                    $mail_arr = $OpportunityCommentedNotificationObj->toMail(
                        $ExpectedRecipiantUserObj
                    )->toArray();

                    $pattern = '/View Task/';
                    $this->assertRegExp($pattern, $mail_arr['actionText']);
                    break;
                }
                return true;
            }
        );

        $this->json(
            'GET',
            '/api/v1/clients/' . $client_id . '/opportunities/' . $opportunity_id
        );
        $this->assertApiSuccess();

        /**
         * now delete the commnt we just created
         */
        $comments_data_arr =              reset(
                $this->getFirstDataObject()['comments'])
            ;
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $client_id . '/properties/' . $property_id .
            '/opportunities/' . $opportunity_id . '/comments/' . (
            $comments_data_arr['id']
            )
        );
        $this->assertApiSuccess();
        $this->json(
            'GET',
            '/api/v1/clients/' . $client_id . '/opportunities/' . $opportunity_id
        );
        $this->assertApiSuccess();
        $this->assertEquals(count($this->getFirstDataObject()['comments']), 0);

        /**
         * now add some comments
         */
        $this->json(
            'POST',
            '/api/v1/clients/' . $client_id . '/properties/' . $property_id . '/opportunities/' . $opportunity_id . '/comments',
            [
                'commentable_id'   => $this->getFirstDataObject()['id'],
                'commentable_type' => 'App\\Waypoint\\Models\\Opportunity',
                'comment'          => 'Ut nihil est fuga pariatur nulla.',
            ]
        );
        $this->assertApiSuccess();
        $this->json(
            'GET',
            '/api/v1/clients/' . $client_id . '/opportunities/' . $opportunity_id
        );
        $this->assertApiSuccess();
        $this->assertEquals(count(reset($this->JSONContent['data'])['comments']), 1);
        $this->assertEquals('open', $this->getFirstDataObject()['opportunity_status']);

        $this->json(
            'GET',
            '/api/v1/clients/' . $client_id .
            '/properties/' . $property_id . '/opportunities/' . $opportunity_id . '/comments'
        );
        $this->assertApiSuccess();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     */
    public function it_can_read_opportunities_for_client_list()
    {
        $OpportunityObj = $this->makeOpportunity(['client_id' => $this->ClientObj->id]);
        $this->json(
            'GET',
            '/api/v1/clients/' . $OpportunityObj->property->client_id . '/opportunities?limit=' . config(
                'waypoint.unittest_loop'
            )
        );
        $this->assertApiListResponse(Opportunity::class);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_update_opportunities()
    {
        $OpportunityObj = $this->makeOpportunity();
        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $editedOpportunity_arr */
        $editedOpportunity_arr                       = [];
        $editedOpportunity_arr['opportunity_status'] = 'open';
        $editedOpportunity_arr['name']               = Seeder::getFakerObj()->word;

        $OpportunityObj->property->client->updateConfig('NOTIFICATIONS', true);

        $OpportunityObj->assignedToUser->setAllOpportunitiesNotificationConfigs(true);
        $OpportunityObj->createdByUser->setAllOpportunitiesNotificationConfigs(true);

        $this->getLoggedInUserObj()->setAllOpportunitiesNotificationConfigs(true);
        $this->json(
            'PUT', '/api/v1/clients/' . $OpportunityObj->property->client_id . '/opportunities/' . $OpportunityObj->id,
            $editedOpportunity_arr
        );
        $this->assertApiSuccess();
        $this->assertEquals('open', $this->getFirstDataObject()['opportunity_status']);

        if (config('waypoint.enable_audits', false))
        {
            $this->json(
                'GET',
                '/api/v1/clients/' . $OpportunityObj->property->client_id . '/opportunities/' . $OpportunityObj->id . '/audits'
            );
            $this->assertApiSuccess();
            $this->assertTrue(is_array($this->getJSONContent()));
            $this->assertTrue(count($this->getDataObjectArr()) > 0);
            $this->assertAuditIsValid($this->getDataObjectArr(), 'updated');
        }

        /**
         * close it
         */
        $this->json(
            'PUT',
            '/api/v1/clients/' . $OpportunityObj->property->client_id . '/opportunities/' . $OpportunityObj->id,
            [
                'opportunity_status' => 'closed',
            ]
        );
        $this->assertApiSuccess();
        $this->assertTrue(is_array($this->getJSONContent()));

        $this->json(
            'GET',
            '/api/v1/clients/' . $OpportunityObj->property->client_id . '/opportunities/' . $OpportunityObj->id
        );
        $this->assertApiSuccess();
        $this->assertEquals('closed', $this->getFirstDataObject()['opportunity_status']);

        /**
         * open it back up it
         */
        $this->json(
            'PUT',
            '/api/v1/clients/' . $OpportunityObj->property->client_id . '/opportunities/' . $OpportunityObj->id,
            [
                'opportunity_status' => 'open',
            ]
        );
        $this->assertApiSuccess();
        $this->assertTrue(is_array($this->getJSONContent()));

        $this->json(
            'GET',
            '/api/v1/clients/' . $OpportunityObj->property->client_id . '/opportunities/' . $OpportunityObj->id
        );
        $this->assertApiSuccess();
        $this->assertEquals('open', $this->getFirstDataObject()['opportunity_status']);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_attach_opportunity_comments()
    {
        /** @var User $MentionedUserObj */
        $MentionedUserObj = $this->SixthGenericUserObj;
        /** @var User $AssignedUserObj */
        $AssignedUserObj = $this->FourthGenericUserObj;
        /** @var User $CreatedByUserObj */
        $CreatedByUserObj = $this->FifthGenericUserObj;

        $this->ClientObj->setConfigJSON(Seeder::getFakeUserConfigJson());

        $AssignedUserObj->setConfigJSON(Seeder::getFakeUserConfigJson());
        $CreatedByUserObj->setConfigJSON(Seeder::getFakeUserConfigJson());
        $MentionedUserObj->setConfigJSON(Seeder::getFakeUserConfigJson());

        /** @var  array $opportunity_arr */
        $opportunity_arr = $this->fakeOpportunityData(['assigned_to_user_id' => $AssignedUserObj->id, 'created_by_user_id' => $CreatedByUserObj->id]);

        $this->getLoggedInUserObj()->setAllOpportunitiesNotificationConfigs(true);
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/opportunities',
            $opportunity_arr
        );
        $this->assertApiSuccess();

        $client_id      = $this->getFirstDataObject()['client_id'];
        $property_id    = $this->getFirstDataObject()['property_id'];
        $opportunity_id = $this->getFirstDataObject()['id'];
        /**
         * is a comment there?? Better not be
         * 'clients/{client_id. '/opportunities/{opportunity_id. '/comments',
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $client_id . '/properties/' . $property_id . '/opportunities/' . $opportunity_id . '/comments'
        );
        $this->assertEquals(count($this->getDataObjectArr()), 0);

        /**
         * now add some comments
         */
        $this->turn_on_fake_notifications();
        $this->json(
            'POST',
            '/api/v1/clients/' . $client_id . '/properties/' . $property_id .
            '/opportunities/' . $opportunity_id . '/comments',
            [
                'commentable_id'   => $opportunity_id,
                'commentable_type' => 'App\\Waypoint\\Models\\Opportunity',
                'comment'          => Seeder::getFakerObj()->words(20, true) . '[~' . $MentionedUserObj->id . ']',
                'recipient_id_arr' => [
                    $this->getLoggedInUserObj()->id,
                    $AssignedUserObj->id,
                    $CreatedByUserObj->id,
                ],
                'mentions' => [$MentionedUserObj->id],
            ]
        );

        $ExpectedRecipiantUserObjArr = collect_waypoint([$AssignedUserObj,$CreatedByUserObj,$MentionedUserObj, $this->getLoggedInUserObj()]);
        /** @noinspection PhpUndefinedMethodInspection */
        Notification::assertSentTo(
            $ExpectedRecipiantUserObjArr,
            App\Waypoint\Notifications\OpportunityCommentedNotification::class,
            function (App\Waypoint\Notifications\OpportunityCommentedNotification $OpportunityCommentedNotificationObj
            ) use ($ExpectedRecipiantUserObjArr)
            {
                foreach ($ExpectedRecipiantUserObjArr as $ExpectedRecipiantUserObj)
                {
                    $mail_arr = $OpportunityCommentedNotificationObj->toMail(
                        $ExpectedRecipiantUserObj
                    )->toArray();

                    $pattern = '/View Task/';
                    $this->assertRegExp($pattern, $mail_arr['actionText']);
                    break;
                }
                return true;
            }
        );

        $this->turn_on_fake_notifications();
        $this->json(
            'POST',
            '/api/v1/clients/' . $client_id . '/properties/' . $property_id .
            '/opportunities/' . $opportunity_id . '/comments',
            [
                'commentable_id'   => $opportunity_id,
                'commentable_type' => 'App\\Waypoint\\Models\\Opportunity',
                'comment'          => Seeder::getFakerObj()->words(20, true) . '[~' . $MentionedUserObj->id . ']',
                'recipient_id_arr' => [
                    $AssignedUserObj->id,
                    $CreatedByUserObj->id,
                ],
                'mentions' => [$MentionedUserObj->id],
            ]
        );

        $ExpectedRecipiantUserObjArr = collect_waypoint([$AssignedUserObj,$CreatedByUserObj,$MentionedUserObj, $this->getLoggedInUserObj()]);
        /** @noinspection PhpUndefinedMethodInspection */
        Notification::assertSentTo(
            $ExpectedRecipiantUserObjArr,
            App\Waypoint\Notifications\OpportunityCommentedNotification::class,
            function (App\Waypoint\Notifications\OpportunityCommentedNotification $OpportunityCommentedNotificationObj
            ) use ($ExpectedRecipiantUserObjArr)
            {
                foreach ($ExpectedRecipiantUserObjArr as $ExpectedRecipiantUserObj)
                {
                    $mail_arr = $OpportunityCommentedNotificationObj->toMail(
                        $ExpectedRecipiantUserObj
                    )->toArray();

                    $pattern = '/View Task/';
                    $this->assertRegExp($pattern, $mail_arr['actionText']);
                    break;
                }
                return true;
            }
        );

        $this->json('GET', '/api/v1/clients/' . $client_id . '/opportunities/' . $opportunity_id);
        $this->assertEquals(count($this->getFirstDataObject()['comments']), 2);
        $this->assertEquals(count(reset($this->getFirstDataObject()['comments'])['commentMentions']), 1);

        $this->assertEquals(array_shift(array_shift($this->getFirstDataObject()['comments'])['commentMentions'])['user_id'], $MentionedUserObj->id);
        $this->assertEquals(array_shift(array_shift($this->getFirstDataObject()['comments'])['commentMentions'])['user']['id'], $MentionedUserObj->id);

        /**
         * try generic get route
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $client_id . '/properties/' . $property_id .
            '/opportunities/' . $opportunity_id . '/comments'
        );
        $this->assertEquals(count($this->getDataObjectArr()), 2);
        $this->assertApiSuccess();

        /**
         * now delete the comment we just created
         */
        $comment_to_delete = $this->getFirstDataObject();
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $client_id . '/properties/' . $property_id .
            '/opportunities/' . $opportunity_id . '/comments/' . $comment_to_delete['id']
        );

        $this->assertApiSuccess();
        $this->json('GET', '/api/v1/clients/' . $client_id . '/opportunities/' . $opportunity_id);
        $this->assertEquals(count($this->getFirstDataObject()['comments']), 1);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_can_attach_opportunity_attachments()
    {
        /** @var User $AssignedUserObj */
        $AssignedUserObj = $this->FourthGenericUserObj;
        /** @var User $CreatedByUserObj */
        $CreatedByUserObj = $this->FifthGenericUserObj;

        $this->ClientObj->setConfigJSON(Seeder::getFakeClientConfigJson());
        $AssignedUserObj->setConfigJSON(Seeder::getFakeUserConfigJson());
        $CreatedByUserObj->setConfigJSON(Seeder::getFakeUserConfigJson());

        /** @var  array $opportunity_arr */
        $opportunity_arr = $this->fakeOpportunityData(['assigned_to_user_id' => $AssignedUserObj->id, 'created_by_user_id' => $CreatedByUserObj->id]);

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/opportunities',
            $opportunity_arr
        );

        $property_id    = $this->getFirstDataObject()['property_id'];
        $opportunity_id = $this->getFirstDataObject()['id'];

        /**
         * via boutique route
         */
        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/opportunities/' . $opportunity_id . '/attachments');
        $this->assertApiSuccess();
        $this->assertEquals(count($this->getDataObjectArr()), 0);

        /**
         * via generic route
         */
        $this->json('GET',
                    '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $property_id . '/opportunities/' . $opportunity_id . '/attachments'
        );
        $this->assertApiSuccess();
        $this->assertEquals(count($this->getDataObjectArr()), 0);

        $fakeAttachmentData = $this->fakeAttachmentData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $property_id . '/opportunities/' . $opportunity_id . '/attachments',
            [
                'attachable_type' => Opportunity::class,
                'attachable_id'   => $opportunity_id,
                'file1'           => new SymfonyUploadedFile(
                    $fakeAttachmentData['path'],
                    $fakeAttachmentData['originalName'],
                    $fakeAttachmentData['mimeType'],
                    $fakeAttachmentData['size'],
                    null,
                    false
                ),
            ]
        );

        $ExpectedRecipiantUserObjArr = collect_waypoint([$AssignedUserObj,$CreatedByUserObj]);
        /** @noinspection PhpUndefinedMethodInspection */
        Notification::assertSentTo(
            $ExpectedRecipiantUserObjArr,
            App\Waypoint\Notifications\OpportunityAttachedNotification::class,
            function (App\Waypoint\Notifications\OpportunityAttachedNotification $OpportunityAttachedNotificationObj
            ) use ($ExpectedRecipiantUserObjArr)
            {
                foreach ($ExpectedRecipiantUserObjArr as $ExpectedRecipiantUserObj)
                {
                    $mail_arr = $OpportunityAttachedNotificationObj->toMail(
                        $ExpectedRecipiantUserObj
                    )->toArray();

                    $pattern = '/View Task/';
                    $this->assertRegExp($pattern, $mail_arr['actionText']);
                    break;
                }
                return true;
            }
        );

        $this->assertApiSuccess();
        $attachment_response = $this->getFirstDataObject();
        $this->assertRegExp(
            '/\/api\/v1\/ClientUser\/attachments\/\d*\/download/',
            $attachment_response['url']
        );
        $this->assertEquals($attachment_response['filename'], basename($fakeAttachmentData['path']));

        if ( ! $this->DownloadHistoryRepositoryObj->findWhere(
            [
                'original_file_name' => $fakeAttachmentData['originalName'],
            ]
        ))
        {
            $this->assertTrue(false, 'original_file_name not found');
        }

        /**
         * via boutique route
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/opportunities/' . $opportunity_id . '/attachments'
        );
        $this->assertApiSuccess();
        $this->assertEquals(count($this->getDataObjectArr()), 1);
        foreach ($this->getDataObjectArr() as $element)
        {
            $this->assertRegExp(
                '/\/api\/v1\/ClientUser\/attachments\/\d*\/download/',
                $element['url']
            );
            $this->assertTrue(is_array($element['createdByUser']));
        }
        /**
         * via generic route
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $property_id . '/opportunities/' . $opportunity_id . '/attachments'
        );
        $this->assertApiSuccess();
        $this->assertEquals(count($this->getDataObjectArr()), 1);
        foreach ($this->getDataObjectArr() as $element)
        {
            $this->assertRegExp(
                '/\/api\/v1\/ClientUser\/attachments\/\d*\/download/',
                $element['url']
            );
            $this->assertTrue(is_array($element['createdByUser']));
        }

        $first_attachment_arr = reset($this->JSONContent['data']);
        $attachment_id        = $first_attachment_arr['id'];

        $PrevUserObj = $this->getLoggedInUserObj();
        $this->logInUser($AssignedUserObj);
        /**
         * now delete it
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/attachments/' . $attachment_id
        );
        $this->assertApiFailure();

        /**
         * should still be in download_history
         */
        if ( ! $this->DownloadHistoryRepositoryObj->findWhere(
            [
                'original_file_name' => $fakeAttachmentData['originalName'],
            ]
        ))
        {
            $this->assertTrue(false, 'original_file_name not found');
        }

        $this->logInUser($PrevUserObj);

        /**
         * now delete it
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/attachments/' . $attachment_id
        );
        $this->assertApiSuccess();

        /**
         * via boutique route
         */
        $this->json('GET', '/api/v1/clients/' . $this->ClientObj->id . '/opportunities/' . $opportunity_id . '/attachments');
        $this->assertApiSuccess();
        $this->assertEquals(count($this->getDataObjectArr()), 0);

        /**
         * via generic route
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/opportunities/' . $opportunity_id . '/attachments');
        $this->assertApiSuccess();
        $this->assertEquals(count($this->getDataObjectArr()), 0);

        $fakeAttachmentData = $this->fakeAttachmentData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $property_id . '/opportunities/' . $opportunity_id . '/attachments',
            [
                'attachable_type' => Opportunity::class,
                'attachable_id'   => $opportunity_id,
                'file1'           => new SymfonyUploadedFile(
                    $fakeAttachmentData['path'],
                    $fakeAttachmentData['originalName'],
                    $fakeAttachmentData['mimeType'],
                    $fakeAttachmentData['size'],
                    null,
                    false
                ),
            ]
        );

        /**
         * should still be in download_history
         */
        if ( ! $this->DownloadHistoryRepositoryObj->findWhere(
            [
                'original_file_name' => $fakeAttachmentData['originalName'],
            ]
        ))
        {
            $this->assertTrue(false, 'original_file_name not found');
        }
        $this->assertApiSuccess();

        /**
         * via boutique route
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $property_id . '/opportunities/' . $opportunity_id . '/attachments'
        );
        $this->assertApiSuccess();
        $this->assertEquals(count($this->getDataObjectArr()), 1);

        $fakeAttachmentData = $this->fakeAttachmentData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $property_id . '/opportunities/77777777/attachments',
            [
                'attachable_type' => Opportunity::class,
                'attachable_id'   => 1000 * mt_rand(),
                'file1'           => new SymfonyUploadedFile(
                    $fakeAttachmentData['path'],
                    $fakeAttachmentData['originalName'],
                    $fakeAttachmentData['mimeType'],
                    $fakeAttachmentData['size'],
                    null,
                    false
                ),
            ]
        );
        $this->assertAPIFailure([400, 500]);
    }

    /**
     * @test
     */
    public function it_cannot_delete_non_existing_attachment()
    {
        /** @var User $AssignedUserObj */
        $AssignedUserObj = $this->FourthGenericUserObj;
        /** @var User $CreatedByUserObj */
        $CreatedByUserObj = $this->FifthGenericUserObj;

        $this->ClientObj->setConfigJSON(Seeder::getFakeClientConfigJson());
        $AssignedUserObj->setConfigJSON(Seeder::getFakeUserConfigJson());
        $CreatedByUserObj->setConfigJSON(Seeder::getFakeUserConfigJson());

        /** @var  array $opportunity_arr */
        $opportunity_arr = $this->fakeOpportunityData(['assigned_to_user_id' => $AssignedUserObj->id, 'created_by_user_id' => $CreatedByUserObj->id]);

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/opportunities',
            $opportunity_arr
        );
        $this->assertApiSuccess();

        $client_id      = $this->getFirstDataObject()['client_id'];
        $opportunity_id = $this->getFirstDataObject()['id'];

        $fakeAttachmentData = $this->fakeAttachmentData();
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $opportunity_arr['property_id'] . '/opportunities/' . $opportunity_id . '/attachments',
            [
                'attachable_type' => Opportunity::class,
                'attachable_id'   => $opportunity_id,
                'file1'           => new SymfonyUploadedFile(
                    $fakeAttachmentData['path'],
                    $fakeAttachmentData['originalName'],
                    $fakeAttachmentData['mimeType'],
                    $fakeAttachmentData['size'],
                    null,
                    false
                ),
            ]
        );
        $this->assertApiSuccess();

        $attachment_id = $this->getFirstDataObject()['id'];
        /**
         * test can delete
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $client_id . '/attachments/' . $attachment_id
        );
        $this->assertApiSuccess();

        /**
         * test cannot delete non-exists
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $client_id . '/attachments/3333'
        );
        $this->assertAPIFailure([400, 500]);
    }

    /**
     * @throws GeneralException
     * @throws \Exception
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     *
     * @test
     */
    public function it_cannot_use_opportunities()
    {
        $this->ClientObj->updateConfig('FEATURE_OPPORTUNITIES', false);

        /** @var User $AssignedUserObj */
        $AssignedUserObj = $this->FourthGenericUserObj;
        /** @var User $CreatedByUserObj */
        $CreatedByUserObj = $this->FifthGenericUserObj;

        $AssignedUserObj->setConfigJSON(Seeder::getFakeUserConfigJson());
        $CreatedByUserObj->setConfigJSON(Seeder::getFakeUserConfigJson());

        $this->ClientObj->addUserToAllAccessList($AssignedUserObj->id);
        $this->ClientObj->addUserToAllAccessList($CreatedByUserObj->id);

        /** @var  array $opportunity_arr */
        $opportunity_arr                       = $this->fakeOpportunityData(['assigned_to_user_id' => $AssignedUserObj->id, 'created_by_user_id' => $CreatedByUserObj->id]);
        $opportunity_arr['opportunity_status'] = 'open';

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/opportunities', $opportunity_arr
        );

        $this->assertApiFailure();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/opportunities?limit=' . config(
                'waypoint.unittest_loop'
            )
        );
        $this->assertApiFailure();

        /**
         * put things back where you found them
         */
        $this->ClientObj->updateConfig('FEATURE_OPPORTUNITIES', true);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
