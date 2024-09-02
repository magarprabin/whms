<?php

namespace App\Utilities;

use App\Models\Rider;

class RiderUtility
{
    public static function getVehicleRiderId()
    {
        $ridersId = Rider::whereNull('deleted_at')->where('status',1)->pluck('id')->toArray();

        return $ridersId;
    }
}