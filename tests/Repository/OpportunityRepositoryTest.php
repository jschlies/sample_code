<?php

namespace App\Waypoint\Tests;

use App\Waypoint\Models\Opportunity;
use App;
use App\Waypoint\Tests\Generated\MakeOpportunityTrait;
use Faker\Provider\Uuid;
use function mt_rand;

/**
 * Class OpportunityRepositoryTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class OpportunityRepositoryTest extends TestCase
{
    use MakeOpportunityTrait, ApiTestTrait;
    use App\Waypoint\CurlServiceTrait;

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
    public function it_creates_opportunity()
    {
        /** @var  array $Opportunity_arr */
        $Opportunity_arr = $this->fakeOpportunityData();
        $OpportunityObj  = $this->OpportunityRepositoryObj->create($Opportunity_arr);
        $this->assertInstanceOf(Opportunity::class, $OpportunityObj);

        /** @var  array $createdOpportunity_arr */
        $createdOpportunity_arr = $OpportunityObj->toArray();
        $this->assertArrayHasKey('id', $createdOpportunity_arr);
        $this->assertNotNull($createdOpportunity_arr['id'], 'Created Opportunity must have id specified');
        $this->assertNotNull(Opportunity::find($createdOpportunity_arr['id']), 'Opportunity with given id must be in DB');

        /** @var  Opportunity $dbOpportunityObj */
        $OpportunityObj = $this->OpportunityRepositoryObj->find($OpportunityObj->id);
        $this->assertTrue($OpportunityObj->validate());

        $fakeOpportunity_arr = $this->fakeOpportunityData();
        $OpportunityObj      = $this->OpportunityRepositoryObj->update($fakeOpportunity_arr, $OpportunityObj->id);

        /** @var  Opportunity $dbOpportunityObj */
        $OpportunityObj = $this->OpportunityRepositoryObj->find($OpportunityObj->id);

        $first_attachment_key = Uuid::uuid();

        /** @var  $FirstAttachmentObj */
        $fq_temp_file_name = '/storage/app/temp_images/README' . mt_rand();
        copy(base_path('/storage/app/temp_images/README'), base_path($fq_temp_file_name));
        $FirstAttachmentObj = $OpportunityObj->attach(
            base_path($fq_temp_file_name),
            [
                'disk'        => config('waypoint.attachment_data_store_disc', 's3_attachments'),
                'title'       => 'foo',
                'description' => 'foofoo',
                'key'         => $first_attachment_key,
            ]
        );
        $this->assertEquals($first_attachment_key, $FirstAttachmentObj->key);

        $second_attachment_key = Uuid::uuid();
        $fq_temp_file_name     = '/storage/app/temp_images/README' . mt_rand();
        copy(base_path('/storage/app/temp_images/README'), base_path($fq_temp_file_name));
        $SecondAttachmentObj = $OpportunityObj->attach(
            base_path($fq_temp_file_name),
            [
                'disk'        => config('waypoint.attachment_data_store_disc', 's3_attachments'),
                'title'       => 'foo' . mt_rand(),
                'description' => 'foofoo' . mt_rand(),
                'key'         => $second_attachment_key,
            ]
        );
        $this->assertEquals($second_attachment_key, $SecondAttachmentObj->key);

        $this->assertEquals(2, $OpportunityObj->getAttachments()->count());
        /**
         * should over-write $SecondAttachmentObj
         */
        $fq_temp_file_name = '/storage/app/temp_images/README' . mt_rand();
        copy(base_path('/storage/app/temp_images/README'), base_path($fq_temp_file_name));
        $ThirdAttachmentObj = $OpportunityObj->attach(
            base_path($fq_temp_file_name),
            [
                'disk'        => config('waypoint.attachment_data_store_disc', 's3_attachments'),
                'title'       => 'foo' . mt_rand(),
                'description' => 'foofoo' . mt_rand(),
                'key'         => $second_attachment_key,
            ]
        );
        $this->assertEquals($second_attachment_key, $ThirdAttachmentObj->key);
        $this->assertEquals(2, $OpportunityObj->getAttachments()->count());

        $this->assertTrue(in_array($FirstAttachmentObj->key, [$first_attachment_key, $second_attachment_key]));

        $FileAttachmentObj = $OpportunityObj->attachment($ThirdAttachmentObj->key);
        $this->assertNotNull($FileAttachmentObj);

        if (method_exists(get_class($OpportunityObj), 'attachments'))
        {
            /** @var \Bnb\Laravel\Attachments\Attachment $attachment */
            /** @noinspection PhpUndefinedMethodInspection */
            foreach ($OpportunityObj->getAttachments() as $attachment)
            {
                $attachment->delete();
            }
        }

        $resp = $this->OpportunityRepositoryObj->delete($OpportunityObj->id);

        $this->assertTrue($resp);
        $this->assertNull(Opportunity::find($OpportunityObj->id), 'Opportunity should not exist in DB');
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