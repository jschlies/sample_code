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
use App\Waypoint\Models\PasswordRule;
use App;
use App\Waypoint\Tests\Factory;

/**
 * @codeCoverageIgnore
 */
trait MakePasswordRuleTrait
{
    /**
     * Create fake instance of PasswordRule and save it in database
     *
     * @param array $password_rules_arr
     * @return PasswordRule
     */
    public function makePasswordRule($password_rules_arr = [])
    {
        $theme = $this->fakePasswordRuleData($password_rules_arr);
        return $this->PasswordRuleRepositoryObj->create($theme);
    }

    /**
     * Get fake instance of PasswordRule
     *
     * @param array $password_rules_arr
     * @return PasswordRule
     */
    public function fakePasswordRule($password_rules_arr = [])
    {
        return new PasswordRule($this->fakePasswordRuleData($password_rules_arr));
    }

    /**
     * Get fake data of PasswordRule
     *
     * @param array $password_rules_arr
     * @param string $factory_name
     * @return array
     */
    public function fakePasswordRuleData($password_rules_arr = [], $factory_name = Seeder::PHPUNIT_FACTORY_NAME)
    {
        /** @var  $factory Factory */
        $factory = app(Factory::class);
        $factory->setProvidedValuesArr($password_rules_arr);
        return $factory->raw(PasswordRule::class, $password_rules_arr, $factory_name);
    }
}