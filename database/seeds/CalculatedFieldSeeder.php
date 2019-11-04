<?php

use App\Waypoint\Models\CalculatedField;
use App\Waypoint\Models\ReportTemplateAccountGroup;
use App\Waypoint\Repositories\CalculatedFieldRepository;
use App\Waypoint\Seeder;

/**
 * Class CalculatedFieldSeeder
 */
class CalculatedFieldSeeder extends Seeder
{
    /**
     * CalculatedFieldSeeder constructor.
     * @param array $seeder_provided_attributes_arr
     * @param int $count
     * @param string $factory_name
     * @throws \Exception
     */
    public function __construct($seeder_provided_attributes_arr = [], $count = 1, $factory_name = self::DEFAULT_FACTORY_NAME)
    {
        parent::__construct($seeder_provided_attributes_arr, $count, $factory_name);
        $this->setResultingClass(CalculatedField::class);
        $this->ModelRepositoryObj = App::make(CalculatedFieldRepository::class)->setSuppressEvents(true);
    }

    /**
     * @return \App\Waypoint\Collection
     */
    public function run()
    {
        /** @var [] $CalculatedFieldObjArr */
        $CalculatedFieldObjArr = parent::run();
        /** @var CalculatedField $CalculatedFieldObj */
        foreach ($CalculatedFieldObjArr as $CalculatedFieldObj)
        {
            /**
             * at this point $ClientObj has 2 reportTemplates, BomaBasedReportTemplate and AccountTypeBasedReportTemplate
             *
             * let's add some CalculatedFieldS to $AccountTypeBasedReportTemplateObj
             */
            if ($CalculatedFieldObj->reportTemplate->is_default_advance_variance_report_template)
            {
                /** @var ReportTemplateAccountGroup $ReportTemplateAccountGroupObj */
                foreach ($CalculatedFieldObj->reportTemplate->reportTemplateAccountGroups as $ReportTemplateAccountGroupObj)
                {
                    if ($SampleReportTemplateMappingObj = $ReportTemplateAccountGroupObj->reportTemplateMappings->first())
                    {
                        break;
                    }
                }
            }

            $this->CalculatedFieldEquationRepositoryObj->create(
                [
                    'calculated_field_id' => $CalculatedFieldObj->id,
                    'equation_string'     => '[NA_' . $SampleReportTemplateMappingObj->native_account_id . '] + 1000 * [RTAG_' . $SampleReportTemplateMappingObj->report_template_account_group_id . '] + ' . mt_rand(),
                ]
            );
        }
        return $CalculatedFieldObjArr;
    }
}