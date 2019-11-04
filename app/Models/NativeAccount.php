<?php

namespace App\Waypoint\Models;

use App\Waypoint\Exceptions\GeneralException;
use App\Waypoint\Model;
use Cache;

/**
 * Class NativeAccount
 * @package App\Waypoint\Models
 */
class NativeAccount extends NativeAccountModelBase
{
    const NATIVE_ACCOUNT_TYPE_REVENUE     = 'Revenue';
    const NATIVE_ACCOUNT_TYPE_EXPENSES    = 'Expenses';
    const NATIVE_ACCOUNT_TYPE_ASSETS      = 'Assets';
    const NATIVE_ACCOUNT_TYPE_LIABILITIES = 'Liabilities';
    const NATIVE_ACCOUNT_TYPE_EQUITY      = 'Equity';
    const NATIVE_ACCOUNT_TYPE_DEFAULT     = self::NATIVE_ACCOUNT_TYPE_EXPENSES;
    public static $native_coa_type_arr = [
        self::NATIVE_ACCOUNT_TYPE_REVENUE,
        self::NATIVE_ACCOUNT_TYPE_EXPENSES,
        self::NATIVE_ACCOUNT_TYPE_ASSETS,
        self::NATIVE_ACCOUNT_TYPE_LIABILITIES,
        self::NATIVE_ACCOUNT_TYPE_EQUITY,
    ];
    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'native_coa_id'            => 'required|integer',
        'native_account_type_id'   => 'required|integer',
        'parent_native_account_id' => 'nullable|integer',
    ];

    /**
     * NativeAccount constructor.
     * @param array $attributes
     * @throws GeneralException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
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
            "id"                       => $this->id,
            "native_coa_name"          => $this->nativeCoa ? $this->nativeCoa->name : null,
            "native_account_name"      => $this->native_account_name,
            "native_account_code"      => $this->native_account_code,
            "native_account_type_id"   => $this->native_account_type_id,
            "parent_native_account_id" => $this->parent_native_account_id,
            "is_category"              => $this->is_category,
            "is_recoverable"           => $this->is_recoverable,
            "native_account_type"      => $this->nativeAccountType ? $this->nativeAccountType->native_account_type_name : null,
            "native_coa_id"            => $this->native_coa_id,

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
        $minutes                     = config('cache.cache_on', false)
            ? config('cache.cache_tags.AdvancedVariance.ttl', Model::CACHE_TAG_DEFAULT_TTL) / 60
            :
            0;
        $key = "NativeAccount_getCoeffients_native_account_id=" . $this->id . "_property_id=" . $property_id.'_'.md5(__FILE__.__LINE__);
        $NativeAccountTypeTrailerObj =
            Cache::tags([
                            'AdvancedVariance_' . $this->nativeCoa->client_id,
                            'Non-Session',
                        ])
                 ->remember(
                     $key,
                     $minutes,
                     function () use ($property_id)
                     {
                         if ($NativeAccountTypeTrailerObj = $this->nativeAccountType->nativeAccountTypeTrailers
                             ->where('property_id', $property_id)
                             ->where('native_coa_id', $this->native_coa_id)
                             ->first()
                         )
                         {
                             return $NativeAccountTypeTrailerObj;
                         }
                         elseif ($NativeAccountTypeTrailerObj = $this->nativeAccountType->nativeAccountTypeTrailers
                             ->where('native_coa_id', $this->native_coa_id)
                             ->first()
                         )
                         {
                             return $NativeAccountTypeTrailerObj;
                         }
                         throw new GeneralException('invalid getCoeffients');
                     }
                 );

        return $NativeAccountTypeTrailerObj;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function nativeAccountTypeDetail()
    {
        return $this->belongsTo(
            NativeAccountTypeDetail::class,
            'native_account_type_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function nativeAccountTypeDetailSummary()
    {
        return $this->belongsTo(
            NativeAccountTypeSummary::class,
            'native_account_type_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     **/
    public function nativeCoaFull()
    {
        return $this->belongsTo(
            NativeCoaFull::class,
            'native_coa_id',
            'id'
        );
    }
}
