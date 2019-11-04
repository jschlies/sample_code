<?php

namespace App\Waypoint\Models\Ledger;

use App\Waypoint\Collection;

class NativeCoa extends Ledger
{

    public $fillable = [];

    protected $casts = [];

    public static $rules = [];

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array $models
     * @return Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }
}