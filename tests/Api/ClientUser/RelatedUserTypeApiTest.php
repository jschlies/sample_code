<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App\Waypoint\Models\Opportunity;
use App\Waypoint\Repositories\RelatedUserTypeRepository;
use App\Waypoint\Models\RelatedUserType;
use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeRelatedUserTypeTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class RelatedUserTypeApiBaseTest
 *
 * @codeCoverageIgnore
 */
class RelatedUserTypeApiTest extends TestCase
{
    use MakeRelatedUserTypeTrait, ApiTestTrait;

    /**
     * @var RelatedUserTypeRepository
     * this is needed in MakeRelatedUserTypeTrait
     */
    protected $relatedUserTypeRepositoryObj;

    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_related_user_types_properties()
    {
        /** @var  array $related_user_types_arr */
        $related_user_types_arr                        = $this->fakeRelatedUserTypeData();
        $related_user_types_arr['related_object_type'] = App\Waypoint\Models\Property::class;
        $this->json(
            'POST', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes',
            $related_user_types_arr
        );

        $this->assertApiSuccess();
        $related_user_types_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes'
        );
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertTrue($found_it);

        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes/properties'
        );
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            $this->assertEquals(App\Waypoint\Models\Property::class, $element['related_object_type']);
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertTrue($found_it);

        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/opportunities/relatedUserTypes'
        );
        /**
         * @todo fix me make this routes payload consistant
         */
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            $this->assertEquals(App\Waypoint\Models\Opportunity::class, $element['related_object_type']);
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertFalse($found_it);

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes/' . $related_user_types_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes'
        );
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertFalse($found_it);

        /**
         * now re-add it
         */
        $this->json(
            'POST', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes/properties',
            $related_user_types_arr
        );
        $this->assertApiSuccess();
        $related_user_types_id = $this->getFirstDataObject()['id'];

        $this->assertEquals(App\Waypoint\Models\Property::class, $this->getFirstDataObject()['related_object_type']);

        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes'
        );
        $this->assertApiSuccess();
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertTrue($found_it);
        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes/properties'
        );
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            $this->assertEquals(App\Waypoint\Models\Property::class, $element['related_object_type']);
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertTrue($found_it);

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes/' . $related_user_types_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes'
        );
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertFalse($found_it);
    }

    /**
     * @test
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_create_related_user_types_opportunities()
    {
        /** @var  array $related_user_types_arr */
        $related_user_types_arr                        = $this->fakeRelatedUserTypeData();
        $related_user_types_arr['related_object_type'] = App\Waypoint\Models\Opportunity::class;
        $this->json(
            'POST', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes',
            $related_user_types_arr
        );
        $this->assertApiSuccess();
        $related_user_types_id = $this->getFirstDataObject()['id'];

        /** @var  RelatedUserType $RelatedUserTypeObj */
        $RelatedUserTypeObj                      = RelatedUserType::find($related_user_types_id);
        $RelatedUserTypeObj->related_object_type = Opportunity::class;
        $RelatedUserTypeObj->save();

        $this->assertApiSuccess();

        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes'
        );
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertTrue($found_it);

        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/opportunities/relatedUserTypes'
        );
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertTrue($found_it);

        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/opportunities/relatedUserTypes'
        );
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertTrue($found_it);

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes/' . $related_user_types_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes'
        );
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertFalse($found_it);

        /**
         * now re-add it
         */
        $this->json(
            'POST', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes/opportunities',
            $related_user_types_arr
        );
        $this->assertApiSuccess();
        $related_user_types_id = $this->getFirstDataObject()['id'];

        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/' . substr('relatedUserTypes', 0, 32)
        );
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertTrue($found_it);
        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes/opportunities'
        );
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertTrue($found_it);

        /**
         * now delete the thing we just created
         */
        $this->json(
            'DELETE', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes/' . $related_user_types_id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes'
        );
        $this->assertAPIListResponse(RelatedUserType::class);

        $found_it = false;
        foreach ($this->getDataObjectArr() as $element)
        {
            if ($element['id'] == $related_user_types_id)
            {
                $found_it = true;
                break;
            }
        }
        $this->assertFalse($found_it);
    }

    /**
     * @test
     *
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_cannot_delete_non_existing_related_user_types()
    {
        $this->json(
            'DELETE', '/api/v1/clients/' . $this->getLoggedInUserObj()->client_id . '/relatedUserTypes/1000' . mt_rand()
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
