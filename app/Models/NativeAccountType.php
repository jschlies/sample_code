<?php

namespace App\Waypoint\Models;

use App;
use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Model;
use App\Waypoint\Models\Ledger\Ledger;
use App\Waypoint\Repositories\ClientRepository;
use Cache;
use App\Waypoint\Http\Controllers\Api\Ledger\LedgerController;

/**
 * Class NativeAccountType
 * @package App\Waypoint\Models
 */
class NativeAccountType extends NativeAccountTypeModelBase
{

    const NATIVE_ACCOUNT_TYPE_CONFIG_KEY = 'NATIVE_ACCOUNT_TYPES';

    const ALLOWED_LOCATION_VALUES = [
        Ledger::ANALYTICS_CONFIG_KEY,
        App\Waypoint\Models\NativeAccountType::NATIVE_ACCOUNT_TYPE_CONFIG_KEY,
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
    ];

    /**
     * AccessList constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @param integer $client_id
     * @param $isAnalytics
     * @return mixed
     * @throws GeneralException
     */
    public function getUltimateParentForReportTemplateAccountGroup($client_id, $isAnalytics = null)
    {
        /** @var Client $ClientObj */
        if ( ! $ClientObj = App::make(ClientRepository::class)->find($client_id))
        {
            throw new GeneralException('client missing');
        }

        // TODO (Alex) [283y98dsf] - remove this condition if/when we start using the top of the expenses account tree
        if ($isAnalytics)
        {
            /** @var App\Waypoint\Repositories\UserRepository $UserRepoObj */
            $UserRepoObj           = App::make(App\Waypoint\Repositories\UserRepository::class);
            $DefaultReportTemplate = $UserRepoObj->getDefaultAnalyticsReportTemplate();

            if (
                str_contains($DefaultReportTemplate->report_template_name, 'BOMA')
                &&
                stri_equal($this->native_account_type_name, LedgerController::NATIVE_ACCOUNT_TYPE_EXPENSES_TEXT)
            )
            {
                return $DefaultReportTemplate
                    ->reportTemplateAccountGroups
                    ->where('report_template_account_group_code', '=', '40_000_h2')
                    ->where('native_account_type_id', '=', $this->id)
                    ->where('report_template_id', '=', $DefaultReportTemplate->id)
                    ->first();
            }

            return $DefaultReportTemplate
                ->reportTemplateAccountGroups
                ->where('parent_report_template_account_group_id', '=', null)
                ->where('native_account_type_id', '=', $this->id)
                ->where('report_template_id', '=', $DefaultReportTemplate->id)
                ->first();
        }

        $DefaultReportTemplate = $ClientObj->defaultAdvancedVarianceReportTemplate;
        return $DefaultReportTemplate
            ->reportTemplateAccountGroups
            ->where('parent_report_template_account_group_id', '=', null)
            ->where('native_account_type_id', '=', $this->id)
            ->where('report_template_id', '=', $DefaultReportTemplate->id)
            ->first();
    }

    /**
     * use this if naming standard of outbound JSON differs from  names of this model
     * if you want to return based solely on $this->attributes, just return parent::toArray();
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            "id"                              => $this->id,
            "native_coa_id"                   => $this->native_coa_id,
            "client_id"                       => $this->client_id,
            "native_account_type_name"        => $this->native_account_type_name,
            "native_account_type_description" => $this->native_account_type_description,
            "display_name"                    => $this->display_name,

            "created_at" => $this->perhaps_format_date($this->created_at),
            "updated_at" => $this->perhaps_format_date($this->updated_at),

            "model_name" => self::class,
        ];
    }

    /**
     * @param integer $property_id
     * @return NativeAccountTypeTrailer
     */
    public function getCoeffients($property_id): NativeAccountTypeTrailer
    {
        $PropertyObj                 = Property::find($property_id);
        $minutes                     = config('cache.cache_on', false)
            ? config('cache.cache_tags.AdvancedVariance.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
            :
            0;
        $key                         = "NativeAccountType_getCoeffients_native_account_type_id=" . $this->id . '_property_id_' . $property_id . '_' . md5(__FILE__ . __LINE__);
        $NativeAccountTypeTrailerObj =
            Cache::tags([
                            'AdvancedVariance_' . $PropertyObj->client_id,
                        ])
                 ->remember(
                     $key,
                     $minutes,
                     function () use ($property_id)
                     {
                         if ($NativeAccountTypeTrailerObj = $this->nativeAccountTypeTrailers
                             ->where('property_id', $property_id)
                             ->first()
                         )
                         {
                             return $NativeAccountTypeTrailerObj;
                         }

                         if ($NativeAccountTypeTrailerObj = $this->nativeAccountTypeTrailers
                             ->first()
                         )
                         {
                             return $NativeAccountTypeTrailerObj;
                         }
                         throw new GeneralException('invalid getCoeffients in NativeAccountType');
                     }
                 );

        return $NativeAccountTypeTrailerObj;

    }
}
