<?php

namespace App\Waypoint;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\Attachment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as ModelAbstract;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;

/**
 * Class Model
 * @package App\Waypoint
 * @method static Builder where($column, $operator = null, $value = null, $boolean = 'and') desc
 * @method static Builder orWhere($column, $operator = null, $value = null, $boolean = 'and') desc
 * @method static Builder whereNull($column) desc
 * @method int count($columns = '*') desc
 *
 * @todo    - review these methods and the User, Permission and Role models. Consider
 *       using traits for methods found here and in User, Permission and Role due to fact that User, Permission and Role
 *       extend App\Waypoint\Models\Entrust, not App\Waypoint\Model
 */
class Model extends ModelAbstract
{
    use ModelSaveAndValidateTrait;
    use ModelDateFormatterTrait;

    /**
     * @todo move this ti Cache our (yet to be) Cache class
     */
    const CACHE_TAG_DEFAULT_TTL = 3600;

    /**
     * at times, like in TenantDetails, we want to pimit the leases of a certain subset of properties. Maybe,
     * for example, we're getting TenantDetails in a propertyGroup context. Per Laura, the leases of
     * these tenants should be limited to the leased to the properties the propertyGroup . See
     * Model def of TenantDetails
     * @var null
     */
    public static $limit_leases_these_property_id_arr = null;

    /** @var bool */
    protected $mustBeApproved = false;

    /** @var bool */
    protected $canBeRated = false;

    /**
     * this is needed to filter on $UserObj->is_hidden.
     * @var string|null
     */
    static public $requesting_user_role = null;

    /**
     * @var MessageBag|array
     */
    protected $errors;

    /**
     * @var array
     */
    public static $hasMany_arr = [

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

    ];

    /**
     * @var array
     */
    public static $belongsToMany_arr = [

    ];

    /** @var boolean */
    public $auditIncludeRelated = true;
    /** @var boolean */
    public static $suppress_use_of_pre_calcs = false;

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
     * @return mixed
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), ["model_name" => get_class($this)]);
    }

    /**
     * Model constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        /**
         * Shameless hack.
         */
        if (method_exists($this, 'construct_scaffold'))
        {
            $this->construct_scaffold();
        }
        parent::__construct($attributes);
    }

    /**
     * Set the dates associated with the model.
     *
     * @param $dates array
     * @return $this
     */
    public function setDates($dates)
    {
        $this->dates = $dates;
        return $this;
    }

    /**
     * Set the fillable associated with the model.
     *
     * @param $fillable array
     * @return $this
     */
    public function setFillable($fillable)
    {
        $this->fillable = $fillable;
        return $this;
    }

    /**
     * Set the casts associated with the model.
     *
     * @param $casts array
     * @return $this
     */
    public function setCasts($casts)
    {
        $this->casts = $casts;
        return $this;
    }

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [];

    /**
     * Generated Validation rules
     *
     * @var array
     */
    public static $baseRules = [];

    /**
     * It is HIGHLY advised that you overload this with a method that adds/deletes rules
     * as needed by model AND calls parent::get_model_rules()
     *
     * @param null|array $rules
     * @return null|array
     * @throws GeneralException
     */
    public static function get_model_rules($rules = null, $object_id = null)
    {
        if ($rules == null)
        {
            throw new GeneralException('Please pass in rules or call this method via a model class', 500);
        }

        /**
         * clear out Timestamp stuff
         */
        self::unset_if_set(['created_at', 'updated_at'], $rules);

        /**
         * If we are in an create context, remove the 'object_id' string from the rule
         * If update context, replace'object_id' with id of updated obj
         */
        foreach ($rules as $field => $rule)
        {
            if (is_array($rule))
            {
                /**
                 * deal w/ Laravel but re:regex validation rules
                 *
                 * See http://stackoverflow.com/questions/22596587/issue-with-laravel-rules-regex-or-operator
                 */
                continue;
            }
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
        $rules['id'] = 'sometimes|nullable|integer';

        return $rules;
    }

    /**
     * @return string
     */
    public function getShortModelName()
    {
        $model_name = explode('\\', get_class($this));
        $return_me  = array_pop($model_name);
        return $return_me;
    }

    /**
     * @return string
     */
    public static function getShortModelNameFromModelName($model_name)
    {
        $model_name = explode('\\', $model_name);
        $return_me  = array_pop($model_name);
        return $return_me;
    }

    /**
     * Delete the model from the database.
     *
     * @return bool|null
     *
     * @throws \Exception
     */
    public function delete()
    {
        if (method_exists(get_class($this), 'attachments'))
        {
            /** @var Attachment $attachment */
            /** @noinspection PhpUndefinedMethodInspection */
            foreach ($this->attachments()->get() as $attachment)
            {
                $attachment->delete();
            }
        }
        return parent::delete();
    }

    /**
     * @param array $array_to_clean_up
     * @param array $element_to_delete_arr
     * @return array
     */
    public static function unset_if_set(array $array_to_clean_up, array $element_to_delete_arr)
    {
        foreach ($element_to_delete_arr as $element_to_delete)
        {
            /**
             * workaround Laravel bug
             *
             * See http://stackoverflow.com/questions/22596587/issue-with-laravel-rules-regex-or-operator
             */
            if (is_array($element_to_delete))
            {
                continue;
            }
            $array_to_clean_up[$element_to_delete] = null;
            unset($array_to_clean_up[$element_to_delete]);
        }
        return $array_to_clean_up;
    }

    /**
     * @return Collection|array
     */
    public function getComments()
    {
        $CommentCollectionObj = new Collection();
        if (method_exists($this, 'comments'))
        {
            foreach ($this->comments as $CommentObj)
            {
                $CommentCollectionObj[] = $CommentObj;
            }
        }
        return $CommentCollectionObj;
    }

    /**
     * @return \App\Waypoint\Collection|array
     */
    public function getAttachments()
    {
        $AttachmentCollectionObj = new Collection();
        if (method_exists($this, 'attachments'))
        {
            $AttachmentCollectionObj = collect_waypoint($this->attachments()->get());
        }
        return $AttachmentCollectionObj;
    }

    /**
     * @throws GeneralException
     */
    public static function getRepository()
    {
        throw new GeneralException('This method should not be called directly, rather overload this in model class', 500);
    }

    /**
     * @return string
     */
    public static function getAccessPolicyName()
    {
        return str_replace('\\', '', Str::snake(Str::plural(class_basename(self::class))));
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        $return_me = $this->getAttribute($key);
        if (
            $return_me instanceof \Illuminate\Support\Collection &&
            ! $return_me instanceof Collection
        )
        {
            return collect_waypoint($return_me);
        }
        return $return_me;
    }

    /**
     * @return array
     */
    public function parentToArray()
    {
        return parent::attributesToArray();
    }
}