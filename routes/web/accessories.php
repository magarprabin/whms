<?php

/*
* Accessories
 */
Route::group([ 'prefix' => 'accessories', 'middleware' => ['auth']], function () {

    Route::get(
        '{accessoryID}/checkout',
        [ 'as' => 'checkout/accessory', 'uses' => 'Accessories\AccessoryCheckoutController@create' ]
    );
    Route::post(
        '{accessoryID}/checkout',
        [ 'as' => 'checkout/accessory', 'uses' => 'Accessories\AccessoryCheckoutController@store' ]
    );
    Route::get(
        '{accessoryID}/printlabel',
        [ 'as' => 'accessories/printlabel', 'uses' => 'Accessories\BulkAccessoriesController@printlabel' ]
    );
    Route::post(
        '{accessoryID}/generatelabel',
        [ 'as' => 'generatelabel/accessory', 'uses' => 'Accessories\AccessoryCheckoutController@generatelabel' ]
    );
    Route::get(
        '{accessoryID}/generatelabel',
        [ 'as' => 'accessories/generatelabel', 'uses' => 'Accessories\BulkAccessoriesController@generateqr' ]
    );
    Route::get(
        '{accessoryID}/generateqr',
        [ 'as' => 'generateqr/accessory', 'uses' => 'Accessories\BulkAccessoriesController@generateqr' ]
    );
    Route::get(
        '{accessoryID}/checkin/{backto?}',
        [ 'as' => 'checkin/accessory', 'uses' => 'Accessories\AccessoryCheckinController@create' ]
    );
    Route::post(
        '{accessoryID}/checkin/{backto?}',
        [ 'as' => 'checkin/accessory', 'uses' => 'Accessories\AccessoryCheckinController@store' ]
    );
    Route::get(
        'scan',
        [ 'as' => 'scan/accessory', 'uses' => 'Accessories\AccessoryCheckoutController@scan' ]
    );
    Route::post(
        'scan',
        [ 'as' => 'scan/accessory', 'uses' => 'Accessories\AccessoryCheckoutController@savescan' ]
    );
    Route::get(
        'scanall',
        [ 'as' => 'scanall/accessory', 'uses' => 'Accessories\AccessoryCheckoutController@scanall' ]
    );
    Route::post(
        'scanall',
        [ 'as' => 'scanall/accessory', 'uses' => 'Accessories\AccessoryCheckoutController@savescanall' ]
    );
    Route::post(
        'numberdata',
        [ 'as' => 'numberdata/accessory', 'uses' => 'Accessories\AccessoryCheckoutController@showInventoryData' ]
    );
    Route::post(
        'inventorydata',
        [ 'as' => 'numberdata/inventorydata', 'uses' => 'Accessories\AccessoryCheckoutController@showInventoryDataByTag' ]
    );
    Route::post(
        'bulkedit',
        [
            'as'   => 'accessories/bulkedit',
            'uses' => 'Accessories\BulkAccessoriesController@edit'
        ]
    );
    Route::post(
        'bulkgenerate',
        [
            'as'   => 'accessories/bulkgenerate',
            'uses' => 'Accessories\BulkAccessoriesController@generateMultipleLabel'
        ]
    );
});

Route::resource('accessories', 'Accessories\AccessoriesController', [
    'middleware' => ['auth'],
    'parameters' => ['accessory' => 'accessory_id']
]);
Route::group(
    ['prefix' => 'accessories',
    'middleware' => ['auth']],
    function () {
Route::get('{assetId}/qr_code', [ 'as' => 'qr_code/hardware', 'uses' => 'Accessories\AccessoriesController@getQrCode' ]);
Route::get('{assetId}/barcode', [ 'as' => 'barcode/hardware', 'uses' => 'Accessories\AccessoriesController@getBarCode' ]);
    });