<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeAccessListUserTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;

/**
 * Class PasswordRuleAPITest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class PasswordRuleAPITest extends TestCase
{
    use MakeAccessListTrait, ApiTestTrait;
    use MakeAccessListUserTrait;
    use MakePropertyTrait;

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
     *
     * @throws GeneralException
     * @throws \PHPUnit\Framework\AssertionFailedError
     * @throws \PHPUnit\Framework\Exception
     */
    public function it_can_read_password_rules()
    {
        $this->json(
            'GET',
            '/api/v1/passwordRules'
        );
        $this->assertApiSuccess();
    }
}
