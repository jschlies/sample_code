<?php

use App\Waypoint\Models\PasswordRule;
use App\Waypoint\Repositories\PasswordRuleRepository;
use App\Waypoint\Seeder;

/**
 * Class PasswordRuleSeeder
 */
class PasswordRuleSeeder extends Seeder
{
    /**
     * PasswordRuleSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(PasswordRule::class);
        $this->ModelRepositoryObj = App::make(PasswordRuleRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        return parent::run();
    }
}