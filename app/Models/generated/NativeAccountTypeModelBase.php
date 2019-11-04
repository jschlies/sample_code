<?php

namespace App\Waypoint\Models;

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;

/**
 * README - README - README - README - README
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 * See readme.md
 * This file is generated - edits to this file will be lost.
 * Please read and understand the info on generating models/controllers/requests/test in the readme.md
 * THIS MEANS YOU - DO NOT EDIT - DO NOT EDIT - YOU HAVE BEEN WARNED - IGNORE AT YOU OWN PERIL
 */

use App\Waypoint\Model;

/**
 * Class NativeAccountType
 *
 * @method static NativeAccountType find($id, $columns = ['*']) desc
 * @method static Collection all($columns = ['*']) desc
 * @method static Collection findMany($ids, $columns = ['*']) desc
 * @method static NativeAccountType|Collection findOrFail($id, $columns = ['*']) desc
 */
class NativeAccountTypeModelBase extends Model
{
    /**
     * Generated
     */

    /**
     * PropertyModelBase constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * Validation rules which get 'merged' with self::$baseRules into self::$rules at $this::__constructor() time
     *
     * @var array
     */
    public static $baseRules = [
        'id'                              => 'sometimes|integer',
        'native_account_type_name'        => 'sometimes|min:3|max:255',
        'native_account_type_description' => 'sometimes|nullable|min:3|max:255',
        'display_name'                    => 'sometimes|nullable|min:3|max:255',
        'client_id'                       => 'sometimes|integer',
        'created_at'                      => 'sometimes',
        'updated_at'                      => 'sometimes',
    ];

    /**
     * @var array
     */
    public static $hasMany_arr = [
        "advancedVarianceThreshold",
        "nativeAccountTypeTrailer",
        "nativeAccount",
        "reportTemplateAccountGroup",
    ];

    /**
     * @var array
     */
    public static $hasOne_arr = [

    ];

    /**
     * @var array
     */
    public static $belongsTo_arr = [
        "client",
    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    public function construct_scaffold()
    {
        $this->setTable('native_account_types');
        $this->setFillable(
            [

                'native_account_type_name',
                'native_account_type_description',
                'display_name',
                'client_id',

            ]
        );
        $this->setCasts(
            [

                'id'                              => 'integer',
                'native_account_type_name'        => 'string',
                'native_account_type_description' => 'string',
                'display_name'                    => 'string',
                'client_id'                       => 'integer',

            ]
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function client()
    {
        return $this->belongsTo(
            Client::class,
            'client_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function advancedVarianceThresholds()
    {
        return $this->hasMany(
            AdvancedVarianceThreshold::class,
            'native_account_type_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function nativeAccountTypeTrailers()
    {
        return $this->hasMany(
            NativeAccountTypeTrailer::class,
            'native_account_type_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function nativeAccounts()
    {
        return $this->hasMany(
            NativeAccount::class,
            'native_account_type_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     **/
    public function reportTemplateAccountGroups()
    {
        return $this->hasMany(
            ReportTemplateAccountGroup::class,
            'native_account_type_id',
            'id'
        );
    }

    /**
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            $rules = array_merge(NativeAccountType::$baseRules, NativeAccountType::$rules);
        }
        $rules = parent::get_model_rules($rules, $object_id);
        return $rules;
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * This is needed to get Audits to work
     *
     * @return string
     */
    public function getMorphClass()
    {
        return NativeAccountType::class;
    }

    /**
     * @return array
     */
    public function getHasManyArr()
    {
        return self::$hasMany_arr;
    }

    /**
     * @return array
     */
    public function getHasOneArr()
    {
        return self::$hasOne_arr;
    }

    /**
     * @return array
     */
    public function getBelongsToArr()
    {
        return self::$belongsTo_arr;
    }

    /**
     * @return array
     */
    public function getBelongsToManyArr()
    {
        return self::$belongsToMany_arr;
    }

    /**
     * End Of Generated
     */
}
