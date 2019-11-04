<?php

namespace App\Waypoint;

use App\Waypoint\Models\Opportunity;
use DB;
use OwenIt\Auditing\Auditable;

/**
 * Class ModelSaveAndValidateTrait
 * @package App\Waypoint\Models
 *
 * NOTE NOTE NOTE
 * This trait exists because the User, permission and role models
 * extend App\Waypoint\Models\Entrust\User which extends App\Waypoint\Models\Entrust which extends blah blah.
 * the point is that if we want to add base functionality to all models, we need to use this trait
 *
 */
trait AuditableTrait
{
    use Auditable;

    /**
     * @var int
     * Clear the oldest audits after N records.
     */
    protected $auditLimit = 100000000;

    /**
     * @var array
     * Fields that you do NOT want to audit.
     */
    protected $auditExclude = ['created_at', 'updated_at'];

    /**
     * @var array
     * Specify what actions you want to audit.
     */
    protected $auditableTypes = ['created', 'updated', 'deleted'];

    /**
     * @var array
     */
    public static $auditCustomFields = [];

    /**
     * @param bool $limit_to_object_attributes
     * @return array
     */
    public function getAuditArr($limit_to_object_attributes = false, $generations = 2)
    {
        if ( ! method_exists($this, 'audits') and $generations >= 1)
        {
            return [];
        }
        /** @noinspection PhpParamsInspection */
        $return_me = $this->process_audits($this, $limit_to_object_attributes, $generations - 1);

        return $return_me;
    }

    /**
     * @param Model $ModelObj
     * @return array
     */
    public function process_audits($ModelObj, $limit_to_object_attributes = false, $generations = 2)
    {
        /**
         * DO NOT USE EAGER LOADING HERE!!!!
         * @todo - fix this
         */
        /** @var Opportunity $ModelObj */
        /** @noinspection PhpUndefinedMethodInspection */
        $SortedAllObjArr = $ModelObj->audits()->get()->sort(
            function ($a, $b)
            {
                if ($a->type == 'created')
                {
                    return 0;
                }
                if ($b->type == 'created')
                {
                    return 1;
                }
                if ($a->type == 'deleted')
                {
                    return 1;
                }
                if ($b->type == 'deleted')
                {
                    return 0;
                }
                return $a->created_at > $b->created_at;
            }
        );
        $update_history  = [];
        $belongsTo       = [];

        /** @var \OwenIt\Auditing\Models\Audit $AuditObj */
        foreach ($SortedAllObjArr as $AuditObj)
        {
            $event_arr              = [];
            $event_arr ['Metadata'] = $AuditObj->getMetadata();
            $event_arr ['Modified'] = $AuditObj->getModified();
            $update_history[]       = $event_arr;
        }

        if ($limit_to_object_attributes)
        {
            return ['update_history' => $update_history, 'belongsTo' => []];
        }

        $sql = "SELECT source_relation,source_relation_id  
                    FROM audit_relations 
                    WHERE
                        target_relation  = :target_relation AND
                        target_relation_id = :target_relation_id 
                    GROUP BY source_relation,source_relation_id
                ";

        $audit_relation_arr = DB::select(
            $sql,
            [
                'target_relation_id' => $ModelObj->id,
                'target_relation'    => $ModelObj->getMorphClass(),
            ]
        );

        foreach ($audit_relation_arr as $audit_relation)
        {
            $full_class = $audit_relation->source_relation;

            /** @noinspection PhpUndefinedMethodInspection */
            if ($AuditRelationObj = $full_class::find($audit_relation->source_relation_id))
            {
                /** @noinspection PhpUndefinedMethodInspection */
                $belongsTo[$audit_relation->source_relation][$audit_relation->source_relation_id] = $full_class::find($audit_relation->source_relation_id)->getAuditArr(
                    true, $generations
                );
            }
            else
            {
                $sql = "
                            SELECT *  
                                FROM audit_relations 
                        
                                JOIN audits ON audit_id = audits.id 
                        
                                WHERE
                                    audit_relations.target_relation  = :target_relation AND
                                    audit_relations.target_relation_id = :target_relation_id 
                                GROUP BY source_relation,source_relation_id
                        ";

                $audit_relation_deleted_rec_arr = DB::select(
                    $sql,
                    [
                        'target_relation_id' => $ModelObj->id,
                        'target_relation'    => $ModelObj->getMorphClass(),
                    ]
                );

                $belongsTo[$audit_relation->source_relation][$audit_relation->source_relation_id] = json_decode(json_encode($audit_relation_deleted_rec_arr));
            }
        }

        $return_me = ['update_history' => $update_history];
        if ($belongsTo)
        {
            $return_me['belongsTo'] = $belongsTo;
        }
        return $return_me;
    }

    /**
     * @return null
     */
    public static function resolveId()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return \Auth::check() ? \Auth::user()->getAuthIdentifier() : null;
    }

    /**
     * Determine whether auditing is enabled.
     *
     * @return bool
     */
    public static function isAuditingEnabled()
    {
        /**
         * Unconditionally return true. See WaypointAuditor::audit() for logic that
         * allows client level overrides
         */
        return true;
    }
}