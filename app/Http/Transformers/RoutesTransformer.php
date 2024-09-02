<?php
namespace App\Http\Transformers;

use App\Helpers\Helper;
use App\Models\Route;
use Gate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class RoutesTransformer
{

    public function transformRoutes (Collection $routes, $total)
    {
        $array = array();
        foreach ($routes as $route) {
            $array[] = self::transformRoute($route);
        }
        return (new DatatablesTransformer)->transformDatatables($array, $total);
    }

    public function transformRoute (Route $route = null)
    {
        if ($route) {

            $array = [
                'id' => (int) $route->id,
                'name' => e($route->name),
                'start_location' => e($route->start_location),
                'end_location' => e($route->end_location),
                'status' => ($route->status == 1) ? 'Active' : 'Inactive',
                'created_at' => Helper::getFormattedDateObject($route->created_at, 'datetime'),
                'updated_at' => Helper::getFormattedDateObject($route->updated_at, 'datetime'),
            ];

            $permissions_array['available_actions'] = [
                'update' => Gate::allows('update', Route::class),
//                'delete' => $route->isDeletable(),
            ];

            $array += $permissions_array;

            return $array;
        }

    }

}
