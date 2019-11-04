<?php

Route::group(
    [
        'namespace' => 'Api',
    ],
    function ()
    {
        /**
         * AdvancedVariance
         */
        Route::post(
            '/clients/{client_id}/properties/{property_id}/advancedVariances',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailApiKeyController.store',
                'uses' => 'AdvancedVarianceDetailApiKeyController@store',
            ]
        );
    }
);

Route::group(
    [
        'namespace' => 'Api',
    ],
    function ()
    {
        /**
         * AdvancedVariance
         */
        Route::post(
            '/clients/{client_id}/properties/{property_id}/advancedVariances',
            [
                'as'   => 'api.v1.AdvancedVarianceDetailApiKeyController.store',
                'uses' => 'AdvancedVarianceDetailApiKeyController@store',
            ]
        );
    }
);