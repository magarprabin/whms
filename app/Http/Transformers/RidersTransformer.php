<?php
namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Rider;
use Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class RidersTransformer
{

    public function transformRiders (Collection $riders, $total)
    {
        $array = array();
        foreach ($riders as $rider) {
            $array[] = self::transformRider($rider);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformRider (Rider $rider = null)
    {
        if ($rider) {

            $array = [
                'id' => (int) $rider->id,
                'user_id' => (int) $rider->user_id,
                'username' => isset($rider->user) ? e($rider->user->first_name.' '.$rider->last_name):'',
                'shift_from_time' => e($rider->shift_from_time),
                'shift_to_time' => e($rider->shift_to_time),
                'vehicle_type' => ucfirst(str_replace('_',' ',$rider->vehicle_type)),
                'phone' => isset($rider->user) ? e($rider->user->phone) : '',
                'status' => ($rider->status == 1) ? 'Active' : 'Inactive',
                'created_at' => Helper::getFormattedDateObject($rider->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($rider->updated_at, 'datetime'),
            ];

            $permissions_array['available_actions'] = [
                'update' => Gate::allows('update', Rider::class),
//                'delete' => $rider->isDeletable(),
            ];

            $array += $permissions_array;

            return $array;
        }

    }

}
