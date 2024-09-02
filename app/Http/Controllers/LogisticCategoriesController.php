<?php

namespace App\Http\Controllers;

use App\Http\Requests\logisticCategories\CreateLogisticCategoryRequest;
use App\Http\Requests\logisticCategories\UpdateLogisticCategoryRequest;
use App\Models\Category;
use App\Models\LogisticCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LogisticCategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Show the page
        $this->authorize('create', LogisticCategory::class);
        return view('logistic_categories.index')->with('item', new LogisticCategory);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Show the page
        $this->authorize('create', LogisticCategory::class);

        return view('logistic_categories/edit')->with('item', new LogisticCategory);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateLogisticCategoryRequest $request)
    {
        $this->authorize('create', LogisticCategory::class);

        DB::beginTransaction();
        $request->validated();
        $logisticCategory = new LogisticCategory();
        $logisticCategory->name = trim($request->get('name'));
        $logisticCategory->status = trim($request->get('status'));
        $logisticCategory->category_id = implode(',',$request->get('category_id'));

        if ($logisticCategory->save()) {
            $this->updateLogisticIdToCategory($request->get('category_id'),$logisticCategory);
            DB::commit();
            return redirect()->route('logistic_categories.index')->with('success', trans('admin/logistic_categories/message.create.success'));
        } else {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors($logisticCategory->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($logisticCategoryId)
    {
        $this->authorize('update', LogisticCategory::class);
        if ($logisticCategory = LogisticCategory::find($logisticCategoryId)) {
            return view('logistic_categories/view', compact('logisticCategory'));
        }
        return redirect()->route('logistic_categories.index')->with('error', trans('admin/logistic_categories/message.does_not_exist'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($logisticCategoryId = null)
    {
        $this->authorize('update', LogisticCategory::class);
        if (is_null($item = LogisticCategory::find($logisticCategoryId))) {
            return redirect()->route('logistic_categories.index')->with('error', trans('admin/logistic_categories/message.does_not_exist'));
        }
        return view('logistic_categories/edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLogisticCategoryRequest $request, $logisticCategoryId)
    {
        $this->authorize('update', LogisticCategory::class);

        $validatedData = $request->validated();
        $logisticCategory = LogisticCategory::find($logisticCategoryId);
        if (is_null($logisticCategory)) {
            // Redirect to the logistic_categories management page
            return redirect()->to('admin/logistic_categories')->with('error', trans('admin/logistic_categories/message.does_not_exist'));
        }

        DB::beginTransaction();
        $validatedData['name'] = trim($validatedData['name']);
        $validatedData['status'] = trim($validatedData['status']);
        $validatedData['category_id'] = implode(',',$validatedData['category_id']);

        if ($logisticCategory->update($validatedData)) {
            $this->updateLogisticIdToCategory($request->get('category_id'),$logisticCategory);
            DB::commit();
            // Redirect to the new route page
            return redirect()->route('logistic_categories.index')->with('success', trans('admin/logistic_categories/message.update.success'));
        }
        DB::rollBack();
        // The given data did not pass validation
        return redirect()->back()->withInput()->withErrors($logisticCategory->getErrors());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($logisticCategoryId)
    {
        $this->authorize('delete', LogisticCategory::class);
        // Check if the route exists
        if (is_null($logisticCategory = LogisticCategory::findOrFail($logisticCategoryId))) {
            return redirect()->route('logistic_categories.index')->with('error', trans('admin/logistic_categories/message.not_found'));
        }

        if (!$logisticCategory->isDeletable()) {
            return redirect()->route('logistic_categories.index')->with('error', trans('admin/logistic_categories/message.assoc_items', ['asset_type'=> $logisticCategory->route_type ]));
        }

        $logisticCategory->delete();
        // Redirect to the locations management page
        return redirect()->route('logistic_categories.index')->with('success', trans('admin/logistic_categories/message.delete.success'));
    }

    public function updateLogisticIdToCategory(array $categoriesId,LogisticCategory $logisticCategory)
    {
        try {
            if (count($categoriesId) > 0 && isset($logisticCategory->id)) {
                if ($logisticCategory->categories()->count()>0) {
                    $logisticCategory->categories()->update(['logistic_category_id'=>null]);
                    $categories = Category::whereIn('id',$categoriesId)->update(['logistic_category_id'=>$logisticCategory->id]);
                } else {
                    $categories = Category::whereIn('id',$categoriesId)->update(['logistic_category_id'=>$logisticCategory->id]);
                }
                return true;
            }
        } catch (\Exception $exception) {
            throw new \Exception('Categories not updated');
        }
    }
}
