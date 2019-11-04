<?php

namespace App\Waypoint\Models;

/**
 * see https://github.com/chrisbjr/api-guard
 */

use App\Waypoint\Collection;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Exceptions\ValidationException;
use App\Waypoint\Model;
use App\Waypoint\ModelSaveAndValidateTrait;
use Chrisbjr\ApiGuard\Models\ApiKey as ApiKeyModelBase;

/**
 * Class ApiKey
 * @package App\Waypoint\Models
 */
class ApiKey extends ApiKeyModelBase
{
    use ModelSaveAndValidateTrait;

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'user_id' => 'required|integer',
        'key'     => 'required|string|max:255|unique:api_keys,key,object_id',
    ];

    /**
     * Validation rules which get 'merged' with self::$baseRules into self::$rules at $this::__constructor() time
     *
     * @var array
     */
    public static $baseRules = [
        'user_id' => 'sometimes|nullable|integer',
        'key'     => 'sometimes|string|max:255',
        'level'   => 'sometimes|nullable|integer',
    ];

    /**
     *
     * note that this differs from pattern due to the fact that this inherits Chrisbjr\ApiGuard\Repositories\ApiKeyRepository
     * rather than the typical
     */
    public function toArray(): array
    {
        return
            [
                "id"            => $this->id,
                "user_id"       => $this->user_id,
                "key"           => $this->key,
                "level"         => $this->level ? true : false,
                "ignore_limits" => $this->ignore_limits ? true : false,
                "model_name"    => self::class,
            ];
    }

    /**
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {
        $this->key = substr(sha1(time() . mt_rand()), 0, 40);
        $this->save();
        return parent::delete();
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
            $rules = array_merge(self::$baseRules, self::$rules);
        }

        Model::unset_if_set(['id', 'created_at', 'updated_at'], $rules);

        foreach ($rules as $field => $rule)
        {
            $rule_as_array = explode(',', $rule);
            if ($rule_as_array[count($rule_as_array) - 1] == 'object_id')
            {
                if ($object_id)
                {
                    $rule_as_array[count($rule_as_array) - 1] = $object_id;
                }
                else
                {
                    array_pop($rule_as_array);
                }
                $rules[$field] = implode(',', $rule_as_array);
            }

        }

        return $rules;
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param array $models
     * @return Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    /**
     * @return string
     *
     * @todo deal with this - This is here since this does not inherit App/Waypoint/Model for Entrust reasons
     */
    public function getShortModelName()
    {
        $model_name = explode('\\', get_class($this));
        $return_me  = array_pop($model_name);
        return $return_me;
    }

    /**
     * @var array
     * @todo deal with this - This is here since this does not inherit App/Waypoint/Model for Entrust reasons
     */
    public static $hasMany_arr = [
    ];

    /**
     * @var array
     * @todo deal with this - This is here since this does not inherit App/Waypoint/Model for Entrust reasons
     */
    public static $hasOne_arr = [
        /**
         * Remember this is an oddball class
         */
    ];

    /**
     * @var array
     * @todo deal with this - This is here since this does not inherit App/Waypoint/Model for Entrust reasons
     */
    public static $belongsTo_arr = [
        /**
         * Remember this is an oddball class
         */
    ];

    /**
     * @var array
     * @todo deal with this - This is here since this does not inherit App/Waypoint/Model for Entrust reasons
     */
    public static $belongsToMany_arr = [
        /**
         * Remember this is an oddball class
         */
    ];

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
     * @param array $options
     * @return $this
     * @throws ValidationException
     *
     * @todo this could be better - learn to use messagebags https://laravel.com/docs/5.4/validation#working-with-error-messages
     */
    public function save(array $options = [])
    {
        if ( ! self::isSuspendValidation())
        {
            if ($rules = $this::get_model_rules(null, $this->id ?: null))
            {
                /**
                 * why not simply do
                 *
                 * $thing_to_validate                = $this->toArray();
                 *
                 * because we have too much logic in our toArray() methods. See propertyDetail or User.
                 * $this->getAttributes(); returns the bare minimun needed by $ValidatorObj->fails()
                 */
                $thing_to_validate = $this->toArray();
                $ValidatorObj      = \Validator::make($thing_to_validate, $rules);

                if ($ValidatorObj->fails())
                {
                    $this->errors           = $ValidatorObj->errors();
                    $ValidationExceptionObj = new ValidationException($ValidatorObj->errors());
                    $ValidationExceptionObj->setValidationErrors($ValidatorObj->errors());
                    throw $ValidationExceptionObj;
                }
            }
        }
        /**
         * we still want to validate in save even if model is not dirty
         */
        if ($this->isDirty())
        {
            parent::save($options);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        if ($rules = $this::get_model_rules($this::$rules, $this->id ?: null))
        {
            /**
             * why not simply do
             *
             * $thing_to_validate                = $this->toArray();
             *
             * because we have too much logic in our toArray() methods. See propertyDetail or User.
             * $this->getAttributes(); returns the bare minimun needed by $ValidatorObj->fails()
             */
            $thing_to_validate = $this->toArray();
            $ValidatorObj      = \Validator::make($thing_to_validate, $rules);

            if ($ValidatorObj->fails())
            {
                $this->errors = $ValidatorObj->errors();
                return false;
            }
        }

        return true;
    }
}
