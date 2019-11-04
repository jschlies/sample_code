<?php

namespace App\Waypoint;

use App\Waypoint\Exceptions\ValidationException;

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
trait ModelSaveAndValidateTrait
{
    protected static $suspend_validation = false;

    /**
     * @param array $options
     * @return $this
     * @throws \App\Waypoint\Exceptions\ValidationException
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
                $thing_to_validate = $this->parentToArray();
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
            $thing_to_validate = $this->parentToArray();
            $ValidatorObj      = \Validator::make($thing_to_validate, $rules);

            if ($ValidatorObj->fails())
            {
                $this->errors = $ValidatorObj->errors();
                return false;
            }
        }

        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return bool
     */
    public static function isSuspendValidation(): bool
    {
        return self::$suspend_validation;
    }

    /**
     * @param bool $suspend_validation
     */
    public static function setSuspendValidation(bool $suspend_validation): void
    {
        self::$suspend_validation = $suspend_validation;
    }
}