<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\LogisticCategoriesTransformer;
use App\Models\LogisticCategory;
use Illuminate\Http\Request;

class LogisticCategoriesController extends Controller
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
        $this->authorize('view', LogisticCategory::class);
        $allowed_columns = ['id', 'name','status','category_id'];

        $logisticCategories = LogisticCategory::select(['id', 'created_at', 'updated_at', 'name','category_id','status',]);

        if ($request->filled('search')) {
            $logisticCategories = $logisticCategories->TextSearch($request->input('search'));
        }

        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($logisticCategories) && ($request->get('offset') > $logisticCategories->count())) ? $logisticCategories->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'id';
        $logisticCategories->orderBy($sort, $order);

        $total = $logisticCategories->count();
        $logisticCategories = $logisticCategories->skip($offset)->take($limit)->get();
        return (new LogisticCategoriesTransformer())->transformLogisticCategories($logisticCategories, $total);

    }
}
