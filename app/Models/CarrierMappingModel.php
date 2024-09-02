<?php

namespace App\Models;

use App\Http\Requests\ApiRequest;
use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class CarrierMappingModel extends SnipeModel
{
    use ValidatingTrait;
    use UniqueUndeletedTrait;
    protected $table = 'carrier_mapping';

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     * 
     * @var array
     */

    /**
     * The relations and their attributes that should be included when searching the model.
     * 
     * @var array
     */
    protected $searchableRelations = [];


    public static function syncCscartShipment($shipment_id, $cscart_carrier_id)
    {
        $ApiRequest = new ApiRequest();
        if ($cscart_carrier_id == env('CSCART_NCM_CARRIER')) {
            $response = $ApiRequest->apiCall('PUT', 'shipments/' . $shipment_id, ['carrier' => 'nepal_can_move', 'external_sync' => true]);
            $message = isset($response['sync'][1]) ? $response['sync'][1] : 'Error';
        } else {
            $message = 'Select NCM as the third party';
        }
        $barcode_number = Asset::where('shipment_number', $shipment_id)->value('barcode_number');

        return $barcode_number . ": " . $message;
    }

    public static function syncTagShipmentCarrier($shipments, $status_id, $asset_tag, $cscart_carrier_id)
    {
        $ApiRequest = new ApiRequest();
        if ($cscart_carrier_id == env('CSCART_NCM_CARRIER')) {
            $status_to_sync = StatusMapping::with('status_label')->where('status_id', $status_id)->value('mapped_code');
            $result = $ApiRequest->apiCall('POST', 'asset_tags', ['shipments' => $shipments, 'asset_tag' => $asset_tag, 'status' => $status_to_sync], array());
            if ($result['status'] == 'NCMSync') {
                $message = $result['sync'][1];
            } else {
                $message = "Error";
            }
        } else {
            $message = 'Select NCM as the third party';
        }
        return $message;
    }
}
