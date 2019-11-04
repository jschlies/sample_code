<?php

namespace App\Waypoint\Repositories;

use App\Waypoint\Models\NativeAccountTypeSummary;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Models\ReportTemplate;
use App\Waypoint\Models\ReportTemplateAccountGroup;

/**
 * Class NativeAccountTypeDetailRepository
 * @package App\Waypoint\Repositories
 */
class NativeAccountTypeSummaryRepository extends NativeAccountTypeRepository
{
    /**
     * @param $report_template_id
     * @return \App\Waypoint\Collection
     */
    public function getForReportTemplate($report_template_id)
    {
        $ReportTemplateAccountGroupRepositoryObj = \App::make(ReportTemplateAccountGroupRepository::class);

        if ( ! \App::make(ReportTemplate::class)->find($report_template_id))
        {
            throw new GeneralException('could not find report template from the id given');
        }

        /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroups */
        $ReportTemplateAccountGroups
            = $ReportTemplateAccountGroupRepositoryObj
            ->findWhere(
                [
                    'report_template_id'                      => $report_template_id,
                    'parent_report_template_account_group_id' => null,
                ]
            )
            ->unique('native_account_type_id');

        /** @var NativeAccountTypeSummary $NativeAccountTypeSummaries */
        $NativeAccountTypeSummaries
            = $this->findWhereIn('id', $ReportTemplateAccountGroups->pluck('native_account_type_id')->toArray());

        $return_me = collect_waypoint();
        foreach ($NativeAccountTypeSummaries as $NativeAccountTypeSummary)
        {
            $NativeAccountTypeSummary->report_template_account_group_id =
                $ReportTemplateAccountGroups
                    ->first(
                        function ($item) use ($NativeAccountTypeSummary)
                        {
                            return $item->native_account_type_id == $NativeAccountTypeSummary->id;
                        }
                    )
                    ->id;

            $return_me[] = $NativeAccountTypeSummary->toArrayWithAdditionalAttributes();
        }

        return $return_me;
    }

    /**
     * @return string
     */
    public function model()
    {
        return NativeAccountTypeSummary::class;
    }
}
