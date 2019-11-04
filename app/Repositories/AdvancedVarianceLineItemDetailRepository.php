<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\AdvancedVarianceLineItemDetail;

/**
 * Class AdvancedVarianceLineItemRepository
 */
class AdvancedVarianceLineItemDetailRepository extends AdvancedVarianceLineItemRepository
{
    /**
     * @return string
     */
    public function model()
    {
        return AdvancedVarianceLineItemDetail::class;
    }
}
