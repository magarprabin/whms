<?php

namespace App\Http\Controllers;

use App\Http\Requests\riders\CreateRiderRequest;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RidersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Show the page
        $this->authorize('create', Rider::class);
        return view('riders.index')->with('item', new Rider);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        // Show the page
        $this->authorize('create', Rider::class);

        return view('riders/edit')->with('item', new Rider);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateRiderRequest $request)
    {
        try {
            $this->authorize('create', Rider::class);

            DB::beginTransaction();
            $validatedData = $request->validated();
            $validatedData['shift_from_time'] = trim($validatedData['shift_from_time']);
            $validatedData['shift_to_time'] = trim($validatedData['shift_to_time']);
            $validatedData['vehicle_type'] = trim($validatedData['vehicle_type']);
            $rider = Rider::create($validatedData);
            DB::commit();

            return redirect()->route('riders.index')->with('success', trans('admin/riders/message.create.success'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return redirect()->back()->with('error',$exception->getMessage());
        }
//
//        if ($rider1) {
//            return redirect()->route('riders.index')->with('success', trans('admin/riders/message.create.success'));
//        } else {
//        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($riderId)
    {
        $this->authorize('update', Rider::class);
        if ($rider = Rider::find($riderId)) {
            return view('riders/view', compact('rider'));
        }
        return redirect()->route('riders.index')->with('error', trans('admin/riders/message.does_not_exist'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($riderId = null)
    {
        $this->authorize('update', Rider::class);
        if (is_null($item = Rider::find($riderId))) {
            return redirect()->route('riders.index')->with('error', trans('admin/riders/message.does_not_exist'));
        }
        return view('riders/edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(CreateRiderRequest $request, $riderId)
    {
        $this->authorize('update', Rider::class);
        if (is_null($rider = Rider::find($riderId))) {
            // Redirect to the riders management page
            return redirect()->to('admin/riders')->with('error', trans('admin/riders/message.does_not_exist'));
        }

        DB::beginTransaction();
        $validatedData = $request->validated();
        $validatedData['shift_from_time'] = trim($validatedData['shift_from_time']);
        $validatedData['shift_to_time'] = trim($validatedData['shift_to_time']);
        $validatedData['vehicle_type'] = trim($validatedData['vehicle_type']);
        $rider1 = $rider->update($validatedData);
        if ($rider1) {
            DB::commit();
            // Redirect to the new route page
            return redirect()->route('riders.index')->with('success', trans('admin/riders/message.update.success'));
        }
        DB::rollBack();
        // The given data did not pass validation
        return redirect()->back()->withInput()->withErrors($rider->getErrors());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($riderId)
    {
        $this->authorize('delete', Rider::class);
        // Check if the route exists
        if (is_null($rider = Rider::findOrFail($riderId))) {
            return redirect()->route('riders.index')->with('error', trans('admin/riders/message.not_found'));
        }

//        if (!$rider->isDeletable()) {
//            return redirect()->route('riders.index')->with('error', trans('admin/riders/message.assoc_items', ['asset_type'=> $rider->route_type ]));
//        }

        $rider->delete();
        // Redirect to the locations management page
        return redirect()->route('riders.index')->with('success', trans('admin/riders/message.delete.success'));
    }
}
