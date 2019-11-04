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
use App\Waypoint\Models\CalculatedFieldVariable;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeCalculatedFieldVariableTrait;

/**
 * Class CalculatedFieldVariableApiBaseTest
 *
 * @codeCoverageIgnore
 */
class CalculatedFieldVariableApiBaseTest extends TestCase
{
    use MakeCalculatedFieldVariableTrait, ApiTestTrait;

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
    public function it_can_create_calculated_field_variables()
    {
        /** @var  array $calculated_field_variables_arr */
        $calculated_field_variables_arr = $this->fakeCalculatedFieldVariableData();
        $this->json(
            'POST',
            '/api/v1/' . substr('calculatedFieldVariables', 0, 32),
            $calculated_field_variables_arr
        );
        $this->assertApiSuccess();
        $calculatedFieldVariables_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('calculatedFieldVariables', 0, 32) . '/' . $calculatedFieldVariables_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('calculatedFieldVariables', 0, 32) . '/' . $calculatedFieldVariables_id
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
                '/api/v1/' . substr('calculatedFieldVariables', 0, 32),
                $calculated_field_variables_arr
            );
            $this->assertApiSuccess();
        }

        $calculatedFieldVariables_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/' . substr('calculatedFieldVariables', 0, 32) . '/' . $this->getFirstDataObject()['id']
        );
        $this->assertApiSuccess();

        /** @var  CalculatedFieldVariable $calculatedFieldVariableObj */
        $calculatedFieldVariableObj = $this->makeCalculatedFieldVariable();
        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_calculated_field_variables_arr */
        $edited_calculated_field_variables_arr = $this->fakeCalculatedFieldVariableData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/' . substr('calculatedFieldVariables', 0, 32) . '/' . $calculatedFieldVariableObj->id,
            $edited_calculated_field_variables_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('calculatedFieldVariables', 0, 32) . '/' . $calculatedFieldVariables_id
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
    public function it_can_read_calculated_field_variables_list()
    {
        /** @var  array $calculated_field_variables_arr */
        $calculated_field_variables_arr = $this->fakeCalculatedFieldVariableData();
        $this->json(
            'POST',
            '/api/v1/' . substr('calculatedFieldVariables', 0, 32),
            $calculated_field_variables_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('calculatedFieldVariables', 0, 32) . '?limit=' . config('waypoint.unittest_loop')
        );

        $this->assertAPIListResponse(CalculatedFieldVariable::class);

    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_calculated_field_variables()
    {
        $this->json(
            'GET',
            '/api/v1/' . substr('calculatedFieldVariables', 0, 32) . '/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_update_non_existing_calculated_field_variables()
    {
        /** @var  array $editedCalculatedFieldVariable_arr */
        $editedCalculatedFieldVariable_arr = $this->fakeCalculatedFieldVariableData([], Seeder::DEFAULT_FACTORY_NAME);
        /** @var  CalculatedFieldVariable $calculatedFieldVariableObj */
        $this->json(
            'PUT',
            '/api/v1/' . substr('calculatedFieldVariables', 0, 32) . '/' . '1000000' . mt_rand(), $editedCalculatedFieldVariable_arr
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_delete_non_existing_calculated_field_variables()
    {
        /** @var  CalculatedFieldVariable $calculatedFieldVariableObj */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('calculatedFieldVariables', 0, 32) . '/1000' . mt_rand()
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
