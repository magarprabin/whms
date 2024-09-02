<?php
namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Pod;
use Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class PodTransformer
{

    public function transformPod (Collection $pod, $total)
    {
        $array = array();
        foreach ($pod as $po) {
            $array[] = self::transformRider($po);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformRider (Pod $pod = null)
    {
        if ($pod) {
            $array = [
                'id' => (int) $pod->id,
                'pod_id' => $pod->pod_id,
                'num_of_item' => (int) $pod->num_of_item,
                'supplier' => isset($pod->supplier->name) ? e($pod->supplier->name) : '',
                'rider' => isset($pod->rider->user) ? e($pod->rider->user->first_name.' '.$pod->rider->user->last_name):'',
                'vehicle' => isset($pod->vehicle->name) ? e($pod->vehicle->name) : '',
                'address' => $pod->address,
                'phone_no' => e($pod->phone_no),
                'status' => ($pod->status == 1) ? 'Active' : 'Inactive',
                'created_at' => Helper::getFormattedDateObject($pod->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($pod->updated_at, 'datetime'),
            ];

            $permissions_array['available_actions'] = [
                'update' => Gate::allows('update', Pod::class),
//                'delete' => $rider->isDeletable(),
            ];

            $array += $permissions_array;

            return $array;
        }

    }

}
