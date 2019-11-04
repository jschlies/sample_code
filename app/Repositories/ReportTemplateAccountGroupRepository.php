<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\ReportTemplateAccountGroup;

/**
 * Class ReportTemplateAccountGroupRepository
 * @package App\Waypoint\Repositories
 */
class ReportTemplateAccountGroupRepository extends ReportTemplateAccountGroupRepositoryBase
{
    /**
     * @return string
     */
    public function model()
    {
        return ReportTemplateAccountGroup::class;
    }

    /**
     * @param $deprecated_account_code
     * @return mixed|null
     */
    public function findReportTemplateAccountGroupIdByDeprecatedAccountCode($deprecated_account_code)
    {
        $ReportTemplateAccountGroupObj = $this->findWhere(['deprecated_waypoint_code' => $deprecated_account_code])->first();
        return $ReportTemplateAccountGroupObj ? $ReportTemplateAccountGroupObj->id : null;
    }

    /**
     * @param $deprecated_account_code
     * @return mixed|null
     */
    public function findReportTemplateAccountGroupCodeByDeprecatedAccountCode($deprecated_account_code)
    {
        $ReportTemplateAccountGroupObj = $this->findWhere(['deprecated_waypoint_code' => $deprecated_account_code])->first();
        return $ReportTemplateAccountGroupObj ? $ReportTemplateAccountGroupObj->report_template_account_group_code : null;
    }

    /**
     * @param array $attributes
     * @return ReportTemplateAccountGroup
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function create(array $attributes)
    {
        if ( ! isset($attributes['report_template_account_group_code']) || ! $attributes['report_template_account_group_code'])
        {
            $attributes['report_template_account_group_code'] = uniqid();
        }
        return parent::create($attributes);
    }
}
