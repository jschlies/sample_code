<?php

namespace App\Waypoint\Tests\Generated;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Seeder;
use App\Waypoint\Models\DownloadHistory;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeDownloadHistoryTrait
{
    /**
     * Create fake instance of DownloadHistory and save it in database
     *
     * @param array $download_histories_arr
     * @return DownloadHistory
     */
    public function makeDownloadHistory($download_histories_arr = [])
    {
        $theme = $this->fakeDownloadHistoryData($download_histories_arr);
        return $this->DownloadHistoryRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of DownloadHistory
     *
     * @param array $download_histories_arr
     * @return DownloadHistory
     */
    public function fakeDownloadHistory($download_histories_arr = [])
    {
        return new DownloadHistory($this->fakeDownloadHistoryData($download_histories_arr));
    }

    /**
     * Get fake data of DownloadHistory
     *
     * @param array $download_histories_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeDownloadHistoryData($download_histories_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($download_histories_arr);
        return $factory->raw(DownloadHistory::class, $download_histories_arr, $factory_name);
    }
}