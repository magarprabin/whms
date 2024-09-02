<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\RoutesTransformer;
use App\Models\Route;
use Illuminate\Http\Request;

class RoutesController extends Controller
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
        $this->authorize('view', Route::class);
        $allowed_columns = ['id', 'name','start_location', 'end_location','end_location','status','supplier_id'];

        $suppliers = Route::select(['id', 'created_at', 'updated_at', 'name','start_location', 'end_location','supplier_id','status','start_lat','start_lng','end_lat','end_lng']);

        if ($request->filled('search')) {
            $suppliers = $suppliers->TextSearch($request->input('search'));
        }

        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($suppliers) && ($request->get('offset') > $suppliers->count())) ? $suppliers->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'id';
        $suppliers->orderBy($sort, $order);

        $total = $suppliers->count();
        $suppliers = $suppliers->skip($offset)->take($limit)->get();
        return (new RoutesTransformer())->transformRoutes($suppliers, $total);

    }
}
