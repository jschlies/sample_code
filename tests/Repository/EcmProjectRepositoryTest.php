<?php

namespace App\Waypoint\Tests;

use App\Waypoint\Exceptions\ValidationException;
use App\Waypoint\Models\EcmProject;
use App\Waypoint\Repositories\EcmProjectRepository;
use App;
use App\Waypoint\Tests\Generated\MakeEcmProjectTrait;

/**
 * Class EcmProjectRepositoryTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class EcmProjectRepositoryTest extends TestCase
{
    use MakeEcmProjectTrait, ApiTestTrait;

    /**
     * @var EcmProjectRepository
     */
    protected $EcmProjectRepositoryObj;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_reads_ecm_project_detail()
    {
        $PropertyObj = $this->ClientObj->properties->first();
        /** @var  EcmProject */
        $EcmProjectObj = $this->makeEcmProject(['property_id' => $PropertyObj->id]);

        /** @var  EcmProject $dbEcmProjectObj */
        $dbEcmProjectObj = $this->EcmProjectRepositoryObj->find($EcmProjectObj->id);

        $this->assertTrue($dbEcmProjectObj->validate());
        $this->assertEquals($dbEcmProjectObj->toArray()['name'], $EcmProjectObj->name);
        $this->assertEquals($dbEcmProjectObj->toArray()['project_status'], $EcmProjectObj->project_status);

    }

    /**
     * @test create
     *
     * @throws \PHPUnit\Framework\Exception
     * @expectedException \App\Waypoint\Exceptions\ValidationException
     */
    public function it_fails_to_create_ecm_project_userDetails_constraint()
    {
        /** @var  array $ecm_project_arr */
        $this->expectException(ValidationException::class);
        $ecm_project_arr = $this->fakeEcmProjectData();
        $UserObj         = $this->EcmProjectRepositoryObj->create($ecm_project_arr);

        $ecm_project_arr2                = $this->fakeEcmProjectData();
        $ecm_project_arr2['name']        = $UserObj->name;
        $ecm_project_arr2['property_id'] = $UserObj->property_id;

        $this->EcmProjectRepositoryObj->create($ecm_project_arr2);
    }

    /**
     * @test create
     *
     * @throws \PHPUnit\Framework\Exception
     * @expectedException \App\Waypoint\Exceptions\ValidationException
     */
    public function it_fails_to_update_ecm_project_userDetails_constraint()
    {
        /** @var  array $user_arr */
        $this->expectException(ValidationException::class);
        $user_arr = $this->fakeEcmProjectData();
        $UserObj  = $this->EcmProjectRepositoryObj->create($user_arr);
        $user_arr = $this->fakeEcmProjectData();
        $UserObj2 = $this->EcmProjectRepositoryObj->create($user_arr);

        $user_arr2                = $this->fakeEcmProjectData();
        $user_arr2['name']        = $UserObj->name;
        $user_arr2['property_id'] = $UserObj->property_id;

        $this->EcmProjectRepositoryObj->update($user_arr2, $UserObj2->id);
    }

    /**
     * @test create
     *
     * @throws \PHPUnit\Framework\Exception
     * @expectedException \App\Waypoint\Exceptions\ValidationException
     */
    public function it_fails_to_create_ecm_project_name()
    {
        /** @var  array $user_arr */
        $this->expectException(ValidationException::class);
        $user_arr         = $this->fakeEcmProjectData();
        $user_arr['name'] = 'somthinglongerthat255somthinglongerthat255somthinglongerthat255somthinglongerthat255somthinglongerthat255somthinglongerthat255somthinglongerthat255somthinglongerthat255somthinglongerthat255somthinglongerthat255somthinglongerthat255somthinglongerthat255somthinglongerthat255somthinglongerthat255';

        $this->EcmProjectRepositoryObj->create($user_arr);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        unset($this->EcmProjectRepositoryObj);
        parent::tearDown();
    }
}