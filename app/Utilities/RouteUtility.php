<?php

namespace App\Utilities;

use App\Models\Route;
use Illuminate\Support\Arr;

class RouteUtility
{
    public static function getRouteAssignedSuppliersId()
    {
        $suppliersId = Route::whereNull('deleted_at')->pluck('supplier_id')->toArray();

        $ids = [];
        if (count($suppliersId) > 0) {
            foreach ($suppliersId as $key=>$supplierId) {
                array_push($ids,json_decode($supplierId,true));
            }

            $ids = Arr::flatten($ids);
        }
       return $ids;
    }
}