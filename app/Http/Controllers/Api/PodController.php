<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\PodTransformer;
use App\Models\Pod;
use Illuminate\Http\Request;

class PodController extends Controller
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
        $this->authorize('view', Pod::class);
        $allowed_columns = ['id', 'pod_id','num_of_item', 'supplier_id','vehicle_id','rider_id','status','phone_no','contact_person'];

        $pod = Pod::select(['id', 'created_at', 'updated_at','pod_id','num_of_item', 'supplier_id','vehicle_id','rider_id','status','phone_no','contact_person']);

        if ($request->filled('search')) {
            $pod = $pod->TextSearch($request->input('search'));
        }

        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($pod) && ($request->get('offset') > $pod->count())) ? $pod->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';
        $pod->orderBy($sort, $order);

        $total = $pod->count();
        $pod = $pod->skip($offset)->take($limit)->get();
        return (new PodTransformer())->transformPod($pod, $total);

    }
}
