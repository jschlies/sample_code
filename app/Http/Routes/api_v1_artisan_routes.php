<?php

Route::get(
    '/flushAllCache',
    [
        'as'   => 'api.v1.ArtisanController.flushAllCache',
        'uses' => 'ArtisanController@flushAllCache',
    ]
);

Route::get(
    '/flushNonSessionCache',
    [
        'as'   => 'api.v1.ArtisanController.flushAllNonSessionCache',
        'uses' => 'ArtisanController@flushAllNonSessionCache',
    ]
);

Route::get(
    '/clients/{client_id}/triggerGroupCalc',
    [
        'as'   => 'api.v1.ArtisanController.triggerGroupCalc',
        'uses' => 'ArtisanController@triggerGroupCalc',
    ]
);

Route::get(
    '/clients/{client_id}/blockGroupCalc',
    [
        'as'   => 'api.v1.ArtisanController.blockGroupCalc',
        'uses' => 'ArtisanController@blockGroupCalc',
    ]
);

Route::get(
    '/clients/{client_id}/setFilterDefaultValue/dropdown_name/{dropdown_name}/dropdown_value/{dropdown_value}',
    [
        'as'   => 'api.v1.ArtisanController.setFilterDefaultValue',
        'uses' => 'ArtisanController@setFilterDefaultValue',
    ]
);

Route::get(
    '/clients/{client_id}/filter_alter/filter_name/{filter_name}/filter_options/{filter_options}',
    [
        'as'   => 'api.v1.ArtisanController.filter_alter',
        'uses' => 'ArtisanController@filter_alter',
    ]
);

Route::get(
    '/clients/{client_id}/setClientConfigValue/config_name/{config_name}/config_value/{config_value}',
    [
        'as'   => 'api.v1.ArtisanController.setClientConfigValue',
        'uses' => 'ArtisanController@setClientConfigValue',
    ]
);

Route::get(
    '/clients/{client_id}/refreshGeneratedListsAndGroups',
    [
        'as'   => 'api.v1.ArtisanController.refreshGeneratedListsAndGroups',
        'uses' => 'ArtisanController@refreshGeneratedListsAndGroups',
    ]
);