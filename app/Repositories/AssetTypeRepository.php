<?php

namespace App\Waypoint\Repositories;

use App;
use App\Waypoint\Models\AssetType;
use Illuminate\Container\Container as Application;

/**
 * Class PropertyRepository
 */
class AssetTypeRepository extends AssetTypeRepositoryBase
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
        return AssetType::class;
    }

}
