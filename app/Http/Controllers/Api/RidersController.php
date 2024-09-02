<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\RidersTransformer;
use App\Models\Rider;
use Illuminate\Http\Request;

class RidersController extends Controller
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
        $this->authorize('view', Rider::class);
        $allowed_columns = ['id', 'name','shift_from_time', 'shift_to_time','vehicle_type','status','user_id'];

        $riders = Rider::select(['id', 'created_at', 'updated_at', 'shift_from_time', 'shift_to_time','vehicle_type','status','user_id']);

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
        return (new RidersTransformer())->transformRiders($riders, $total);

    }
}
