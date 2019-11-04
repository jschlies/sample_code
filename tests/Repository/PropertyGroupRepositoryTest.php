<?php

namespace App\Waypoint\Tests\Repository;

use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\PropertyGroupProperty;
use App;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\Generated\MakePropertyGroupTrait;
use App\Waypoint\Tests\Generated\MakePropertyGroupPropertyTrait;

/**
 * Class PropertyGroupRepositoryBaseTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class PropertyGroupRepositoryTest extends TestCase
{
    use MakePropertyGroupPropertyTrait, ApiTestTrait;
    use MakePropertyGroupTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function it_reads_property_groups()
    {
        /** @var  PropertyGroupProperty $PropertyGroupPropertyObj */
        $PropertyGroupPropertyObj = $this->makePropertyGroupProperty();
        /** @var PropertyGroupProperty $PropertyChildGroupPropertyObj */
        $PropertyChildGroupObj         = $this->makePropertyGroup(['parent_property_group_id' => $PropertyGroupPropertyObj->property_group_id]);
        $PropertyChildGroupPropertyObj = $this->makePropertyGroupProperty(['property_group_id' => $PropertyChildGroupObj->id]);

        /** @var  PropertyGroup $dbPropertyGroupObj */
        $dbPropertyGroupObj      = $this->PropertyGroupRepositoryObj->find($PropertyGroupPropertyObj->property_group_id);
        $dbPropertyChildGroupObj = $this->PropertyGroupRepositoryObj->find($PropertyChildGroupPropertyObj->property_group_id);

        $this->assertTrue($dbPropertyGroupObj->validate());

        foreach ($dbPropertyGroupObj->properties->getArrayOfGivenFieldValues('property_id_old') as $id)
        {
            $this->assertTrue(is_integer($id) || $id == null);
        }
        foreach ($dbPropertyGroupObj->properties->getArrayOfIDsFormatted() as $id)
        {
            $this->assertRegExp('/^Property_\d*$/', $id);
        }
        $this->assertEquals($dbPropertyGroupObj->propertyGroupChildren->count(), 1);
        /** @var PropertyGroup $Children */
        foreach ($dbPropertyGroupObj->propertyGroupChildren() as $Children)
        {
            $this->assertEquals(get_class($Children->propertyGroupParent()), PropertyGroup::class);
            $this->assertEquals($Children->parent_property_group_id, $dbPropertyGroupObj->id);
        }
        $this->assertEquals(get_class($dbPropertyChildGroupObj->propertyGroupParent), PropertyGroup::class);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        unset($this->PropertyGroupRepositoryObj);
        unset($this->PropertyGroupPropertyRepositoryObj);
        parent::tearDown();
    }
}
