<?php

namespace App\Waypoint\Notifications;

use App;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use Illuminate\Bus\Queueable;
use App\Waypoint\Repositories\Ledger\NativeCoaLedgerRepository;

class AdvancedVarianceNotificationBase extends Notification
{
    use Queueable;

    /** @var  AdvancedVariance */
    private $AdvancedVarianceObj;
    /** @var  AdvancedVarianceLineItem */
    private $AdvancedVarianceLineItemObj;

    /**
     * @return AdvancedVariance
     */
    public function getAdvancedVarianceObj(): AdvancedVariance
    {
        if(! $this->AdvancedVarianceObj)
        {
            return $this->getAdvancedVarianceLineItemObj()->advancedVariance;
        }
        return $this->AdvancedVarianceObj;
    }

    /**
     * @param AdvancedVariance $AdvancedVarianceObj
     */
    public function setAdvancedVarianceObj(AdvancedVariance $AdvancedVarianceObj)
    {
        $this->AdvancedVarianceObj = $AdvancedVarianceObj;
    }

    /**
     * @return AdvancedVarianceLineItem
     */
    public function getAdvancedVarianceLineItemObj(): AdvancedVarianceLineItem
    {
        if(! $this->AdvancedVarianceLineItemObj)
        {
            return $this->AdvancedVarianceObj->advancedVarianceLineItems->first();
        }
        return $this->AdvancedVarianceLineItemObj;
    }

    /**
     * @param AdvancedVarianceLineItem $AdvancedVarianceLineItemObj
     */
    public function setAdvancedVarianceLineItemObj(AdvancedVarianceLineItem $AdvancedVarianceLineItemObj)
    {
        $this->AdvancedVarianceLineItemObj = $AdvancedVarianceLineItemObj;
    }

    /**
     * @return string
     */
    protected function get_period_text()
    {
        return $this->getAdvancedVarianceObj()->period_type == AdvancedVariance::PERIOD_TYPE_MONTHLY
            ?
            $this->getAdvancedVarianceObj()->as_of_month . '/' . $this->getAdvancedVarianceObj()->as_of_year
            :
            'Q' . NativeCoaLedgerRepository::MONTHS_QUARTERS_LOOKUP[$this->getAdvancedVarianceObj()->as_of_month] . ' ' . $this->getAdvancedVarianceObj()->as_of_year;
    }

    /**
     * @return string
     */
    protected function get_period_text_for_advanced_variance_line_item()
    {
        return $this->getAdvancedVarianceLineItemObj()->advancedVariance->period_type == AdvancedVariance::PERIOD_TYPE_MONTHLY
            ?
            $this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_month .
            '/' . $this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_year
            :
            'Q' . NativeCoaLedgerRepository::MONTHS_QUARTERS_LOOKUP[$this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_month] . ' ' .
            $this->getAdvancedVarianceLineItemObj()->advancedVariance->as_of_year;
    }

    /**
     * @return mixed
     */
    protected function get_account_name_from_advanced_variance_line_item()
    {
        return $this->getAdvancedVarianceLineItemObj()->line_item_name;
    }
}
