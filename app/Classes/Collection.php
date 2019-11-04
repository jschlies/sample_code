<?php

namespace App\Waypoint;

use App\Waypoint\Models\Permission;
use App\Waypoint\Models\Role;
use App\Waypoint\Models\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection as BaseCollection;

/**
 * Class Collection
 * @package App\Waypoint
 */
class Collection extends BaseCollection
{
    /** @var array */
    public $metadata = null;

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $return_me = [];
        /** @var Model|User|Permission|Role $value */
        foreach ($this->items as $value)
        {
            if (is_object($value) && $value->hasCast('id'))
            {
                if (method_exists($value, 'getShortModelName'))
                {
                    $return_me[$value->getShortModelName() . '_' . $value->id] = $value instanceof Arrayable ? $value->toArray() : $value;
                }
                else
                {
                    $return_me[$value->id] = $value instanceof Arrayable ? $value->toArray() : $value;
                }
            }
            else
            {
                $return_me[] = $value instanceof Arrayable ? $value->toArray() : $value;
            }
        }
        return $return_me;
    }

    /**
     * Get the collection of items as a plain array.
     *
     * seems that array_unique returns what it should but the array indexes
     * are not as expected so we call array_values()
     *
     * @return array
     */
    public function getArrayOfIDsFormatted()
    {
        return array_values(
            array_unique(
                array_map(
                    function ($value)
                    {
                        /**
                         * @todo review and/or fix this
                         * note that you may be tempted to restrict $value to the class App\Waypoint\Model
                         * in this functions signature but remember that App\Waypoint\Model\User
                         * extends Entrust, not App\Waypoint\Model
                         */
                        /** @var  Model|User|Permission|Role $value */
                        return $value->getShortModelName() . '_' . $value->id;
                    }, $this->items

                )
            )
        );
    }

    /**
     * Get the collection of items as a plain array.
     *
     * seems that array_unique returns what it should but the array indexes
     * are not as expected so we call array_values()
     *
     * @return array
     */
    public function getArrayOfIDs()
    {
        return array_values(
            array_unique(
                array_map(
                    function ($value)
                    {
                        /**
                         * @todo review and/or fix this
                         * note that you may be tempted to restrict $value to the class App\Waypoint\Model
                         * in this functions signature but remember that App\Waypoint\Model\User
                         * extends Entrust, not App\Waypoint\Model
                         */
                        return $value->id;
                    },
                    $this->items
                )
            )
        );
    }

    /**
     * @param $field
     * @return array
     *
     * seems that array_unique returns what it should but the array indexes
     * are not as expected so we call array_values()
     */
    public function getArrayOfGivenFieldValues($field)
    {
        return array_values(
            array_unique(
                array_map(
                    function ($item) use ($field)
                    {
                        /**
                         * @todo review and/or fix this
                         * note that you may be tempted to restrict $value to the class App\Waypoint\Model
                         * in this functions signature but remember that App\Waypoint\Model\User
                         * extends Entrust, not App\Waypoint\Model
                         */
                        return $item->{$field};
                    },
                    $this->items
                )
            )
        );
    }

    /**
     * Run a map over each of the items.
     *
     * @param callable $callback
     * @return \Illuminate\Support\Collection|static
     */
    public function map(callable $callback)
    {
        return collect_waypoint(parent::map($callback));
    }

    /**
     * Get a flattened array of the items in the collection.
     *
     * @param int $depth
     * @return \Illuminate\Support\Collection
     */
    public function flatten($depth = INF)
    {
        return collect_waypoint(parent::flatten($depth));
    }

    /**
     * Run a filter over each of the items.
     *
     * @param callable|null $callback
     * @return Collection|BaseCollection
     */
    public function filter(callable $callback = null)
    {
        return parent::filter($callback);
    }

    /**
     * Merge the collection with the given items.
     *
     * @param \ArrayAccess|array $items
     * @return static
     */
    public function merge($items)
    {
        return collect_waypoint(parent::merge($items));
    }

    /**
     * Return only unique items from the collection.
     *
     * @param string|callable|null $key
     * @param bool $strict
     * @return static|\Illuminate\Support\Collection
     */
    public function unique($key = null, $strict = false)
    {
        return collect_waypoint(parent::unique($key, $strict));
    }

    /**
     * Get an array with the values of a given key.
     *
     * @param string $value
     * @param string|null $key
     * @return \Illuminate\Support\Collection
     */
    public function pluck($value, $key = null)
    {
        return collect_waypoint(parent::pluck($value, $key));
    }

    /**
     * Get the keys of the collection items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function keys()
    {
        return collect_waypoint(parent::keys());
    }

    /**
     * Zip the collection together with one or more arrays.
     *
     * @param mixed ...$items
     * @return \Illuminate\Support\Collection
     */
    public function zip($items)
    {
        return collect_waypoint(parent::zip($items));
    }

    /**
     * Collapse the collection of items into a single array.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collapse()
    {
        return collect_waypoint(parent::collapse());
    }

    /**
     * Flip the items in the collection.
     *
     * @return \Illuminate\Support\Collection
     */
    public function flip()
    {
        return collect_waypoint(parent::flip());
    }
}
