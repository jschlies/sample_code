<?php

namespace App\Waypoint\Tests\Api\ClientAdmin;

use App;
use App\Waypoint\Models\Role;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\TestCase;
use App\Waypoint\Tests\Generated\MakeReportTemplateTrait;
use App\Waypoint\Exceptions\GeneralException;

/**
 * Class ReportApiTest
 * @package App\Waypoint\Tests;
 *
 * @codeCoverageIgnore
 */
class ReportApiTest extends TestCase
{
    use MakeReportTemplateTrait, ApiTestTrait;

    /**
     * @throws GeneralException
     */
    public function setUp()
    {
        $this->setLoggedInUserRole(Role::CLIENT_ADMINISTRATIVE_USER_ROLE);
        parent::setUp();
    }

    /**
     * @test
     *
     * @throws \Exception
     * @throws \PHPUnit\Framework\AssertionFailedError
     */
    public function it_can_read_reports()
    {
        /**
         * this will not work on 'application/json' and trying to fix it to test non-'application/json', you will run into the following
         *  - the 'maatwebsite/excel' package will not allow you to suppress headers so you get the 'headers already sent' error from php
         *  - The solutions presented at http://stackoverflow.com/questions/36229372/cant-execute-any-test-on-vagrant-box-using-phpunit mess up debugging via PhpStorm
         *
         * Spent half a day on this.
         * Solutions
         * - rework report controllers to return string and forgo headers set in 'maatwebsite/excel'
         * - rework report controllers to get string 'maatwebsite/excel' and set headers in controller and add switch to forgo headers (look at how bridge works)
         * - do pull request to 'maatwebsite/excel' to supperss headers in Excel::create()->download()
         */

        $this->assertTrue($this->ClientObj->nativeCoas->first()->nativeAccounts->count() > 0, 'Run the Seeder first');

        $this->json(
            'GET',
            '/api/v1/report/clients/' . $this->ClientObj->id . '/nativeCoas/' . $this->ClientObj->nativeCoas->first()->id . '/report',
            [],
            [
                'Content-Type' => 'application/json',
            ]
        );
        $this->assertApiSuccess();

        $this->asserttrue($this->getJSONContent()['success']);
        $this->assertTrue(count($this->getDataObjectArr()) > 0);

        $ReportTemplateObjArr = $this->ReportTemplateRepositoryObj->findWhere(
            [
                'client_id'               => $this->ClientObj->id,
                'is_boma_report_template' => true,
            ]
        );
        $this->assertTrue($ReportTemplateObjArr->count() == 1, 'Too many ReportTemplates');

        $this->json(
            'GET',
            '/api/v1/report/clients/' . $this->ClientObj->id . '/reportTemplates/' . $ReportTemplateObjArr->first()->id . '/report',
            [],
            [
                'Content-Type' => 'application/json',
            ]
        );
        $this->assertApiSuccess();
        $this->asserttrue($this->getJSONContent()['success']);
        $this->assertTrue(count($this->getDataObjectArr()) > 0);

        /****************************************/

        $this->json(
            'GET',
            '/api/v1/report/clients/' . $this->ClientObj->id . '/client_id_old/' . $this->ClientObj->client_id_old . '/property_groups/list/report',
            [],
            [
                'Content-Type' => 'application/json',
            ]
        );
        $this->assertApiSuccess();
        $this->asserttrue($this->getJSONContent()['success']);
        $this->assertTrue(count($this->getDataObjectArr()) > 0);

        /****************************************/

        $this->json(
            'GET',
            '/api/v1/report/clients/' . $this->ClientObj->id . '/properties/report',
            [],
            [
                'Content-Type' => 'application/json',
            ]
        );
        $this->assertApiSuccess();
        $this->asserttrue($this->getJSONContent()['success']);
        $this->assertTrue(count($this->getDataObjectArr()) > 0);

        $this->json(
            'GET',
            '/api/v1/report/clients/' . $this->ClientObj->id . '/users/report',
            [],
            [
                'Content-Type' => 'application/json',
            ]
        );
        $this->assertApiSuccess();
        $this->asserttrue($this->getJSONContent()['success']);
        $this->assertTrue(count($this->getDataObjectArr()) > 0);
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
