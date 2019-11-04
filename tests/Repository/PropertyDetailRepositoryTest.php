<?php

namespace App\Waypoint\Tests;

use App;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyDetail;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use App\Waypoint\Seeder;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\Generated\MakeRelatedUserTypeTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use Illuminate\Database\QueryException;

/**
 * Class PropertyDetailRepositoryTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class PropertyDetailRepositoryTest extends TestCase
{
    use MakePropertyTrait, ApiTestTrait;
    use MakeRelatedUserTypeTrait;
    use MakeUserTrait;

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function it_creates_property_detail()
    {
        /** @var  array $Property_arr */
        $Property_arr = $this->fakePropertyData();
        /** @var Property $PropertyDetailObj */
        $PropertyDetailObj = $this->PropertyDetailRepositoryObj->create($Property_arr);
        $this->assertInstanceOf(PropertyDetail::class, $PropertyDetailObj);

        /** @var User $UserObj */
        $UserObj = $this->fakeUser(['client_id' => $PropertyDetailObj->client_id])->save();
        $UserObj->attachRole(Role::where('name', Role::CLIENT_ADMINISTRATIVE_USER_ROLE)->first());

        /** @var  array $createdPropertyDetail_arr */
        $createdPropertyDetail_arr = $PropertyDetailObj->toArray();
        $this->assertArrayHasKey('id', $createdPropertyDetail_arr);
        $this->assertNotNull($createdPropertyDetail_arr['id'], 'Created PropertyDetail must have id specified');
        $this->assertNotNull(PropertyDetail::find($createdPropertyDetail_arr['id']), 'PropertyDetail with given id must be in DB');

        /** @var  PropertyDetail $dbPropertyDetailObj */
        $PropertyDetailObj = $this->PropertyDetailRepositoryObj->find($PropertyDetailObj->id);
        $this->assertTrue($PropertyDetailObj->validate());

        $fakeProperty_arr  = $this->fakePropertyData();
        $PropertyDetailObj = $this->PropertyDetailRepositoryObj->update($fakeProperty_arr, $PropertyDetailObj->id);

        /** @var  PropertyDetail $dbPropertyDetailObj */
        $PropertyDetailObj = $this->PropertyDetailRepositoryObj->find($PropertyDetailObj->id);

        /**
         * now lets test CustomAttribute's
         */
        $this->assertTrue(is_object($PropertyDetailObj->getCustomAttributeJSONObj()));
        $this->assertTrue(is_array($PropertyDetailObj->getCustomAttributeJSONObj(true)));

        $groucho = mt_rand();
        $zeppo   = mt_rand();
        $harpo   = mt_rand();
        $gummo   = mt_rand();

        $PropertyDetailObj->setCustomAttribute('groucho', $groucho);
        $PropertyDetailObj->setCustomAttribute('zeppo', $zeppo);
        $PropertyDetailObj->setCustomAttribute('harpo', $harpo);
        $PropertyDetailObj->setCustomAttribute('gummo', $gummo);

        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj()->groucho, $groucho);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj()->zeppo, $zeppo);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj()->harpo, $harpo);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj()->gummo, $gummo);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj(true)['groucho'], $groucho);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj(true)['zeppo'], $zeppo);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj(true)['harpo'], $harpo);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj(true)['gummo'], $gummo);
        $this->assertEquals($PropertyDetailObj->getCustomAttribute('groucho'), $groucho);
        $this->assertEquals($PropertyDetailObj->getCustomAttribute('zeppo'), $zeppo);
        $this->assertEquals($PropertyDetailObj->getCustomAttribute('harpo'), $harpo);
        $this->assertEquals($PropertyDetailObj->getCustomAttribute('gummo'), $gummo);

        $PropertyDetailObj = $this->PropertyDetailRepositoryObj->find($PropertyDetailObj->id);

        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj()->groucho, $groucho);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj()->zeppo, $zeppo);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj()->harpo, $harpo);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj()->gummo, $gummo);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj(true)['groucho'], $groucho);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj(true)['zeppo'], $zeppo);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj(true)['harpo'], $harpo);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj(true)['gummo'], $gummo);
        $this->assertEquals($PropertyDetailObj->getCustomAttribute('groucho'), $groucho);
        $this->assertEquals($PropertyDetailObj->getCustomAttribute('zeppo'), $zeppo);
        $this->assertEquals($PropertyDetailObj->getCustomAttribute('harpo'), $harpo);
        $this->assertEquals($PropertyDetailObj->getCustomAttribute('gummo'), $gummo);

        $CustomAttributeJSONObj          = $PropertyDetailObj->getCustomAttributeJSONObj();
        $CustomAttributeJSONObj->groucho = $zeppo;
        $PropertyDetailObj->setCustomAttributeJSONObj($CustomAttributeJSONObj);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj()->groucho, $zeppo);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj(true)['groucho'], $zeppo);

        $CustomAttributeJSONObj        = $PropertyDetailObj->getCustomAttributeJSONObj();
        $CustomAttributeJSONObj->harpo = null;
        $PropertyDetailObj->setCustomAttributeJSONObj($CustomAttributeJSONObj);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj()->harpo, null);
        $this->assertEquals($PropertyDetailObj->getCustomAttributeJSONObj(true)['harpo'], null);

        $PropertyDetailObj->setCustomAttributeJSONObj([]);
        $this->assertEquals($PropertyDetailObj->getCustomAttribute('groucho'), null);
        $this->assertEquals($PropertyDetailObj->getCustomAttribute('zeppo'), null);
        $this->assertEquals($PropertyDetailObj->getCustomAttribute('harpo'), null);
        $this->assertEquals($PropertyDetailObj->getCustomAttribute('gummo'), null);

        $related_object_type    = Seeder::getFakerObj()->jobTitle;
        $related_object_subtype = $related_object_type . ' ' . Seeder::getFakerObj()->word;
        $this->RelatedUserTypeRepositoryObj->create(
            [
                'name'                   => $related_object_type,
                'client_id'              => $PropertyDetailObj->client_id,
                'related_object_type'    => Property::class,
                'related_object_subtype' => $related_object_subtype,
            ]
        );

        $PropertyDetailObj->add_related_user($UserObj->id, $related_object_subtype);
        $RelatedUserTypeArr = $PropertyDetailObj->getRelatedUserTypes();
        $found_it           = false;
        foreach ($RelatedUserTypeArr as $RelatedUserTypeObj)
        {
            $RelatedUserTypeObj->relatedUsers;
            if (
            $RelatedUserTypeObj->related_object_type = $related_object_type &&
                                                       $RelatedUserTypeObj->related_object_subtype = $related_object_subtype
            )
            {
                $found_it = true;
                foreach ($RelatedUserTypeObj->relatedUsers as $RelatedUsersObj)
                {
                    if ($RelatedUsersObj->user_id = $UserObj->id)
                    {
                        $found_it_user = true;
                    }
                }
            }
        }
        $this->assertTrue($found_it);

        $resp = $this->PropertyDetailRepositoryObj->delete($PropertyDetailObj->id);
        $this->assertTrue($resp);
        $this->assertNull(PropertyDetail::find($PropertyDetailObj->id), 'PropertyDetail should not exist in DB');
    }

    /**
     * @test create
     *
     * @throws App\Waypoint\Exceptions\GeneralException
     * @throws App\Waypoint\Exceptions\SmartyStreetsException
     * @throws \PHPUnit\Framework\Exception
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function it_fails_to_create_PropertyDetails_constraint()
    {
        /** @var  array $user_arr */
        $this->expectException(QueryException::class);
        $property_arr      = $this->fakePropertyData();
        $PropertyDetailObj = $this->PropertyDetailRepositoryObj->create($property_arr);

        $property_arr2              = $this->fakePropertyData();
        $property_arr2['name']      = $PropertyDetailObj->name;
        $property_arr2['client_id'] = $PropertyDetailObj->client_id;

        $this->PropertyDetailRepositoryObj->create($property_arr2);
    }

    /**
     * @test create
     *
     * @throws App\Waypoint\Exceptions\SmartyStreetsException
     * @throws \PHPUnit\Framework\Exception
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function it_can_create_PropertyDetails_with_null_year_built()
    {
        /** @var  array $Property_arr */
        $Property_arr               = $this->fakePropertyData();
        $Property_arr['year_built'] = null;
        $PropertyDetailObj          = $this->PropertyDetailRepositoryObj->create($Property_arr);
        $this->assertInstanceOf(PropertyDetail::class, $PropertyDetailObj);

        /** @var  array $createdPropertyDetail_arr */
        $createdPropertyDetail_arr = $PropertyDetailObj->toArray();
        $this->assertArrayHasKey('id', $createdPropertyDetail_arr);
        $this->assertNotNull($createdPropertyDetail_arr['id'], 'Created PropertyDetail must have id specified');
        $this->assertNotNull(PropertyDetail::find($createdPropertyDetail_arr['id']), 'PropertyDetail with given id must be in DB');
        $this->assertNull($PropertyDetailObj->year_built);
    }

    /**
     * @test create
     *
     * @throws App\Waypoint\Exceptions\SmartyStreetsException
     * @throws \PHPUnit\Framework\Exception
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function it_can_create_PropertyDetails_with_null_year_renovated()
    {
        /** @var  array $Property_arr */
        $Property_arr                   = $this->fakePropertyData();
        $Property_arr['year_renovated'] = null;
        $PropertyDetailObj              = $this->PropertyDetailRepositoryObj->create($Property_arr);
        $this->assertInstanceOf(PropertyDetail::class, $PropertyDetailObj);

        /** @var  array $createdPropertyDetail_arr */
        $createdPropertyDetail_arr = $PropertyDetailObj->toArray();
        $this->assertArrayHasKey('id', $createdPropertyDetail_arr);
        $this->assertNotNull($createdPropertyDetail_arr['id'], 'Created PropertyDetail must have id specified');
        $this->assertNotNull(PropertyDetail::find($createdPropertyDetail_arr['id']), 'PropertyDetail with given id must be in DB');
        $this->assertNull($PropertyDetailObj->year_renovated);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        unset($this->PropertyDetailRepositoryObj);
        parent::tearDown();
    }
}