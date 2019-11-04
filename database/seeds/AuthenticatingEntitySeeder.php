<?php

use App\Waypoint\Models\AuthenticatingEntity;
use App\Waypoint\Seeder;

/**
 * Class AuthenticatingEntitySeeder
 */
class AuthenticatingEntitySeeder extends Seeder
{
    /**
     * AuthenticatingEntitySeeder constructor.
     * @param array $attributes
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($attributes = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($attributes, $count, $factory_name);
        $this->setResultingClass(AuthenticatingEntity::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}