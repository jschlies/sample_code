<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Auth0\Auth0ApiManagementConnection;
use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use \App\Waypoint\Repository as BaseRepository;
use App\Waypoint\Models\PasswordRule;

/**
 * Class PropertyRepository
 * @package App\Waypoint\Repositories
 */
class PasswordRuleRepository extends BaseRepository
{
    /** @var Auth0ApiManagementConnection */
    private static $Auth0ApiManagementConnectionObj = null;

    public function create(array $attributes)
    {
        return parent::create($attributes);
    }

    /**
     * @return string
     */
    public function model()
    {
        return PasswordRule::class;
    }

    /**
     * @return Collection
     * @throws GeneralException
     */
    public function get_password_rules($identity_connection = 'Username-Password-Authentication')
    {
        $ConnectionObj = self::getAuth0ApiManagementConnectionObj()->get_connections_with_name('Username-Password-Authentication');

        if (
            isset($ConnectionObj) && $ConnectionObj &&
            isset($ConnectionObj->options) &&
            isset($ConnectionObj->options->passwordPolicy) &&
            in_array($ConnectionObj->options->passwordPolicy, PasswordRule::$passwword_rule_type_arr)
        )
        {
            $PasswordRuleObjArr = $this->PasswordRuleRepositoryObj->findWhere(
                ['password_rule_type' => $ConnectionObj->options->passwordPolicy]
            );
        }
        elseif (
            isset($ConnectionObj) && $ConnectionObj &&
            isset($ConnectionObj->options) &&
            isset($ConnectionObj->options->passwordPolicy) &&
            $ConnectionObj->options->passwordPolicy == 'none'
        )
        {
            $PasswordRuleObjArr = new Collection();
        }
        else
        {
            /**
             * remove before pushing to develop
             */
            $PasswordRuleObjArr = $this->findWhere(
                ['password_rule_type' => 'fair']
            );
        }

        return $PasswordRuleObjArr;
    }

    /**
     * @return mixed
     */
    public static function getAuth0ApiManagementConnectionObj()
    {
        if ( ! self::$Auth0ApiManagementConnectionObj)
        {
            self::$Auth0ApiManagementConnectionObj = \App::make(Auth0ApiManagementConnection::class);
        }
        return self::$Auth0ApiManagementConnectionObj;
    }

    /**
     * @param mixed $Auth0ApiManagementConnectionObj
     */
    public static function setAuth0ApiManagementConnectionObj($Auth0ApiManagementConnectionObj): void
    {
        self::$Auth0ApiManagementConnectionObj = $Auth0ApiManagementConnectionObj;
    }
}
