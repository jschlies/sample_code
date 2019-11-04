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
use App\Waypoint\Models\NotificationLog;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakeNotificationLogTrait
{
    /**
     * Create fake instance of NotificationLog and save it in database
     *
     * @param array $notification_logs_arr
     * @return NotificationLog
     */
    public function makeNotificationLog($notification_logs_arr = [])
    {
        $theme = $this->fakeNotificationLogData($notification_logs_arr);
        return $this->NotificationLogRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of NotificationLog
     *
     * @param array $notification_logs_arr
     * @return NotificationLog
     */
    public function fakeNotificationLog($notification_logs_arr = [])
    {
        return new NotificationLog($this->fakeNotificationLogData($notification_logs_arr));
    }

    /**
     * Get fake data of NotificationLog
     *
     * @param array $notification_logs_arr
     * @param string $factory_name
     * @return array
     */
    public function fakeNotificationLogData($notification_logs_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($notification_logs_arr);
        return $factory->raw(NotificationLog::class, $notification_logs_arr, $factory_name);
    }
}