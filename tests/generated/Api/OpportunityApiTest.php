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
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeOpportunityTrait;

/**
 * Class OpportunityApiBaseTest
 *
 * @codeCoverageIgnore
 */
class OpportunityApiBaseTest extends TestCase
{
    use MakeOpportunityTrait, ApiTestTrait;

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
    public function it_can_create_opportunities()
    {
        /** @var  array $opportunities_arr */
        $opportunities_arr = $this->fakeOpportunityData();
        $this->json(
            'POST',
            '/api/v1/' . substr('opportunities', 0, 32),
            $opportunities_arr
        );
        $this->assertApiSuccess();
        $opportunities_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('opportunities', 0, 32) . '/' . $opportunities_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('opportunities', 0, 32) . '/' . $opportunities_id
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
                '/api/v1/' . substr('opportunities', 0, 32),
                $opportunities_arr
            );
            $this->assertApiSuccess();
        }

        $opportunities_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/' . substr('opportunities', 0, 32) . '/' . $this->getFirstDataObject()['id']
        );
        $this->assertApiSuccess();

        /** @var  Opportunity $opportunityObj */
        $opportunityObj = $this->makeOpportunity();
        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_opportunities_arr */
        $edited_opportunities_arr = $this->fakeOpportunityData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/' . substr('opportunities', 0, 32) . '/' . $opportunityObj->id,
            $edited_opportunities_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('opportunities', 0, 32) . '/' . $opportunities_id
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
    public function it_can_read_opportunities_list()
    {
        /** @var  array $opportunities_arr */
        $opportunities_arr = $this->fakeOpportunityData();
        $this->json(
            'POST',
            '/api/v1/' . substr('opportunities', 0, 32),
            $opportunities_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('opportunities', 0, 32) . '?limit=' . config('waypoint.unittest_loop')
        );

        $this->assertAPIListResponse(Opportunity::class);

    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_opportunities()
    {
        $this->json(
            'GET',
            '/api/v1/' . substr('opportunities', 0, 32) . '/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_update_non_existing_opportunities()
    {
        /** @var  array $editedOpportunity_arr */
        $editedOpportunity_arr = $this->fakeOpportunityData([], Seeder::DEFAULT_FACTORY_NAME);
        /** @var  Opportunity $opportunityObj */
        $this->json(
            'PUT',
            '/api/v1/' . substr('opportunities', 0, 32) . '/' . '1000000' . mt_rand(), $editedOpportunity_arr
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_delete_non_existing_opportunities()
    {
        /** @var  Opportunity $opportunityObj */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('opportunities', 0, 32) . '/1000' . mt_rand()
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
