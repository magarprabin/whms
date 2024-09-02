<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\VehiclesTransformer;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehiclesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view', Vehicle::class);
        $allowed_columns = ['id', 'name', 'vehicle_no','vehicle_type','status'];

        $riders = Vehicle::select(['id', 'created_at', 'updated_at', 'vehicle_no', 'name','vehicle_type','status']);

        if ($request->filled('search')) {
            $riders = $riders->TextSearch($request->input('search'));
        }

        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($riders) && ($request->get('offset') > $riders->count())) ? $riders->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';
        $riders->orderBy($sort, $order);

        $total = $riders->count();
        $riders = $riders->skip($offset)->take($limit)->get();
        return (new VehiclesTransformer())->transformVehicles($riders, $total);

    }
}
