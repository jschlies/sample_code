<?php

namespace App\Waypoint\Tests\Repository;

use App;
use App\Waypoint\Models\AdvancedVariance;
use App\Waypoint\Models\AdvancedVarianceLineItem;
use App\Waypoint\Models\NativeAccount;
use App\Waypoint\Models\Property;
use App\Waypoint\Tests\ApiTestTrait;
use App\Waypoint\Tests\Generated\MakeAccessListPropertyTrait;
use App\Waypoint\Tests\Generated\MakeAccessListTrait;
use App\Waypoint\Tests\Generated\MakeAccessListUserTrait;
use App\Waypoint\Tests\Generated\MakeAdvancedVarianceTrait;
use App\Waypoint\Tests\Generated\MakeClientTrait;
use App\Waypoint\Tests\Generated\MakePropertyGroupPropertyTrait;
use App\Waypoint\Tests\Generated\MakePropertyGroupTrait;
use App\Waypoint\Tests\Generated\MakePropertyTrait;
use App\Waypoint\Tests\Generated\MakeUserTrait;
use App\Waypoint\Tests\TestCase;

/**
 * Class AdvancedVarianceThresholdRepositoryTest
 * @package App\Waypoint\Tests
 * @codeCoverageIgnore
 */
class AdvancedVarianceThresholdRepositoryTest extends TestCase
{
    use MakeAccessListTrait, ApiTestTrait;
    use MakePropertyTrait;
    use MakeUserTrait;
    use MakeAccessListUserTrait;
    use MakeAccessListPropertyTrait;
    use MakePropertyGroupTrait;
    use MakePropertyGroupPropertyTrait;
    use MakeClientTrait;
    use MakeAdvancedVarianceTrait;

    public function setUp()
    {
        parent::setUp();
        $this->ClientObj->updateConfig('ADVANCED_VARIANCE_REVIEWERS', $this->ClientObj->users->getArrayOfGivenFieldValues('email'));
    }

    /**
     * @test
     */
    public function it_can_check_advanced_variance_the_good_the_bad_and_the_ugly()
    {
        /** @var AdvancedVariance $AdvancedVarianceObj */
        $AdvancedVarianceObj = $this->ClientObj->properties->map(
            function (Property $PropertyObj)
            {
                return $PropertyObj->advancedVariances;
            }
        )->flatten()->first();

        /** @var AdvancedVarianceLineItem $NativeAccountAdvancedVarianceLineItemObj */
        $NativeAccountAdvancedVarianceLineItemObj = $AdvancedVarianceObj->advancedVarianceLineItems->filter(
            function (AdvancedVarianceLineItem $AdvancedVarianceLineItemObj)
            {
                return $AdvancedVarianceLineItemObj->nativeAccount;
            }
        )->first();

        /** @var NativeAccount $NativeAccountObj */
        $NativeAccountObj            = $NativeAccountAdvancedVarianceLineItemObj->nativeAccount;
        $NativeAccountTypeTrailerObj = $NativeAccountObj->getCoeffients($AdvancedVarianceObj->property_id);

        /*
         * answer key based on a threshold of
         * native_account_overage_threshold_amount = 5000
         * native_account_overage_threshold_amount_too_good = 5000
         * native_account_overage_threshold_percent = 15%
         * native_account_overage_threshold_percent_too_good = %15
         */

        $test_data_arr = $this->get_test_values_and_answers();

        $test_recorder_arr = [];

        foreach ($test_data_arr as $test_data)
        {
            $NativeAccountAdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->update(
                [
                    'native_account_overage_threshold_amount'           => $test_data['native_account_overage_threshold_amount'],
                    'native_account_overage_threshold_amount_too_good'  => $test_data['native_account_overage_threshold_amount_too_good'],
                    'native_account_overage_threshold_percent'          => $test_data['native_account_overage_threshold_percent'],
                    'native_account_overage_threshold_percent_too_good' => $test_data['native_account_overage_threshold_percent_too_good'],
                ],
                $NativeAccountAdvancedVarianceLineItemObj->id
            );
            foreach ($test_data['data'] as $dollar_values_and_answers_data)
            {
                foreach (
                    [
                        AdvancedVariance::OVERAGE_THRESHOLD_OPERATOR_OR,
                        AdvancedVariance::OVERAGE_THRESHOLD_OPERATOR_AND,
                    ] as $overage_threshold_operator)
                {
                    /**
                     * 0 = revenue
                     * 1 = expense
                     */
                    foreach ([
                                 0,
                                 1,
                             ] as $advanced_variance_coefficient)
                    {
                        /** I'm not sure I need to do this but...... */
                        $NativeAccountTypeTrailerObj         = $this->NativeAccountTypeTrailerRepositoryObj->update(
                            [
                                'advanced_variance_coefficient'             => $advanced_variance_coefficient,
                                'native_account_overage_threshold_operator' => $overage_threshold_operator,
                            ],
                            $NativeAccountTypeTrailerObj->id
                        );
                        $saved_ative_account_type_trailer_id = $NativeAccountTypeTrailerObj->id;
                        $NativeAccountTypeTrailerObj         =
                            $NativeAccountAdvancedVarianceLineItemObj
                                ->nativeAccount
                                ->getCoeffients($NativeAccountAdvancedVarianceLineItemObj->advancedVariance->property_id);

                        $this->assertEquals($saved_ative_account_type_trailer_id, $NativeAccountTypeTrailerObj->id);

                        /**
                         * let's update the native_account_overage_threshold_operator for this loop
                         */
                        $NativeAccountAdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->update(
                            [
                                'native_account_overage_threshold_operator' => $overage_threshold_operator,
                            ],
                            $NativeAccountAdvancedVarianceLineItemObj->id
                        );

                        /**
                         * rather that worry about what kind of AdvancedVariance this is......
                         */
                        $NativeAccountAdvancedVarianceLineItemObj = $this->AdvancedVarianceLineItemRepositoryObj->update(
                            [
                                'monthly_budgeted'  => $dollar_values_and_answers_data['budgeted'],
                                'monthly_actual'    => $dollar_values_and_answers_data['actual'],
                                'ytd_budgeted'      => $dollar_values_and_answers_data['budgeted'],
                                'ytd_actual'        => $dollar_values_and_answers_data['actual'],
                                'qtd_budgeted'      => $dollar_values_and_answers_data['budgeted'],
                                'qtd_actual'        => $dollar_values_and_answers_data['actual'],
                                'forecast_budgeted' => $dollar_values_and_answers_data['budgeted'],
                                'forecast_actual'   => $dollar_values_and_answers_data['actual'],
                            ],
                            $NativeAccountAdvancedVarianceLineItemObj->id
                        );

                        $test_recorder_arr[] = [
                            'budgeted'                      => $dollar_values_and_answers_data['budgeted'] ?: 'zero',
                            'actual'                        => $dollar_values_and_answers_data['actual'] ?: 'zero',
                            'advanced_variance_coefficient' => ($advanced_variance_coefficient ? 'expenses' : 'revenue') ?: 'zero',
                            'overage_threshold_operator'    => $overage_threshold_operator,

                            'native_account_overage_threshold_amount'           => $NativeAccountAdvancedVarianceLineItemObj->native_account_overage_threshold_amount ?: 'zero',
                            'native_account_overage_threshold_amount_too_good'  => $NativeAccountAdvancedVarianceLineItemObj->native_account_overage_threshold_amount_too_good ?: 'zero',
                            'native_account_overage_threshold_percent'          => $NativeAccountAdvancedVarianceLineItemObj->native_account_overage_threshold_percent ?: 'zero',
                            'native_account_overage_threshold_percent_too_good' => $NativeAccountAdvancedVarianceLineItemObj->native_account_overage_threshold_percent_too_good ?: 'zero',

                            'flagged_via_policy_expected' => $dollar_values_and_answers_data['answers'][$overage_threshold_operator][($advanced_variance_coefficient ? 'expenses' : 'revenue')] ? 'FLAGGED' : '',
                            'flagged_via_policy'          => $NativeAccountAdvancedVarianceLineItemObj->flagged_via_policy ? 'FLAGGED' : '',
                        ];

                        $this->assertEquals(
                            $dollar_values_and_answers_data['answers'][$overage_threshold_operator][($advanced_variance_coefficient ? 'expenses' : 'revenue')],
                            $NativeAccountAdvancedVarianceLineItemObj->flagged_via_policy,
                            'test failed =' . PHP_EOL .
                            'flagged_via_policy =' . $NativeAccountAdvancedVarianceLineItemObj->flagged_via_policy . PHP_EOL .
                            'overage_threshold_operator =' . $overage_threshold_operator . PHP_EOL .
                            'advanced_variance_coefficient =' . ($advanced_variance_coefficient ? 'expenses' : 'revenue') . PHP_EOL .
                            'budgeted =' . $dollar_values_and_answers_data['budgeted'] . PHP_EOL .
                            'actual =' . $dollar_values_and_answers_data['actual'] . PHP_EOL .

                            'native_account_overage_threshold_amount =' . $NativeAccountAdvancedVarianceLineItemObj->native_account_overage_threshold_amount . PHP_EOL .
                            'native_account_overage_threshold_amount_too_good =' . $NativeAccountAdvancedVarianceLineItemObj->native_account_overage_threshold_amount_too_good . PHP_EOL .
                            'native_account_overage_threshold_percent =' . $NativeAccountAdvancedVarianceLineItemObj->native_account_overage_threshold_percent . PHP_EOL .
                            'native_account_overage_threshold_percent_too_good =' . $NativeAccountAdvancedVarianceLineItemObj->native_account_overage_threshold_percent_too_good . PHP_EOL
                        );
                    }
                }
            }
        }

        ///** @var LaravelExcelWriter $LaravelExcelWriterObj */
        //$LaravelExcelWriterObj = Excel::create(
        //    'unit_test_manifest.xls',
        //    function ($excel) use ($test_recorder_arr)
        //    {
        //        $excel->sheet(
        //            'ECM Projects',
        //            function (LaravelExcelWorksheet $sheet) use ($test_recorder_arr)
        //            {
        //                $sheet->fromArray($test_recorder_arr, 0, 'A1', true, true);
        //            }
        //        );
        //    }
        //);
        //$LaravelExcelWriterObj->save('xls');
    }

    public function get_test_values_and_answers()
    {
        /**
         * budgeted => budgeted amt
         * actual => actual amt
         * answers [$overage_threshold_operator][$advanced_variance_coefficient] = is flagged or not flagged
         *
         */
        return [
            [
                'native_account_overage_threshold_amount'           => 5000,
                'native_account_overage_threshold_amount_too_good'  => 5000,
                'native_account_overage_threshold_percent'          => 15,
                'native_account_overage_threshold_percent_too_good' => 15,
                'data'                                              => [
                    [
                        /** exactly on budget */
                        'budgeted' => 0,
                        'actual'   => 0,
                        'answers'  => [
                            'or'  =>
                                [
                                    'revenue'  => 0,
                                    'expenses' => 0,
                                ],
                            'and' =>
                                [
                                    'revenue'  => 0,
                                    'expenses' => 0,
                                ],
                        ],
                    ],
                    /** 100 over/under budget */
                    [
                        'budgeted' => 0,
                        'actual'   => 100,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    /** 1000 over/under budget */
                    [
                        'budgeted' => 0,
                        'actual'   => 1000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 0,
                        'actual'   => 10000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 100,
                        'actual'   => 0,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 100,
                        'actual'   => 100,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 1000,
                        'actual'   => 100,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 10000,
                        'actual'   => 100,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 100,
                        'actual'   => 10000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 100,
                        'actual'   => 10000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 1000,
                        'actual'   => 1000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 10000,
                        'actual'   => 1000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'native_account_overage_threshold_amount'           => 5000,
                'native_account_overage_threshold_amount_too_good'  => 0,
                'native_account_overage_threshold_percent'          => 15,
                'native_account_overage_threshold_percent_too_good' => 0,
                'data'                                              => [
                    [
                        /** exactly on budget */
                        'budgeted' => 0,
                        'actual'   => 0,
                        'answers'  => [
                            'or'  =>
                                [
                                    'revenue'  => 0,
                                    'expenses' => 0,
                                ],
                            'and' =>
                                [
                                    'revenue'  => 0,
                                    'expenses' => 0,
                                ],
                        ],
                    ],
                    /** 100 over/under budget */
                    [
                        'budgeted' => 0,
                        'actual'   => 100,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    /** 1000 over/under budget */
                    [
                        'budgeted' => 0,
                        'actual'   => 1000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 0,
                        'actual'   => 10000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 1,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 100,
                        'actual'   => 0,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 100,
                        'actual'   => 100,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 1000,
                        'actual'   => 100,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 10000,
                        'actual'   => 100,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 1,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 100,
                        'actual'   => 10000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 1,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 1000,
                        'actual'   => 1000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 10000,
                        'actual'   => 1000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 1,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'native_account_overage_threshold_amount'           => 5000,
                'native_account_overage_threshold_amount_too_good'  => 5000,
                'native_account_overage_threshold_percent'          => 1,
                'native_account_overage_threshold_percent_too_good' => 1,
                'data'                                              => [
                    [
                        /** exactly on budget */
                        'budgeted' => 0,
                        'actual'   => 0,
                        'answers'  => [
                            'or'  =>
                                [
                                    'revenue'  => 0,
                                    'expenses' => 0,
                                ],
                            'and' =>
                                [
                                    'revenue'  => 0,
                                    'expenses' => 0,
                                ],
                        ],
                    ],
                    /** 100 over/under budget */
                    [
                        'budgeted' => 0,
                        'actual'   => 100,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    /** 1000 over/under budget */
                    [
                        'budgeted' => 0,
                        'actual'   => 1000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 0,
                        'actual'   => 10000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 100,
                        'actual'   => 0,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 100,
                        'actual'   => 100,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 1000,
                        'actual'   => 100,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 10000,
                        'actual'   => 100,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 100,
                        'actual'   => 10000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 1000,
                        'actual'   => 1000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                            'and' => [
                                'revenue'  => 0,
                                'expenses' => 0,
                            ],
                        ],
                    ],
                    [
                        'budgeted' => 10000,
                        'actual'   => 1000,
                        'answers'  => [
                            'or'  => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                            'and' => [
                                'revenue'  => 1,
                                'expenses' => 1,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * See http://stackoverflow.com/questions/13537545/clear-memory-being-used-by-php
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
}
