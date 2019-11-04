<?php

namespace App\Waypoint\Tests\Api\ClientUser;

use App\Waypoint\Models\Role;
use App;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\ApiTestTrait;

class DownloadHistoryApiTest extends TestCase
{
    use ApiTestTrait;

    protected $DownloadHistoryRepository;

    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_GENERIC_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     */
    public function can_create_download_history()
    {
        $download_history_body_arr = [
            'download_type'      => 'report',
            'original_file_name' => 'original.txt',
            'user_id'            => $this->getLoggedInUserObj()->id,
        ];

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/downloadHistories',
            $download_history_body_arr
        );

        $this->assertApiSuccess();
    }
}
