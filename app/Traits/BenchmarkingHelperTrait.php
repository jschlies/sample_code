<?php

namespace App\Waypoint\Traits;

use App\Waypoint\Exceptions\LedgerException;

trait BenchmarkingHelperTrait
{

    /**
     * @return bool
     *
     * This checks to see if a report template is of the BOMA flavor
     */
    function isBomaReportTemplate()
    {
        if (is_null($this->ReportTemplateObj->is_boma_report_template))
        {
            throw new LedgerException('your report template is not present and cannot emit this attribute');
        }
        return (bool) $this->ReportTemplateObj->is_boma_report_template;
    }

    /**
     * @return bool
     */
    protected function accountGroupMatchesUserDefaultReportTemplate(): bool
    {
        if (empty($this->ReportTemplateObj) || empty($this->ClientObj) || empty($this->ReportTemplateAccountGroupObj))
        {
            throw new LedgerException('missing critical dependencies: report template / account group / client object');
        }

        // gather default report template for this user (using the client object for now)
        // TODO (Alex) - check the user object for the default analytics report template
        $this->ReportTemplateObj = $this->ClientObj->getDefaultAnalyticsReportTemplate();

        return $this->ReportTemplateObj->id === $this->ReportTemplateAccountGroupObj->reportTemplate->id;
    }

    /**
     * Basically:
     *      - client obj
     *      - relevant report template
     *      - table field with which to fetch approapriate ledger data
     */
    public function initializeEssentialIngredients()
    {
        $this->initializeClientObject();

        $this->ReportTemplateObj = $this->ClientObj->getDefaultAnalyticsReportTemplate();

        if ($this->isBomaReportTemplate())
        {
            $this->report_template_account_group_code_field_name = 'deprecated_waypoint_code';
        }

        // basic input checks
        if ( ! $this->accountGroupMatchesUserDefaultReportTemplate())
        {
            throw new LedgerException('this account group does not match the default report template set for this user');
        }
    }

    /**
     * @param array $array
     * @param $repo
     */
    public function initInputForCombinedSpreadsheets(array $array, $repo)
    {
        foreach ($array as $key => $val)
        {
            $this->{$key} = $val;
            $repo->{$key} = $val;
        }
    }

}
