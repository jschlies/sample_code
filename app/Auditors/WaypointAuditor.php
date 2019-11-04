<?php

namespace App\Waypoint\Auditors;

use App;
use App\Waypoint\Model;
use App\Waypoint\Models\AccessList;
use App\Waypoint\Models\AccessListProperty;
use App\Waypoint\Models\AccessListUser;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceApproval;
use App\Waypoint\Models\AdvancedVarianceExplanationType;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\AuthenticatingEntity;
use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Models\CalculatedFieldEquation;
use App\Waypoint\Models\CalculatedFieldEquationProperty;
use App\Waypoint\Models\CalculatedFieldVariable;
use App\Waypoint\Models\Client;
use App\Waypoint\Models\EcmProject;
use App\Waypoint\Models\Lease;
use App\Waypoint\Models\Opportunity;
use App\Waypoint\Models\Property;
use App\Waypoint\Models\PropertyGroup;
use App\Waypoint\Models\PropertyGroupProperty;
use App\Waypoint\Models\User;
use Config;
use DB;
use OwenIt\Auditing\Contracts\Auditable;
use Webpatser\Uuid\Uuid;
use \OwenIt\Auditing\Models\Audit;

class WaypointAuditor extends \OwenIt\Auditing\Drivers\Database
{
    /**
     * @param Auditable $AuditableObj
     * @return $this|bool|\Illuminate\Database\Eloquent\Model|Audit
     * @throws \Exception
     */
    public function audit(Auditable $AuditableObj)
    {
        /** @var Client $ClientObj */

        /**
         * This is the code that determines if we need to be 'auditing' this create/update/delete for this client.
         * The .env value of ENABLE_AUDITS can be overridden by a client conf of ENABLE_AUDITS.
         *
         * Note that the owen-it/laravel-auditing package expects this to be done at AuditableTrait::isAuditingEnabled() but since that's a static call
         * thus impossible to get the $ClientObj at that point in the processing. Thus we unconditionally return true from AuditableTrait::isAuditingEnabled()
         * and check the $ClientObj conf here.
         * Also note we return an empty Audit(); object to make owen-it/laravel-auditing happy. It is not committed to DB
         */
        if (
            AuthenticatingEntity::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, AuthenticatingEntity::class)
        )
        {
            if ( ! config('waypoint.enable_audits', false))
            {
                return new Audit();
            }
        }
        else
        {
            $ClientObj = $this->captureClient($AuditableObj);

            if ($ClientObj->getConfigValue('ENABLE_AUDITS') === false)
            {
                return new Audit();
            }
            elseif ($ClientObj->getConfigValue('ENABLE_AUDITS') === null)
            {
                if ( ! config('waypoint.enable_audits', false))
                {
                    return new Audit();
                }
            }
            elseif ($ClientObj->getConfigValue('ENABLE_AUDITS') !== true)
            {
                throw new App\Waypoint\Exceptions\GeneralException('Mis-configured object encountered at ' . __FILE__ . ':' . __LINE__);
            }
        }

        if (App::runningInConsole())
        {
            if ( ! (bool) Config::get('audit.console', false))
            {
                return new Audit();
            }
        }

        /** @var Auditable $AuditableObj */
        $AuditReportObj = parent::audit($AuditableObj);
        if ( ! method_exists($AuditableObj, 'getBelongsToArr') || ! $AuditableObj->auditIncludeRelated)
        {
            return $AuditReportObj;
        }

        /**
         * now audit all getBelongsToArr relations of $AuditReportObj
         */
        foreach ($AuditableObj->getBelongsToArr() as $belongs_to_method)
        {
            /** @var Model $BelongsToObj */
            if ($BelongsToObj = $AuditableObj->$belongs_to_method)
            {
                $sql = "INSERT INTO audit_relations SET  
                    id  = :id,
                    audit_id  = :audit_id,
                    type  = :type,
                    source_relation = :source_relation,
                    source_relation_id = :source_relation_id,
                    target_relation = :target_relation ,
                    target_relation_id = :target_relation_id,
                    created_at = :created_at
                ";
                DB::insert(
                    DB::raw($sql),
                    [
                        'id'                 => Uuid::generate()->__get('string'),
                        'audit_id'           => $AuditReportObj->id,
                        'type'               => 'belongsTo',
                        'source_relation'    => get_class($AuditableObj),
                        'source_relation_id' => $AuditableObj->id,
                        'target_relation'    => get_class($BelongsToObj),
                        'target_relation_id' => $BelongsToObj->id,
                        'created_at'         => $BelongsToObj->created_at,
                    ]
                );
            }
        }
        return $AuditReportObj;
    }

    /**
     * figure out what $AuditableObj is and extract it's client
     * @param Auditable $AuditableObj
     * @return mixed
     */
    public function captureClient(Auditable $AuditableObj)
    {
        if (
            AccessList::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, AccessList::class) ||
            AdvancedVarianceExplanationType::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, AdvancedVarianceExplanationType::class) ||
            Property::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, Property::class) ||
            PropertyGroup::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, PropertyGroup::class) ||
            User::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, User::class)
        )
        {
            $ClientObj = $AuditableObj->client;
        }
        elseif (
            AccessListProperty::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, AccessListProperty::class) ||
            AccessListUser::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, AccessListUser::class)
        )
        {
            $ClientObj = $AuditableObj->accessList->client;
        }
        elseif (
            AdvancedVariance::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, AdvancedVariance::class) ||
            EcmProject::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, EcmProject::class) ||
            Lease::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, Lease::class) ||
            Opportunity::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, Opportunity::class)
        )
        {
            $ClientObj = $AuditableObj->property->client;
        }
        elseif (
            AdvancedVarianceApproval::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, AdvancedVarianceApproval::class) ||
            AdvancedVarianceLineItem::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, AdvancedVarianceLineItem::class)
        )
        {
            $ClientObj = $AuditableObj->advancedVariance->property->client;
        }
        elseif (
            CalculatedField::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, CalculatedField::class)
        )
        {
            $ClientObj = $AuditableObj->reportTemplate->client;
        }
        elseif (
            CalculatedFieldEquation::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, CalculatedFieldEquation::class)
        )
        {
            $ClientObj = $AuditableObj->calculatedField->reportTemplate->client;
        }
        elseif (
            CalculatedFieldEquationProperty::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, CalculatedFieldEquationProperty::class) ||
            CalculatedFieldVariable::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, CalculatedFieldVariable::class)
        )
        {
            $ClientObj = $AuditableObj->calculatedFieldEquation->calculatedField->reportTemplate->client;
        }
        elseif (
            Client::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, Client::class)
        )
        {
            $ClientObj = $AuditableObj;
        }
        elseif (
            PropertyGroupProperty::class == get_class($AuditableObj) || is_subclass_of($AuditableObj, PropertyGroupProperty::class)
        )
        {
            $ClientObj = $AuditableObj->propertyGroup->client;
        }
        else
        {
            throw new App\Waypoint\Exceptions\GeneralException('Mis-configured object encountered at ' . __FILE__ . ':' . __LINE__);
        }

        return $ClientObj;
    }
}
