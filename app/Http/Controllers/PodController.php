<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rider;
use App\Models\Vehicle;
use App\Models\Pod;
use App\Models\Pod_asset;
use App\Models\Asset;
use App\Models\Setting;
use Illuminate\Support\Facades\File;


class PodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('create', Pod::class);
        return view('pod.index')
            ->with('item', new Pod);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pod.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $pod = Pod::find($id);
        $podAssets = Pod_asset::where('po_id', $id)->get();
        return view('pod/show')->with('pod', $pod)->with('pod_assets', $podAssets);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $pod = Pod::find($id);
        $data = [];

        
        $podAssets = Pod_asset::where('po_id', $id)->get(); // Retrieve associated assets   

        return view('pod/edit')
        ->with('pod', $pod)
        ->with('item', $pod)
        ->with('pod_assets', $podAssets)
        ->with('vehicles', Vehicle::where('status', '=', 1)->get())
        ->with('riders', Rider::where('status', '=', 1)->get())
        ->with('barcode', '../../uploads/pod_barcode/' . $pod->pod_id . '.png');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $this->authorize('update', Pod::class);
        $pod = Pod::find($id);
        $pod->rider_id = $request['rider'];
        $pod->vehicle_id = $request['vehicle'];
        $pod->address = $request['address'];
        $pod->contact_person = $request['contact_person'];
        $pod->phone_no = $request['phone_no'];
        $pod->save();
        return redirect()->route('pod.index')->with('success', trans('admin/pod/general.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function print_pod($id)
    {
        $pod = Pod::find($id);
        $data = [];

        
        $podAssets = Pod_asset::where('po_id', $id)->get();        

        $data[] = [
            'pod' => $pod,
            'pod_assets' => $podAssets,
            'barcode' => '../../uploads/pod_barcode/' . $pod->pod_id . '.png'
        ];
    
        return view('pod/print_pod')
            ->with('data', $data);

    }

    public function generate_pod(Request $request){
        if (!$request->filled('main_rider') || !$request->filled('main_vehicle')) {
            return redirect()->back()->with('error', 'Please select main rider and vehicle');
        }
        for($i=0;$i<$request['no_of_supplier'];$i++){
            $pod = new Pod();
            $pod->pod_id = $this->generatePodId();
            $pod->num_of_item = count($request['asset_ids'][$i]);
            $pod->supplier_id = $request['supplier_id'][$i];
            $pod->rider_id = !empty($request['rider'][$i])?$request['rider'][$i]:$request['main_rider'];
            $pod->vehicle_id = !empty($request['vehicle'][$i])?$request['vehicle'][$i]:$request['main_vehicle'];
            $pod->address = $request['address'][$i];
            $pod->contact_person = $request['contact'][$i];
            $pod->phone_no = $request['phone'][$i];
            $pod->status = 'pending'; // Set the status value accordingly
            $pod->save();
    
            $podId[$i] = $pod->id;

            foreach ($request['asset_ids'][$i] as $assetId) {
                $podAsset = new Pod_asset();
                $podAsset->po_id = $podId[$i];
                $podAsset->pod_id = $pod->pod_id;
                $podAsset->asset_id = $assetId;
                $podAsset->count = 1; // Assuming count is always 1 for each asset
                $podAsset->status = 'pending'; // Set the status value accordingly
                $podAsset->save();

                $asset = Asset::find($assetId);
                $asset->status_id = (config('app.pod_generated') != NULL)?config('app.pod_generated'):'14';
                $asset->save();
            }

            $settings = Setting::getSettings();
            $folderPath = public_path('uploads/pod_barcode');

            // Create the directory if it doesn't exist
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true); // Recursive directory creation
            }

            $barcode_file = $folderPath . '/' . $pod->pod_id . '.png';
            $barcode_width = ($settings->labels_width - $settings->labels_display_sgutter) * 50.000000000001;
            // instantiate the barcode class
            $barcode = new \Com\Tecnick\Barcode\Barcode();

            // generate a barcode
            $bobj = $barcode->getBarcodeObj(
                $settings->alt_barcode,                     // barcode type and additional comma-separated parameters
                $pod->pod_id,          // data string to encode
                ($barcode_width < 300 ? $barcode_width : 300),                             // bar width (use absolute or negative value as multiplication factor)
                50                           // bar height (use absolute or negative value as multiplication factor)    
                ); // background color

                file_put_contents($barcode_file, $bobj->getPngData()); 
        }
        $pod_data = Pod::find($podId);
        $data = [];

        foreach ($pod_data as $pod) {
            $podAssets = Pod_asset::where('po_id', $pod->id)->get(); // Retrieve associated assets

            $data[] = [
                'pod' => $pod,
                'pod_assets' => $podAssets,
                'barcode' => './uploads/pod_barcode/' . $pod->pod_id . '.png',
            ];
        }

        return view('pod/print_pod')
            ->with('data', $data);
    }

    function generatePodId($id=NULL)
    {
        $timestamp = time(); // Get the current timestamp
        $randomString = $this->generateRandomString(6); // Generate a random alphanumeric string

        return $timestamp . $randomString;
    }

    function generateRandomString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
    
    function checkin_pod ($id) {
        $pod = Pod::find($id);
        $podAssets = Pod_asset::where('po_id', $id)->get();
        return view('pod/checkin')->with('pod', $pod)->with('pod_assets', $podAssets)->with('item', $pod);
    }

    function upload_pod ($id) {
        $pod = Pod::find($id);
        return view('pod/upload')->with('pod_id',$id)->with('pod',$pod);
    }

    function upload_pod_image(Request $request,$id) {
        $filename = time() . "-pod." . $request->file('image')->getClientOriginalExtension();
        $filePath = public_path('uploads/pod_uploads/' . $filename);
        $request->file('image')->move(public_path('uploads/pod_uploads'), $filename);

        $pod = Pod::find($id);
        $pod->image = 'uploads/pod_uploads/'.$filename;
        $pod->save();

        return redirect()->route('pod.index')->with('success', trans('admin/pod/general.update_success'));

    }
}