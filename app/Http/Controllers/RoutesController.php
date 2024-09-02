<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRouteRequest;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoutesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Show the page
        $this->authorize('create', Route::class);
        return view('routes.index')->with('item', new Route);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Show the page
        $this->authorize('create', Route::class);

        return view('routes/edit')->with('item', new Route);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateRouteRequest $request)
    {
        $this->authorize('create', Route::class);

        DB::beginTransaction();
        $route = new Route();
        $validatedData = $request->validated();
        $startLocationGeo = app('geocoder')->geocode($validatedData['start_location'])->get();
        $endLocationGeo = app('geocoder')->geocode($validatedData['end_location'])->get();
        $startLocationCoordinates = $startLocationGeo[0]->getCoordinates();
        $endLocationCoordinates = $endLocationGeo[0]->getCoordinates();
        $route->name = trim($validatedData['name']);
        $route->start_lat = $startLocationCoordinates->getLatitude();
        $route->start_lng = $startLocationCoordinates->getLongitude();
        $route->end_lat = $endLocationCoordinates->getLatitude();
        $route->end_lng = $endLocationCoordinates->getLongitude();
        $route->supplier_id = json_encode($validatedData['supplier_id']);
        $route->start_location = trim($validatedData['start_location']);
        $route->end_location = trim($validatedData['end_location']);
        $route->status = $validatedData['status'];

        if ($route->save()) {
            DB::commit();
            return redirect()->route('routes.index')->with('success', trans('admin/routes/message.create.success'));
        } else {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors($route->getErrors());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($routeId)
    {
        $this->authorize('update', Route::class);
        if ($route = Route::find($routeId)) {
            return view('routes/view', compact('route'));
        }
        return redirect()->route('routes.index')->with('error', trans('admin/routes/message.does_not_exist'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($routeId = null)
    {
        $this->authorize('update', Route::class);
        if (is_null($item = Route::find($routeId))) {
            return redirect()->route('routes.index')->with('error', trans('admin/routes/message.does_not_exist'));
        }
        return view('routes/edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(CreateRouteRequest $request, $routeId)
    {
        $this->authorize('update', Route::class);
        if (is_null($route = Route::find($routeId))) {
            // Redirect to the routes management page
            return redirect()->to('admin/routes')->with('error', trans('admin/routes/message.does_not_exist'));
        }

        DB::beginTransaction();
        $validatedData = $request->validated();
        $validatedData['name'] = trim($validatedData['name']);
        $validatedData['start_location'] = trim($validatedData['start_location']);
        $validatedData['end_location'] = trim($validatedData['end_location']);
        $startLocationGeo = app('geocoder')->geocode($validatedData['start_location'])->get();
        $endLocationGeo = app('geocoder')->geocode($validatedData['end_location'])->get();
        $startLocationCoordinates = $startLocationGeo[0]->getCoordinates();
        $endLocationCoordinates = $endLocationGeo[0]->getCoordinates();
        $validatedData['start_lat'] = $startLocationCoordinates->getLatitude();
        $validatedData['start_lng'] = $startLocationCoordinates->getLongitude();
        $validatedData['end_lat'] = $endLocationCoordinates->getLatitude();
        $validatedData['end_lng'] = $endLocationCoordinates->getLongitude();
        $validatedData['supplier_id'] = json_encode($validatedData['supplier_id']);
        $route1 = $route->update($validatedData);
        if ($route1) {
            DB::commit();
            // Redirect to the new route page
            return redirect()->route('routes.index')->with('success', trans('admin/routes/message.update.success'));
        }
        DB::rollBack();
        // The given data did not pass validation
        return redirect()->back()->withInput()->withErrors($route->getErrors());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($routeId)
    {
        $this->authorize('delete', Route::class);
        // Check if the route exists
        if (is_null($route = Route::findOrFail($routeId))) {
            return redirect()->route('routes.index')->with('error', trans('admin/routes/message.not_found'));
        }

//        if (!$route->isDeletable()) {
//            return redirect()->route('routes.index')->with('error', trans('admin/routes/message.assoc_items', ['asset_type'=> $route->route_type ]));
//        }

        $route->delete();
        // Redirect to the locations management page
        return redirect()->route('routes.index')->with('success', trans('admin/routes/message.delete.success'));
    }

}
