<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Supplier;
use App\Utilities\RouteUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FilteredSuppliersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Http\Response
     */
    public function getFilteredSuppliers(Request $request)
    {
        $this->authorize('view.selectlists');

        $suppliersId = RouteUtility::getRouteAssignedSuppliersId();
        $suppliers = Supplier::select([
            'id',
            'name',
            'image',
        ])->whereNotIn('id',$suppliersId);

        if ($request->filled('search')) {
            $suppliers = $suppliers->where('suppliers.name', 'LIKE', '%'.$request->get('search').'%');
        }

        $suppliers = $suppliers->orderBy('name', 'ASC')->paginate(50);

        // Loop through and set some custom properties for the transformer to use.
        // This lets us have more flexibility in special cases like assets, where
        // they may not have a ->name value but we want to display something anyway
        foreach ($suppliers as $supplier) {
            $supplier->use_text = $supplier->name;
            $supplier->use_image = ($supplier->image) ? Storage::disk('public')->url('suppliers/'.$supplier->image, $supplier->image) : null;
        }

        return (new SelectlistTransformer)->transformSelectlist($suppliers);
    }
}
