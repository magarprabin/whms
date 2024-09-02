<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\SelectlistTransformer;
use App\Models\Category;
use App\Models\LogisticCategory;
use App\Models\Route;
use App\Utilities\CategoryUtility;
use Illuminate\Http\Request;

class FilteredCategoriesController extends Controller
{
    public function selectlist(Request $request)
    {
        $categoriesIdInLogistic = CategoryUtility::getCategoriesIdInLogistic();
        $categories = Category::select(
            [
                'categories.id',
                'categories.name',
            ]
        )
        ->whereNotIn('id',$categoriesIdInLogistic)
        ->whereNull('deleted_at');

        if ($request->filled('search')) {
            $categories = $categories->where('name', 'LIKE', '%'.$request->get('search').'%');
        }

        $categories = $categories->orderBy('name', 'asc');
        $categories = $categories->paginate(50);

        return (new SelectlistTransformer())->transformSelectlist($categories);
    }

    public function selectlist_logistic(Request $request)
    {
        $categories = LogisticCategory::select(
            [
                'logistic_categories.id',
                'logistic_categories.name',
            ]
        )
        ->whereNull('deleted_at');

        if ($request->filled('search')) {
            $categories = $categories->where('name', 'LIKE', '%'.$request->get('search').'%');
        }

        $categories = $categories->orderBy('name', 'asc');
        $categories = $categories->paginate(50);

        return (new SelectlistTransformer())->transformSelectlist($categories);
    }
    
    public function selectlist_route(Request $request)
    {
        $routes = Route::select(
            [
                'routes.id',
                'routes.name',
            ]
        )
        ->whereNull('deleted_at');

        if ($request->filled('search')) {
            $routes = $routes->where('name', 'LIKE', '%'.$request->get('search').'%');
        }

        $routes = $routes->orderBy('name', 'asc');
        $routes = $routes->paginate(50);

        return (new SelectlistTransformer())->transformSelectlist($routes);
    }
}
