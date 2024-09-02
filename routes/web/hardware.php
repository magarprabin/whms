<?php
/*
|--------------------------------------------------------------------------
| Asset Routes
|--------------------------------------------------------------------------
|
| Register all the asset routes.
|
*/
Route::group(
    ['prefix' => 'hardware',
    'middleware' => ['auth']],
    function () {

        Route::get( 'bulkaudit',  [
            'as' => 'assets.bulkaudit',
            'uses' => 'Assets\AssetsController@quickScan'
        ]);

        # Asset Maintenances
        Route::resource('maintenances', 'AssetMaintenancesController', [
            'parameters' => ['maintenance' => 'maintenance_id', 'asset' => 'asset_id']
        ]);

        Route::get('requested', [ 'as' => 'assets.requested', 'uses' => 'Assets\AssetsController@getRequestedIndex']);

        Route::get('scan', [
            'as' => 'asset.scan',
            'uses' => 'Assets\AssetsController@scan'
        ]);

        Route::get('audit/due', [
            'as' => 'assets.audit.due',
            'uses' => 'Assets\AssetsController@dueForAudit'
        ]);

        Route::get('audit/overdue', [
            'as' => 'assets.audit.overdue',
            'uses' => 'Assets\AssetsController@overdueForAudit'
        ]);

        Route::get('audit/due', [
            'as' => 'assets.audit.due',
            'uses' => 'Assets\AssetsController@dueForAudit'
        ]);

        Route::get('audit/overdue', [
            'as' => 'assets.audit.overdue',
            'uses' => 'Assets\AssetsController@overdueForAudit'
        ]);

        Route::get('audit/due', [
            'as' => 'assets.audit.due',
            'uses' => 'Assets\AssetsController@dueForAudit'
        ]);

        Route::get('audit/overdue', [
            'as' => 'assets.audit.overdue',
            'uses' => 'Assets\AssetsController@overdueForAudit'
        ]);

        Route::get('audit/{id}', [
            'as' => 'asset.audit.create',
            'uses' => 'Assets\AssetsController@audit'
        ]);

        Route::post('audit/{id}', [
            'as' => 'asset.audit.store',
            'uses' => 'Assets\AssetsController@auditStore'
        ]);


        Route::get('history', [
            'as' => 'asset.import-history',
            'uses' => 'Assets\AssetsController@getImportHistory'
        ]);

        Route::post('history', [
            'as' => 'asset.process-import-history',
            'uses' => 'Assets\AssetsController@postImportHistory'
        ]);

        Route::get('bytag/{any?}',
            [
                'as'   => 'findbytag/hardware',
                'uses' => 'Assets\AssetsController@getAssetByTag'
            ]
        )->where('any', '.*');

        Route::get('bytag/{any?}',
            [
                'as'   => 'findbyshipmentnumber/hardware',
                'uses' => 'Assets\AssetsController@getAssetByShipmentNumber'
            ]
        )->where('any', '.*');

        Route::get('byserial/{any?}',
            [
                'as'   => 'findbyserial/hardware',
                'uses' => 'Assets\AssetsController@getAssetBySerial'
            ]
        )->where('any', '.*');



        Route::get('{assetId}/clone', [
            'as' => 'clone/hardware',
            'uses' => 'Assets\AssetsController@getClone'
        ]);

        Route::get('{assetId}/label', [
            'as' => 'label/hardware',
            'uses' => 'Assets\AssetsController@getLabel'
        ]);

        Route::get('{assetId}/labeltag', [
            'as' => 'label/hardware',
            'uses' => 'Assets\AssetsController@getLabelTag'
        ]);

        Route::get(
            '{assetId}/printlabel',
            [ 'as' => 'hardware/printlabel', 'uses' => 'Assets\BulkAssetsController@printTag' ]
        );

        Route::post('{assetId}/clone', 'Assets\AssetsController@postCreate');

        Route::get('{assetId}/checkout', [
            'as' => 'checkout/hardware',
            'uses' => 'Assets\AssetCheckoutController@create'
        ]);
        Route::post('{assetId}/checkout', [
            'as' => 'checkout/hardware',
            'uses' => 'Assets\AssetCheckoutController@store'
        ]);
        Route::get('{assetId}/checkin/{backto?}', [
            'as' => 'checkin/hardware',
            'uses' => 'Assets\AssetCheckinController@create'
        ]);

        Route::post('{assetId}/checkin/{backto?}', [
            'as' => 'checkin/hardware',
            'uses' => 'Assets\AssetCheckinController@store'
        ]);

        Route::get('hubcheckin', [
            'as' => 'hardware/hubcheckin',
            'uses' => 'Assets\AssetCheckinController@showHubCheckInForm'
        ]);

        Route::post('hubcheckin', [
            'as' => 'hardware/hubcheckin',
            'uses' => 'Assets\AssetCheckinController@storeHubcheckin'
        ]);
        //get shipment information by bar code
        Route::post(
            'shipmentdata',
            [ 'as' => 'shipmentdata/accessory', 'uses' => 'Assets\AssetCheckinController@showShipmentData' ]
        );
        Route::get('{assetId}/view', [
            'as' => 'hardware.view',
            'uses' => 'Assets\AssetsController@show'
        ]);
        Route::get('{assetId}/qr_code', [ 'as' => 'qr_code/hardware', 'uses' => 'Assets\AssetsController@getQrCode' ]);
        Route::get('{assetId}/barcode', [ 'as' => 'barcode/hardware', 'uses' => 'Assets\AssetsController@getBarCode' ]);
        Route::get('{assetId}/barcodetag', [ 'as' => 'barcode/hardware', 'uses' => 'Assets\AssetsController@getBarCodeTag' ]);
        Route::post('{assetId}/restore', [
            'as' => 'restore/hardware',
            'uses' => 'Assets\AssetsController@getRestore'
        ]);
        Route::post('{assetId}/upload', [
            'as' => 'upload/asset',
            'uses' => 'Assets\AssetFilesController@store'
        ]);

        Route::get('{assetId}/showfile/{fileId}/{download?}', [
            'as' => 'show/assetfile',
            'uses' => 'Assets\AssetFilesController@show'
        ]);

        Route::delete('{assetId}/showfile/{fileId}/delete', [
            'as' => 'delete/assetfile',
            'uses' => 'Assets\AssetFilesController@destroy'
        ]);


        Route::post(
            'bulkedit',
            [
                'as'   => 'hardware/bulkedit',
                'uses' => 'Assets\BulkAssetsController@edit'
            ]
        );
        Route::post(
            'bulkdelete',
            [
                'as'   => 'hardware/bulkdelete',
                'uses' => 'Assets\BulkAssetsController@destroy'
            ]
        );
        Route::post(
            'bulksave',
            [
                'as'   => 'hardware/bulksave',
                'uses' => 'Assets\BulkAssetsController@update'
            ]
        );
        Route::post(
            'numberdata',
            [ 'as' => 'numberdata/hardware', 'uses' => 'Assets\AssetCheckoutController@showAssetData' ]
        );
        Route::post(
            'barcodedata',
            [ 'as' => 'barcodedata/hardware', 'uses' => 'Assets\AssetCheckoutController@showBarCodeData' ]
        );
        Route::post(
            'shipmentnumberdata',
            [ 'as' => 'shipmentnumberdata/hardware', 'uses' => 'Assets\AssetCheckoutController@showAssetInfoByShipmentNumber' ]
        );
        Route::post(
            'shipmentnumbercheck',
            [ 'as' => 'shipmentnumbercheck/hardware', 'uses' => 'Assets\AssetCheckoutController@checkBarCodeNumberExist' ]
        );
        Route::post(
            'assettagcheck',
            [ 'as' => 'assettagcheck/hardware', 'uses' => 'Assets\AssetCheckoutController@checkAssetTagExist' ]
        );
        Route::post(
            'shipmentcheckfirsttimecheckin',
            [ 'as' => 'shipmentcheckfirsttimecheckin/hardware', 'uses' => 'Assets\AssetCheckoutController@checkBarCodeNumberExistFirstTimeCheckIn' ]
        );
        Route::post(
            'shipmentdatafirsttimecheckin',
            [ 'as' => 'shipmentdatafirsttimecheckin/hardware', 'uses' => 'Assets\AssetCheckinController@showShipmentDataFirstTimeCheckIn' ]
        );
        Route::post(
            'shipmentnumbercheckthirdparty',
            [ 'as' => 'shipmentnumbercheckthirdparty/hardware', 'uses' => 'Assets\AssetCheckoutController@showBarCodeNumberForThirdParty' ]
        );
        Route::post(
            'shipmentnumberhubcheckin',
            [ 'as' => 'shipmentnumberhubcheckin/hardware', 'uses' => 'Assets\AssetCheckoutController@showBarCodeInfoHubCheckin' ]
        );
        # Bulk checkout / checkin
        Route::get( 'bulkcheckout',  [
                 'as' => 'hardware/bulkcheckout',
                 'uses' => 'Assets\BulkAssetsController@showCheckout'
        ]);
        Route::post( 'bulkcheckout',  [
            'as' => 'hardware/bulkcheckout',
            'uses' => 'Assets\BulkAssetsController@storeCheckout'
        ]);
        Route::get( 'bulkcheckin',  [
            'as' => 'hardware/bulkcheckin',
            'uses' => 'Assets\BulkAssetsController@showCheckin'
        ]);
        Route::post( 'bulkcheckin',  [
            'as' => 'hardware/bulkcheckin',
            'uses' => 'Assets\BulkAssetsController@storeCheckin'
        ]);
        Route::get( 'bulkcheckoutsys',  [
            'as' => 'hardware/bulkcheckout',
            'uses' => 'Assets\BulkAssetsController@showBulkCheckout'
        ]);
        Route::post( 'bulkcheckoutsys',  [
            'as' => 'hardware/bulkcheckoutsys',
            'uses' => 'Assets\BulkAssetsController@storeBulkCheckout'
        ]);
        Route::get( 'bulkpod',  [
            'as' => 'hardware/bulkpod',
            'uses' => 'Assets\BulkAssetsController@showBulkPOD'
        ]);
        Route::get( 'bulkcheckouttag',  [
            'as' => 'hardware/bulkcheckouttag',
            'uses' => 'Assets\BulkAssetsController@showBulkCheckoutTag'
        ]);
        Route::post( 'bulkcheckouttag',  [
            'as' => 'hardware/bulkcheckouttag',
            'uses' => 'Assets\BulkAssetsController@storeBulkCheckoutTag'
        ]);
        Route::get( 'bulkcheckoutthirdparty',  [
            'as' => 'hardware/bulkcheckoutthirdparty',
            'uses' => 'Assets\BulkAssetsController@showBulkCheckoutThirdParty'
        ]);
        Route::post( 'bulkcheckoutthirdparty',  [
            'as' => 'hardware/bulkcheckoutthirdparty',
            'uses' => 'Assets\BulkAssetsController@storeBulkCheckoutThirdParty'
        ]);
        Route::get( 'individualcheckout',  [
            'as' => 'hardware/individualcheckout',
            'uses' => 'Assets\AssetCheckoutController@createIndividual'
        ]);
        Route::post( 'individualcheckout',  [
            'as' => 'hardware/individualcheckout',
            'uses' => 'Assets\AssetCheckoutController@storeIndividual'
        ]);
        Route::post( 'bulkcheckout',  [
            'as' => 'hardware/bulkcheckout',
            'uses' => 'Assets\BulkAssetsController@storeCheckout'
        ]);
        
        Route::post( 'bulkstatusupdate', [
            'as'   => 'hardware/updatebulkstatus',
            'uses' => 'Assets\BulkAssetsController@updateBulkStatus'
        ]);
        Route::post( 'statusupdate',[
                'as'   => 'hardware/updatestatus',
                'uses' => 'Assets\BulkAssetsController@updateStatus'
        ]);
    });


    Route::resource('hardware', 'Assets\AssetsController', [
        'middleware' => ['auth'],
        'parameters' => ['asset' => 'asset_id']
    ]);
