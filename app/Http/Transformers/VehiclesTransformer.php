<?php
namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Vehicle;
use Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class VehiclesTransformer
{

    public function transformVehicles (Collection $vehicles, $total)
    {
        $array = array();
        foreach ($vehicles as $vehicle) {
            $array[] = self::transformVehicle($vehicle);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformVehicle (Vehicle $vehicle = null)
    {
        if ($vehicle) {

            $array = [
                'id' => (int) $vehicle->id,
                'name' => e($vehicle->name),
                'vehicle_no' => e($vehicle->vehicle_no),
                'vehicle_type' => ucfirst(str_replace('_',' ',$vehicle->vehicle_type)),
                'status' => ($vehicle->status == 1) ? 'Active' : 'Inactive',
                'created_at' => Helper::getFormattedDateObject($vehicle->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($vehicle->updated_at, 'datetime'),
            ];

            $permissions_array['available_actions'] = [
                'update' => Gate::allows('update', Vehicle::class),
//                'delete' => $vehicle->isDeletable(),
            ];

            $array += $permissions_array;

            return $array;
        }

    }

}
