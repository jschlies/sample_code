<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Role;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeLeaseTrait;
use App\Waypoint\Tests\Generated\MakeSuiteTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class ClientDetailApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class CommentApiTest extends TestCase
{
    use MakeLeaseTrait, MakeSuiteTrait, ApiTestTrait;
    use MakeAccessListTrait;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_can_create_comments_on_leases()
    {
        $LeaseObj      = $this->makeLease(['property_id' => $this->getUnitTestClient()->properties->first()->id]);
        $AccessListObj = $this->ThirdAccessListObj;

        $this->AccessListUserRepositoryObj->create(
            [
                'user_id'        => $this->getLoggedInUserObj()->id,
                'access_list_id' => $AccessListObj->id,
            ]
        );
        $this->AccessListPropertyRepositoryObj->create(
            [
                'property_id'    => $LeaseObj->property_id,
                'access_list_id' => $AccessListObj->id,
            ]
        );
        $AccessListObj->refresh();

        /**
         * should be empty
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/commentable_type/Lease/commentable_id/' . $LeaseObj->id
        );
        $this->assertApiSuccess();
        $this->assertEquals(0, count($this->getJSONContent()['data']));

        /**
         * make a comment
         */
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/commentable_type/Lease/commentable_id/' . $LeaseObj->id,
            [
                'comment'  => Seeder::getFakerObj()->sentence('100', true),
                'rate'     => null,
                'approved' => true,
            ]
        );
        $this->assertApiSuccess();

        $comment_id = $this->getJSONContent()['data']['id'];

        /**
         * get comments of $LeaseObj
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/commentable_type/Lease/commentable_id/' . $LeaseObj->id
        );
        $this->assertApiSuccess();
        $this->assertEquals(1, count($this->getJSONContent()['data']));

        /**
         * get comment in question
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/' . $comment_id
        );
        $this->assertApiSuccess();

        /**
         * delete comment in question
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/' . $comment_id
        );
        $this->assertApiSuccess();

        /**
         * fail to get comment in question
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/' . $comment_id
        );
        $this->assertApiFailure();
        /**
         * get comments of $LeaseObj
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/commentable_type/Lease/commentable_id/' . $LeaseObj->id
        );
        $this->assertApiSuccess();
        $this->assertEquals(0, count($this->getJSONContent()['data']));
    }

    /**
     * @test
     */
    public function it_can_create_comments_on_suites()
    {
        $SuiteObj      = $this->makeSuite();
        $AccessListObj = $this->FourthAccessListObj;
        $this->AccessListUserRepositoryObj->create(
            [
                'user_id'        => $this->getLoggedInUserObj()->id,
                'access_list_id' => $AccessListObj->id,
            ]
        );
        $this->AccessListPropertyRepositoryObj->create(
            [
                'property_id'    => $SuiteObj->property_id,
                'access_list_id' => $AccessListObj->id,
            ]
        );

        /**
         * should be empty
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/commentable_type/Suite/commentable_id/' . $SuiteObj->id
        );
        $this->assertApiSuccess();
        $this->assertEquals(0, count($this->getJSONContent()['data']));

        /**
         * make a comment
         */
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/commentable_type/Suite/commentable_id/' . $SuiteObj->id,
            [
                'comment'          => Seeder::getFakerObj()->sentence('100', true),
                'rate'             => null,
                'approved'         => true,
                'commented_id'     => $this->getLoggedInUserObj()->id,
                'commentable_id'   => $SuiteObj->id,
                'commentable_type' => 'Suite',
            ]
        );
        $this->assertApiSuccess();

        $comment_id = $this->getJSONContent()['data']['id'];

        /**
         * get comments of $SuiteObj
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/commentable_type/Suite/commentable_id/' . $SuiteObj->id
        );
        $this->assertApiSuccess();
        $this->assertEquals(1, count($this->getJSONContent()['data']));

        /**
         * get comment in question
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/' . $comment_id
        );
        $this->assertApiSuccess();

        /**
         * delete comment in question
         */
        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/' . $comment_id
        );
        $this->assertApiSuccess();

        /**
         * fail to get comment in question
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/' . $comment_id
        );
        $this->assertApiFailure();
        /**
         * get comments of $SuiteObj
         */
        $this->json(
            'GET',
            '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/commentsDetail/commentable_type/Suite/commentable_id/' . $SuiteObj->id);
        $this->assertApiSuccess();
        $this->assertEquals(0, count($this->getJSONContent()['data']));

    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
