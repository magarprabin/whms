<?php

namespace App\Models;

use App\Http\Requests\ApiRequest;
use Illuminate\Database\Eloquent\Model;

class StatusMapping extends Model
{
    public function status_label()
    {
        return $this->belongsTo('\App\Models\Statuslabel', 'status_id');
    }

    public static function syncShipmentStatus($shipment_id, $status_id)
    {
        $ApiRequest = new ApiRequest();
        $status_to_sync = StatusMapping::with('status_label')->where('status_id', $status_id)->value('mapped_code');
        $response = $ApiRequest->apiCall('PUT', 'shipments/' . $shipment_id, ['status' => $status_to_sync]);
        if ($response['shipment_id']) {
            $message = trans('admin/hardware/message.status_sync.success');
        } else {
            $message = trans('admin/hardware/message.status_sync.error');
        }
        $barcode_number = Asset::where('shipment_number', $shipment_id)->value('barcode_number');

        return $barcode_number . ": " . $message;
    }
}
