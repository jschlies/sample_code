<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\NativeAccountAmount;
use App\Waypoint\Repository as BaseRepository;
use Illuminate\Container\Container as Application;

/**
 * Class PropertyRepository
 */
class NativeChartAmountRepository extends BaseRepository
{
    public function __construct(Application $app)
    {
        parent::__construct($app);
    }

    /**
     * @return string
     */
    public function model()
    {
        return NativeAccountAmount::class;
    }

}
