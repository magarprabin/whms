<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehiclesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Show the page
        $this->authorize('create', Vehicle::class);
        return view('vehicles.index')->with('item', new Vehicle);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        // Show the page
        $this->authorize('create', Vehicle::class);

        return view('vehicles/edit')->with('item', new Vehicle);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->authorize('create', Vehicle::class);

            DB::beginTransaction();
            $data = [
                'name'=>trim($request->get('name')),
                'vehicle_no'=>trim($request->get('vehicle_no')),
                'vehicle_type'=>trim($request->get('vehicle_type')),
                'status'=>trim($request->get('status')),
            ];

            $vehicle = Vehicle::create($data);
            DB::commit();

            return redirect()->route('vehicles.index')->with('success', trans('admin/vehicles/message.create.success'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('error',$exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($vehicleId)
    {
        $this->authorize('update', Vehicle::class);
        if ($vehicle = Vehicle::find($vehicleId)) {
            return view('vehicles/view', compact('rider'));
        }
        return redirect()->route('vehicles.index')->with('error', trans('admin/vehicles/message.does_not_exist'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($vehicleId = null)
    {
        $this->authorize('update', Vehicle::class);
        if (is_null($item = Vehicle::find($vehicleId))) {
            return redirect()->route('vehicles.index')->with('error', trans('admin/vehicles/message.does_not_exist'));
        }
        return view('vehicles/edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $vehicleId)
    {
        $this->authorize('update', Vehicle::class);
        if (is_null($vehicle = Vehicle::find($vehicleId))) {
            // Redirect to the vehicles management page
            return redirect()->to('admin/vehicles')->with('error', trans('admin/vehicles/message.does_not_exist'));
        }

        DB::beginTransaction();

        $data = [
            'name'=>trim($request->get('name')),
            'vehicle_no'=>trim($request->get('vehicle_no')),
            'vehicle_type'=>trim($request->get('vehicle_type')),
            'status'=>trim($request->get('status')),
        ];

        $vehicle1 = $vehicle->update($data);
        if ($vehicle1) {
            DB::commit();
            // Redirect to the new route page
            return redirect()->route('vehicles.index')->with('success', trans('admin/vehicles/message.update.success'));
        }
        DB::rollBack();
        // The given data did not pass validation
        return redirect()->back()->withInput()->withErrors($vehicle->getErrors());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($vehicleId)
    {
        $this->authorize('delete', Vehicle::class);
        // Check if the route exists
        if (is_null($vehicle = Vehicle::findOrFail($vehicleId))) {
            return redirect()->route('vehicles.index')->with('error', trans('admin/vehicles/message.not_found'));
        }

//        if (!$vehicle->isDeletable()) {
//            return redirect()->route('vehicles.index')->with('error', trans('admin/vehicles/message.assoc_items', ['asset_type'=> $vehicle->route_type ]));
//        }

        $vehicle->delete();
        // Redirect to the locations management page
        return redirect()->route('vehicles.index')->with('success', trans('admin/vehicles/message.delete.success'));
    }
}
