<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App;
use App\Waypoint\Models\CustomReport;
use App\Waypoint\Models\CustomReportDetail;
use App\Waypoint\Models\CustomReportType;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\Generated\MakeCustomReportTypeTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\MakeAttachmentTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;
use App\Waypoint\Tests\ApiTestTrait;

class CustomReportDetailApiTest extends TestCase
{
    use MakePropertyTrait, MakeAttachmentTrait, MakeCustomReportTypeTrait, ApiTestTrait;

    /** @var Property $PropertyObj */
    protected $PropertyObj;

    /** @var null|CustomReportType $CustomReportTypeObj */
    protected $CustomReportTypeObj = null;

    /** @var null|CustomReport $CustomReportObj */
    protected $CustomReportObj = null;

    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function can_create_custom_reports()
    {
        $this->CustomReportTypeObj = $this->makeCustomReportType();

        $fakeAttachmentData = $this->fakeAttachmentData();
        $period_type        = $this->get_random_period_from_period_type($this->CustomReportTypeObj->period_type);

        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyObj->id . '/customReportType/' . $this->CustomReportTypeObj->id . '/year/2017/period/' . $period_type,
            [
                'attachable_type' => CustomReport::class,
                'file1'           => new SymfonyUploadedFile(
                    $fakeAttachmentData['path'],
                    $fakeAttachmentData['originalName'],
                    $fakeAttachmentData['mimeType'],
                    $fakeAttachmentData['size'],
                    null,
                    false
                ),
            ]
        );

        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyObj->id . '/customReportsDetail'
        );
        $this->assertApiListResponse(CustomReportDetail::class);
        $this->assertEquals(1, count($this->getJSONContent()['data']));

        /**
         * try to update report
         */
        $this->json(
            'POST',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyObj->id . '/customReportType/' . $this->CustomReportTypeObj->id . '/year/2017/period/' . $period_type,
            [
                'attachable_type' => CustomReport::class,
                'file1'           => new SymfonyUploadedFile(
                    $fakeAttachmentData['path'],
                    $fakeAttachmentData['originalName'],
                    $fakeAttachmentData['mimeType'],
                    $fakeAttachmentData['size'],
                    null,
                    false
                ),
            ]
        );

        $this->assertApiSuccess();
        $this->assertNotNull($this->getDataObjectArr()['id']);
        $this->CustomReportObj = $this->CustomReportRepositoryObj->find($this->getDataObjectArr()['id']);
        $this->assertNotEmpty($this->CustomReportObj->id);
        $this->assertEquals($this->getFirstDataObject()['id'], $this->CustomReportObj->id);

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyObj->id . '/customReportsDetail'
        );
        $this->assertApiListResponse(CustomReportDetail::class);
        $this->assertEquals($this->getFirstDataObject()['id'], $this->CustomReportObj->id);
        $this->assertApiSuccess();
        $this->assertEquals(1, count($this->getJSONContent()['data']));

        $this->json(
            'DELETE',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyObj->id . '/customReportsDetail/' . $this->CustomReportObj->id
        );
        $this->assertApiSuccess();

        $this->json(
            'GET',
            '/api/v1/clients/' . $this->ClientObj->id . '/properties/' . $this->FirstPropertyObj->id . '/customReportsDetail'
        );
        $this->assertApiSuccess();
        $this->assertEquals(0, count($this->getJSONContent()['data']));
    }

    /**
     * @param $period_type
     * @return mixed
     */
    public function get_random_period_from_period_type($period_type)
    {
        return array_random(CustomReportType::PERIOD_TYPES_TO_PERIODS_LOOKUP[$period_type]);
    }

    protected function tearDown()
    {
        parent::tearDown();
    }
}