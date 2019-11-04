<?php

namespace App\Waypoint\Tests;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\RelatedUser;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeRelatedUserTrait;

/**
 * Class RelatedUserApiBaseTest
 *
 * @codeCoverageIgnore
 */
class RelatedUserApiBaseTest extends TestCase
{
    use MakeRelatedUserTrait, ApiTestTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_related_users()
    {
        /** @var  array $related_users_arr */
        $related_users_arr = $this->fakeRelatedUserData();
        $this->json(
            'POST',
            '/api/v1/' . substr('relatedUsers', 0, 32),
            $related_users_arr
        );
        $this->assertApiSuccess();
        $relatedUsers_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('relatedUsers', 0, 32) . '/' . $relatedUsers_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('relatedUsers', 0, 32) . '/' . $relatedUsers_id
        );

        /**
         * since users are never deleted, just made inactive......
         */
        if (get_class($this) == UserApiBaseTest::class)
        {
            $this->assertApiSuccess();
        }
        else
        {
            $this->assertAPIFailure([400]);

            /**
             * now re-add it
             */
            $this->json(
                'POST',
                '/api/v1/' . substr('relatedUsers', 0, 32),
                $related_users_arr
            );
            $this->assertApiSuccess();
        }

        $relatedUsers_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/' . substr('relatedUsers', 0, 32) . '/' . $this->getFirstDataObject()['id']
        );
        $this->assertApiSuccess();

        /** @var  RelatedUser $relatedUserObj */
        $relatedUserObj = $this->makeRelatedUser();
        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_related_users_arr */
        $edited_related_users_arr = $this->fakeRelatedUserData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/' . substr('relatedUsers', 0, 32) . '/' . $relatedUserObj->id,
            $edited_related_users_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('relatedUsers', 0, 32) . '/' . $relatedUsers_id
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
    public function it_can_read_related_users_list()
    {
        /** @var  array $related_users_arr */
        $related_users_arr = $this->fakeRelatedUserData();
        $this->json(
            'POST',
            '/api/v1/' . substr('relatedUsers', 0, 32),
            $related_users_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('relatedUsers', 0, 32) . '?limit=' . config('waypoint.unittest_loop')
        );

        $this->assertAPIListResponse(RelatedUser::class);

    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_related_users()
    {
        $this->json(
            'GET',
            '/api/v1/' . substr('relatedUsers', 0, 32) . '/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_update_non_existing_related_users()
    {
        /** @var  array $editedRelatedUser_arr */
        $editedRelatedUser_arr = $this->fakeRelatedUserData([], Seeder::DEFAULT_FACTORY_NAME);
        /** @var  RelatedUser $relatedUserObj */
        $this->json(
            'PUT',
            '/api/v1/' . substr('relatedUsers', 0, 32) . '/' . '1000000' . mt_rand(), $editedRelatedUser_arr
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_delete_non_existing_related_users()
    {
        /** @var  RelatedUser $relatedUserObj */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('relatedUsers', 0, 32) . '/1000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
