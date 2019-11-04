<?php

namespace App\Waypoint\Models;

use App;

/**
 * Class AdvancedVarianceDetail
 * @package App\Waypoint\Models
 */
class AdvancedVarianceWorkflow extends AdvancedVariance
{
    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     */
    public function toArray(): array
    {
        /** @var [] $related_user_id_arr */
        $related_user_id_arr = App::make(App\Waypoint\Repositories\RelatedUserRepository::class)->getReviewerIdArr($this->id);

        return [
            "id"                       => $this->id,
            "advanced_variance_status" => $this->advanced_variance_status,

            "locker_user_id"     => $this->locker_user_id,
            "locked_date"        => $this->perhaps_format_date($this->locked_date),
            "target_locked_date" => $this->perhaps_format_date($this->target_locked_date),
            "lockerUser"         => $this->lockerUser ? $this->lockerUser->toArray() : [],

            "num_flagged_via_policy" => $this->num_flagged_via_policy,
            'num_flagged_manually'   => $this->num_flagged_manually,
            'num_flagged'            => $this->num_flagged,
            'num_explained'          => $this->num_explained,
            'num_line_items'         => $this->num_line_items,
            'num_resolved'           => $this->num_resolved,
            'num_approved'           => $this->advancedVarianceApprovals->count(),

            "advancedVarianceApprovals" => $this->advancedVarianceApprovals->toArray(),

            'reviewer_ids' => $related_user_id_arr,

            'model_name' => self::class,
        ];
    }
}
