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
use App\Waypoint\Models\CalculatedFieldEquation;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakeCalculatedFieldEquationTrait;

/**
 * Class CalculatedFieldEquationApiBaseTest
 *
 * @codeCoverageIgnore
 */
class CalculatedFieldEquationApiBaseTest extends TestCase
{
    use MakeCalculatedFieldEquationTrait, ApiTestTrait;

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
    public function it_can_create_calculated_field_equations()
    {
        /** @var  array $calculated_field_equations_arr */
        $calculated_field_equations_arr = $this->fakeCalculatedFieldEquationData();
        $this->json(
            'POST',
            '/api/v1/' . substr('calculatedFieldEquations', 0, 32),
            $calculated_field_equations_arr
        );
        $this->assertApiSuccess();
        $calculatedFieldEquations_id = $this->getFirstDataObject()['id'];

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('calculatedFieldEquations', 0, 32) . '/' . $calculatedFieldEquations_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('calculatedFieldEquations', 0, 32) . '/' . $calculatedFieldEquations_id
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
                '/api/v1/' . substr('calculatedFieldEquations', 0, 32),
                $calculated_field_equations_arr
            );
            $this->assertApiSuccess();
        }

        $calculatedFieldEquations_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET',
            '/api/v1/' . substr('calculatedFieldEquations', 0, 32) . '/' . $this->getFirstDataObject()['id']
        );
        $this->assertApiSuccess();

        /** @var  CalculatedFieldEquation $calculatedFieldEquationObj */
        $calculatedFieldEquationObj = $this->makeCalculatedFieldEquation();
        /*
         * @todo use Seeder::DEFAULT_FACTORY_NAME for now to keeps 'keys' out of the equation.
         */
        /** @var  array $edited_calculated_field_equations_arr */
        $edited_calculated_field_equations_arr = $this->fakeCalculatedFieldEquationData([], Seeder::DEFAULT_FACTORY_NAME);
        $this->json(
            'PUT',
            '/api/v1/' . substr('calculatedFieldEquations', 0, 32) . '/' . $calculatedFieldEquationObj->id,
            $edited_calculated_field_equations_arr
        );
        $this->assertApiSuccess();

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('calculatedFieldEquations', 0, 32) . '/' . $calculatedFieldEquations_id
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
    public function it_can_read_calculated_field_equations_list()
    {
        /** @var  array $calculated_field_equations_arr */
        $calculated_field_equations_arr = $this->fakeCalculatedFieldEquationData();
        $this->json(
            'POST',
            '/api/v1/' . substr('calculatedFieldEquations', 0, 32),
            $calculated_field_equations_arr
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/' . substr('calculatedFieldEquations', 0, 32) . '?limit=' . config('waypoint.unittest_loop')
        );

        $this->assertAPIListResponse(CalculatedFieldEquation::class);

    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_read_non_existing_calculated_field_equations()
    {
        $this->json(
            'GET',
            '/api/v1/' . substr('calculatedFieldEquations', 0, 32) . '/' . '1000000' . mt_rand()
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_update_non_existing_calculated_field_equations()
    {
        /** @var  array $editedCalculatedFieldEquation_arr */
        $editedCalculatedFieldEquation_arr = $this->fakeCalculatedFieldEquationData([], Seeder::DEFAULT_FACTORY_NAME);
        /** @var  CalculatedFieldEquation $calculatedFieldEquationObj */
        $this->json(
            'PUT',
            '/api/v1/' . substr('calculatedFieldEquations', 0, 32) . '/' . '1000000' . mt_rand(), $editedCalculatedFieldEquation_arr
        );
        $this->assertAPIFailure([400]);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_delete_non_existing_calculated_field_equations()
    {
        /** @var  CalculatedFieldEquation $calculatedFieldEquationObj */
        $this->json(
            'DELETE',
            '/api/v1/' . substr('calculatedFieldEquations', 0, 32) . '/1000' . mt_rand()
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
