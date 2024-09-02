<?php

namespace App\Http\Controllers\Assets;


use App\Exceptions\CheckoutNotAllowed;
use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCheckoutRequest;
use App\Models\Asset;
use App\Models\Actionlog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use DB;
use Illuminate\Http\Request;
use App\Http\Requests\ApiRequest;
use App\Models\StatusMapping;
use App\Models\CarrierModel;
use App\Models\CarrierMappingModel;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AssetCheckoutController extends Controller
{
    use CheckInOutRequest;
    /**
    * Returns a view that presents a form to check an asset out to a
    * user.
    *
    * @author prabinthapamagar
    * @param int $assetId
    * @since [v1.0]
    * @return View
    */
    public function create($assetId)
    {
        $locations = Location::get();
        $current_user_id = Auth::user()->id;
        $current_user_location_id = Auth::user()->location_id;
        $current_user_location_name = Location::where('id',$current_user_location_id)->value('name');
        $carrier_lists = CarrierModel::get();
        if (is_null($asset = Asset::find(e($assetId)))) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'))
            ->with('locations',$locations)
            ->with('logged_in_user_location_id',$current_user_location_id)
            ->with('current_user_location_name',$current_user_location_name)
            ->with('carrier_lists',$carrier_lists);
        }

        $this->authorize('checkout', $asset);

        if ($asset->availableForCheckout()) {
            return view('hardware/checkout', compact('asset'))
                ->with('statusLabel_list', Helper::deployableStatusLabelListDeliveredIndvCheckout())
                ->with('locations',$locations)
                ->with('logged_in_user_location_id',$current_user_location_id)
                ->with('current_user_location_name',$current_user_location_name)
                ->with('carrier_lists',$carrier_lists);
        }
        return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkout.not_available'))
        ->with('locations',$locations)
        ->with('logged_in_user_location_id',$current_user_location_id)
        ->with('current_user_location_name',$current_user_location_name)
        ->with('carrier_lists',$carrier_lists);
    }

    /**
     * Validate and process the form data to check out an asset to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param AssetCheckoutRequest $request
     * @param int $assetId
     * @return Redirect
     * @since [v1.0]
     */
    public function store(AssetCheckoutRequest $request, $assetId)
    {
        try {
            // Check if the asset exists
            if (!$asset = Asset::find($assetId)) {
                return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
            } elseif (!$asset->availableForCheckout()) {
                return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkout.not_available'));
            }
            $this->authorize('checkout', $asset);
            $admin = Auth::user();
            $current_user_id = Auth::user()->id;
            $target = $this->determineCheckoutTarget($asset);
            $status_id = $request->get('status_id');
            if($status_id != 6){
                $asset = $this->updateAssetLocation($asset, $target);
            }

            $checkout_at = date("Y-m-d H:i:s");
            if (($request->filled('checkout_at')) && ($request->get('checkout_at')!= date("Y-m-d"))) {
                $checkout_at = $request->get('checkout_at');
            }

            $expected_checkin = '';
            if ($request->filled('expected_checkin')) {
                $expected_checkin = $request->get('expected_checkin');
            }

            if ($request->filled('status_id')) {
                $asset->status_id = $request->get('status_id');
            }

            $shipment_number = $request->get('shipment_number');
            $barcode_number = $request->get('barcode_number');

            if($status_id == 6){
                $asset->checkout_location_cstm = $input_location_id = $request->get('assigned_location');
                $sql_1 = "SELECT company_id FROM users WHERE location_id = $input_location_id 
                        AND manager_id is NULL AND activated = 1 and id != '1'";
                $query_1 = DB::select($sql_1);
                $company_id = $query_1[0]->company_id;
                $asset->company_id = $company_id;
            }

            //$status_id = 9 => Third party transfer
            $cscart_carrier_id = null;
            if($status_id == '9'){
                $carrier_id = $request->get('carrier_id');
                $asset->carrier_id = $carrier_id;
                $mapping_lists = CarrierMappingModel::where('carrier_id',$carrier_id)->get();
                $cscart_carrier_id = $mapping_lists[0]->cscart_carrier_id;
            }

            if($status_id == 6){
                if ($asset->checkOut($target, $admin, $checkout_at, $expected_checkin, e($request->get('note')), $request->get('name'))) {
                    $sync_message = '';
                    if(isset($status_id) && isset($shipment_number)){
                        $sync_message = StatusMapping::syncShipmentStatus($shipment_number, $status_id);
                    }
                    //update status and action date in action log table
                    if(env('APP_URL') == 'http://127.0.0.1:8000/'){
                        $sql_2 = "SELECT now() as currentdatetime";
                        $query_2 = DB::select($sql_2);
                        $action_date = $query_2[0]->currentdatetime;
                    }
                    else{
                        $action_date = date('Y-m-d H:i:s');
                    }
                    $action_id = DB::table('action_logs')->where('action_logs.user_id', '=', $current_user_id)->max('id');
                    $action_log = Actionlog::find($action_id);
                    $action_log->status_id = $status_id;
                    $action_log->action_date = $action_date;
                    $action_log->save();
                    //end update status in action log table
                    return redirect()->route("hardware.index")->with('success', trans('admin/hardware/message.checkout.success'))->with('message', $sync_message);
                }
            }
            else{
                $response_message = '';
                if ($asset->checkOut($target, $admin, $checkout_at, $expected_checkin, e($request->get('note')), $request->get('name'))) {
                    $sync_message = '';
                    if(isset($status_id) && isset($shipment_number)){
                        $sync_message = StatusMapping::syncShipmentStatus($shipment_number, $status_id);
                    }
                    if(isset($shipment_number) && isset($cscart_carrier_id) && $cscart_carrier_id == env('CSCART_NCM_CARRIER')){
                        $response_message = CarrierMappingModel::syncCscartShipment($shipment_number, env('CSCART_NCM_CARRIER'));
                    }
                    //update status and action date in action log table
                    if(env('APP_URL') == 'http://127.0.0.1:8000/'){
                        $sql_2 = "SELECT now() as currentdatetime";
                        $query_2 = DB::select($sql_2);
                        $action_date = $query_2[0]->currentdatetime;
                    }
                    else{
                        $action_date = date('Y-m-d H:i:s');
                    }
                    $action_id = DB::table('action_logs')->where('action_logs.user_id', '=', $current_user_id)->max('id');
                    $action_log = Actionlog::find($action_id);
                    $action_log->status_id = $status_id;
                    $action_log->action_date = $action_date;
                    $action_log->save();
                    //end update status and action date  in action log table
                    return redirect()->route("hardware.index")->with('success', trans('admin/hardware/message.checkout.success'))->with('message', $sync_message)->with('notification', $response_message);
                }
            }

            // Redirect to the asset management page with error
            return redirect()->to("hardware/$assetId/checkout")->with('error', trans('admin/hardware/message.checkout.error').$asset->getErrors());
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', trans('admin/hardware/message.checkout.error'))->withErrors($asset->getErrors());
        } catch (CheckoutNotAllowed $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
    * Return Asset Data.
    * @author prabin.thapa
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function showAssetData(Request $request)
    {
        $shipment_id = $request->shipment_id;
        $asset_details = Asset::where('asset_tag',$shipment_id)->get();
        $asset = $asset_details[0];
        $asset_id = $asset->id;
        $model_id = $asset->model_id;
        $product_name = $asset->name;
        $supplier_id = $asset->supplier_id;
        $order_number = $asset->order_number;
        $quantity = $asset->quantity;
        $json = array(
            'model_id' => $model_id,
            'asset_id' => $asset_id,
            'product_name' => $product_name,
            'supplier_id' => $supplier_id,
            'order_number' => $order_number,
            'quantity' => $quantity
        );
        return response()->json($json);
    }
    
    /**
    * Show Individual Checkout page.
    *
    * @author prabinthapamagar
    * @since [v1.0]
    * @return View
    */
    public function createIndividual()
    {
        $locations = Location::get();
        $current_user_id = Auth::user()->id;
        $current_user_location_id = Auth::user()->location_id;
        $carrier_lists = CarrierModel::get();
        return view('hardware/individual-checkout')
                ->with('statusLabel_list', Helper::deployableStatusLabelListDeliveredIndvCheckout())
                ->with('locations', $locations)
                ->with('logged_in_user_location_id', $current_user_location_id)
                ->with('carrier_lists', $carrier_lists);
    }

    /**
     * Save Individual Checkout entry.
     *
     * @author prabinthapamagar
     * @param AssetCheckoutRequest $request
     * @return Redirect
     * @since [v1.0]
     */
    public function storeIndividual(AssetCheckoutRequest $request)
    {
        try {
            // Check if the asset exists
            $assetId = $request->get('asset_id');
            if (!$asset = Asset::find($assetId)) {
                return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
            } elseif (!$asset->availableForCheckout()) {
                return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkout.not_available'));
            }
            $this->authorize('checkout', $asset);
            $admin = Auth::user();
            $current_user_id = Auth::id();
            $target = $this->determineCheckoutTarget($asset);
            $status_id = $request->get('status_id');
            if($status_id != 6){
                $asset = $this->updateAssetLocation($asset, $target);
            }

            $checkout_at = date("Y-m-d H:i:s");
            if (($request->filled('checkout_at')) && ($request->get('checkout_at')!= date("Y-m-d"))) {
                $checkout_at = $request->get('checkout_at');
            }

            $expected_checkin = '';
            if ($request->filled('expected_checkin')) {
                $expected_checkin = $request->get('expected_checkin');
            }

            if ($request->filled('status_id')) {
                $asset->status_id = $request->get('status_id');
            }
            
            if($status_id == 6){
                $asset->checkout_location_cstm = $input_location_id = $request->get('assigned_location');
                $sql_1 = "SELECT company_id FROM users WHERE location_id = $input_location_id 
                        AND manager_id is NULL AND activated = 1 and id != '1'";
                $query_1 = DB::select($sql_1);
                if(!empty($query_1)){
                    $company_id = $query_1[0]->company_id;
                    $asset->company_id = $company_id;
                }
            }
            $shipment_number = $request->get('shipment_number');
            $carrier_id = $cscart_carrier_id = null;
            //$status_id = 9 => Third party transfer
            if($status_id == '9'){
                $carrier_id = $request->get('carrier_id');
                $asset->carrier_id = $carrier_id;
                $mapping_lists = CarrierMappingModel::where('carrier_id',$carrier_id)->get();
                $cscart_carrier_id = $mapping_lists[0]->cscart_carrier_id;
            }

            if($status_id == 6){
                if ($asset->checkOutIndividual($target, $admin, $checkout_at, $expected_checkin, e($request->get('note')), $request->get('name'))) {
                    $sync_message = $response_message = '';
                    if(isset($status_id) && isset($shipment_number)){
                        $sync_message = StatusMapping::syncShipmentStatus($shipment_number, $status_id);
                    }
                    if(isset($cscart_carrier_id) && $cscart_carrier_id == env('CSCART_NCM_CARRIER')){
                        $response_message = CarrierMappingModel::syncCscartShipment($shipment_number, env('CSCART_NCM_CARRIER'));
                    }
                    //update status and action date in action log table
                    if(env('APP_URL') == 'http://127.0.0.1:8000/'){
                        $sql_2 = "SELECT now() as currentdatetime";
                        $query_2 = DB::select($sql_2);
                        $action_date = $query_2[0]->currentdatetime;
                    }
                    else{
                        $action_date = date('Y-m-d H:i:s');
                    }
                    $action_id = DB::table('action_logs')->where('action_logs.user_id', '=', $current_user_id)->max('id');
                    $action_log = Actionlog::find($action_id);
                    $action_log->status_id = $status_id;
                    $action_log->action_date = $action_date;
                    $action_log->save();
                    //end update status and action date  in action log table
                    return redirect()->route("hardware.index")->with('success', trans('admin/hardware/message.checkout.success'))->with('message', $sync_message)->with('message', $response_message);;
                }
            }
            else{
                if ($asset->checkOut($target, $admin, $checkout_at, $expected_checkin, e($request->get('note')), $request->get('name'))) {
                    $sync_message = $response_message = '';
                    if(isset($status_id) && isset($shipment_number)){
                        $sync_message = StatusMapping::syncShipmentStatus($shipment_number, $status_id);
                    }
                    if(isset($cscart_carrier_id) && $cscart_carrier_id == env('CSCART_NCM_CARRIER')){
                        $response_message = CarrierMappingModel::syncCscartShipment($shipment_number, env('CSCART_NCM_CARRIER'));
                    }
                    //update status and action date in action log table
                    if(env('APP_URL') == 'http://127.0.0.1:8000/'){
                        $sql_2 = "SELECT now() as currentdatetime";
                        $query_2 = DB::select($sql_2);
                        $action_date = $query_2[0]->currentdatetime;
                    }
                    else{
                        $action_date = date('Y-m-d H:i:s');
                    }
                    $action_id = DB::table('action_logs')->where('action_logs.user_id', '=', $current_user_id)->max('id');
                    $action_log = Actionlog::find($action_id);
                    $action_log->status_id = $status_id;
                    $action_log->action_date = $action_date;
                    $action_log->save();
                    //end update status in action log table
                    return redirect()->route("hardware.index")->with('success', trans('admin/hardware/message.checkout.success'))->with('message', $sync_message)->with('notification', $response_message);
                }
            }

            // Redirect to the asset management page with error
            return redirect()->to("hardware/$assetId/checkout")->with('error', trans('admin/hardware/message.checkout.error').$asset->getErrors());
        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', trans('admin/hardware/message.checkout.error'))->withErrors($asset->getErrors());
        } catch (CheckoutNotAllowed $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
    * Return Asset Data.
    * @author prabin.thapa
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function showBarCodeData(Request $request)
    {
        $barcode_number = $request->barcode_number;
        $barcode_number = trim($barcode_number);
        $sql_1 = "SELECT * from assets where barcode_number = '$barcode_number' and deleted_at is null";
        $asset_details = DB::select($sql_1);
        if(!empty($asset_details)){
            $asset_tag = $asset_details[0]->asset_tag;
            $assetId = $asset_details[0]->id;
            $asset = Asset::find($assetId);
            if(!empty($asset_tag)){
                $json = array(
                    'status' => 'error',
                    'message' => 'Shipment Number '.$barcode_number.' has product tag.Please Checkout from Bulk Tag Checkout'
                );
            }
            elseif (!$asset->availableForCheckout()) {
                $json = array(
                    'status' => 'error',
                    'message' => 'Shipment Number '.$barcode_number.' not available for Checkout'
                );
            }
            else{
                $asset = $asset_details[0];
                $asset_id = $asset->id;
                $asset_check = Asset::find($asset_id);
                $company_id = $asset->company_id;
                $user_details = Auth::user();
                $user_id = $user_details->id;
                if($user_id == 1){
                    $model_id = $asset->model_id;
                    $product_name = $asset->name;
                    $supplier_id = $asset->supplier_id;
                    $order_number = $asset->order_number;
                    $quantity = $asset->quantity;
                    $shipment_number = $asset->shipment_number;
                    $json = array(
                        'status' => 'success',
                        'message' => 'Valid Shipment Number',
                        'model_id' => $model_id,
                        'asset_id' => $asset_id,
                        'product_name' => $product_name,
                        'supplier_id' => $supplier_id,
                        'order_number' => $order_number,
                        'shipment_number' => $shipment_number,
                        'quantity' => $quantity
                    );
                }
                else{
                    $logged_in_user_company_id = $user_details->company_id;
                    if($company_id == $logged_in_user_company_id){
                        $model_id = $asset->model_id;
                        $product_name = $asset->name;
                        $supplier_id = $asset->supplier_id;
                        $order_number = $asset->order_number;
                        $quantity = $asset->quantity;
                        $shipment_number = $asset->shipment_number;
                        $json = array(
                            'status' => 'success',
                            'message' => 'Valid Shipment Number',
                            'model_id' => $model_id,
                            'asset_id' => $asset_id,
                            'product_name' => $product_name,
                            'supplier_id' => $supplier_id,
                            'order_number' => $order_number,
                            'shipment_number' => $shipment_number,
                            'quantity' => $quantity
                        );
                    }
                    else{
                        $json = array(
                            'status' => 'error',
                            'message' => 'Shipment Number '.$barcode_number.' not allowed for checkout'
                        );
                    }
                }
            }
        }
        else{
            $json = array(
                'status' => 'error',
                'message' => 'Invalid Shipment Number '.$barcode_number.''
            );
        }
        return response()->json($json);
    }

    /**
    * Return Asset Data.
    * @author prabin.thapa
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function showAssetInfoByShipmentNumber(Request $request)
    {
        $shipment_number = $request->shipment_number;
        $asset_details = Asset::where('shipment_number',$shipment_number)->get();
        $asset = $asset_details[0];
        $asset_id = $asset->id;
        $model_id = $asset->model_id;
        $product_name = $asset->name;
        $supplier_id = $asset->supplier_id;
        $order_number = $asset->order_number;
        $quantity = $asset->quantity;
        $json = array(
            'model_id' => $model_id,
            'asset_id' => $asset_id,
            'product_name' => $product_name,
            'supplier_id' => $supplier_id,
            'order_number' => $order_number,
            'quantity' => $quantity
        );
        return response()->json($json);
    }

    /**
    * Check Bar Code Number Exist.
    * @author prabin.thapa
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function checkBarCodeNumberExist(Request $request)
    {
        $barcode_number = $request->barcode_number;
        $barcode_number = trim($barcode_number);
        $sql_1 = "SELECT * from assets where barcode_number = '$barcode_number' and deleted_at is null";
        $asset_details = DB::select($sql_1);
        if(!empty($asset_details)){
            $asset_tag = $asset_details[0]->asset_tag;
            $assetId = $asset_details[0]->id;
            $asset = Asset::find($assetId);
            if(!empty($asset_tag)){
                $status = 'error';
                $message = 'Shipment Number '.$barcode_number.' has product tag.Please Checkout from Bulk Tag Checkout';
                $asset_id = $category_name = $product_name = $vendor_name = $quantity = $order_number = $shipment_number = $customer_firstname = $customer_lastname = $customer_phoneno = $customer_address = null;    
            }
            else{
                if (!$asset->availableForCheckout()) {
                    $status = 'error';
                    $message = 'Shipment Number '.$barcode_number.' not available for checkout.';
                    $asset_id = $category_name = $product_name = $vendor_name = $quantity = $order_number = $shipment_number = $customer_firstname = $customer_lastname = $customer_phoneno = $customer_address = null;
                }
                else{
                    $status = $message = 'success';
                    $asset = $asset_details[0];
                    $asset_id = $asset->id;
                    $company_id = $asset->company_id;
                    $user_details = Auth::user();
                    $user_id = $user_details->id;
                    if($user_id == 1){
                        $category_name = $asset->category_name;
                        $product_name_val = $asset->name;
                        $product_name_replace = htmlspecialchars($product_name_val, ENT_QUOTES, 'UTF-8');
                        $product_name = $product_name_replace;
                        $vendor_id = $asset->supplier_id;
                        $sql_2 = "SELECT * from suppliers where id = $vendor_id";
                        $query_2 = DB::select($sql_2);
                        foreach($query_2 as $val_2){
                            $vendor_name = $val_2->name;
                        }
                        $quantity = $asset->quantity;
                        $order_number = $asset->order_number;
                        $shipment_number = $asset->shipment_number;
                        $customer_firstname = $asset->customer_firstname;
                        $customer_lastname = $asset->customer_lastname;
                        $customer_phoneno = $asset->customer_phoneno;
                        $customer_address = $asset->customer_address;
                    }
                    else{
                        $logged_in_user_company_id = $user_details->company_id;
                        if($company_id == $logged_in_user_company_id){
                            $category_name = $asset->category_name;
                            $product_name_val = $asset->name;
                            $product_name_replace = htmlspecialchars($product_name_val, ENT_QUOTES, 'UTF-8');
                            $product_name = $product_name_replace;
                            $vendor_id = $asset->supplier_id;
                            $sql_2 = "SELECT * from suppliers where id = $vendor_id";
                            $query_2 = DB::select($sql_2);
                            foreach($query_2 as $val_2){
                                $vendor_name = $val_2->name;
                            }
                            $quantity = $asset->quantity;
                            $order_number = $asset->order_number;
                            $shipment_number = $asset->shipment_number;
                            $customer_firstname = $asset->customer_firstname;
                            $customer_lastname = $asset->customer_lastname;
                            $customer_phoneno = $asset->customer_phoneno;
                            $customer_address = $asset->customer_address;
                        }
                        else{
                            $status = 'error';
                            $message = 'Shipment Number '.$barcode_number.' not allowed for checkout';
                            $asset_id = $category_name = $product_name = $vendor_name = $quantity = $order_number = $shipment_number = $customer_firstname = $customer_lastname = $customer_phoneno = $customer_address = null;
                        }
                    }
                }
            }
        }
        else{
            $status = 'error';
            $message = 'Invalid Barcode Number '.$barcode_number.'';
            $asset_id = $category_name = $product_name = $vendor_name = $quantity = $order_number = $shipment_number = $customer_firstname = $customer_lastname = $customer_phoneno = $customer_address = null;
        }
        $json = array(
            'status' => $status,
            'message' => $message,
            'barcode_number' => $barcode_number,
            'asset_id' => $asset_id,
            'category_name' => $category_name,
            'product_name' => $product_name,
            'vendor_name' => $vendor_name,
            'order_number' => $order_number,
            'shipment_number' => $shipment_number,
            'quantity' => $quantity,
            'customer_firstname' => $customer_firstname,
            'customer_lastname' => $customer_lastname,
            'customer_phoneno' => $customer_phoneno,
            'customer_address' => $customer_address,
        );
        return response()->json($json);
    }

    /**
    * Show Bar Code data for hub check in.
    * @author prabin.thapa
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function showBarCodeInfoHubCheckin(Request $request)
    {
        $barcode_number = $request->barcode_number;
        $barcode_number = trim($barcode_number);
        $sql_1 = "SELECT * from assets where barcode_number = '$barcode_number' and deleted_at is null";
        $asset_details = DB::select($sql_1);
        if(!empty($asset_details)){
            $status = $message = 'success';
            $asset_details = $asset_details[0];
            $asset_id = $asset_details->id;
            $asset = Asset::find($asset_id);
            if(!is_null($asset)){
                if (is_null($target = $asset->assignedTo)) {
                    $status = 'error';
                    $message = 'Already Checked In. Please Checkout First.';
                    $category_name = $product_name = $vendor_name = $order_number = $quantity = $shipment_number = $customer_firstname = $customer_lastname = $customer_phoneno = $customer_address = null;
                }
                else{
                    $category_name = $asset_details->category_name;
                    $product_name = $asset_details->name;
                    $vendor_id = $asset_details->supplier_id;
                    $sql_2 = "SELECT * from suppliers where id = $vendor_id";
                    $query_2 = DB::select($sql_2);
                    foreach($query_2 as $val_2){
                        $vendor_name = $val_2->name;
                    }
                    $order_number = $asset_details->order_number;
                    $shipment_number = $asset_details->shipment_number;
                    $quantity = $asset_details->quantity;
                    $order_number = $asset->order_number;
                    $shipment_number = $asset->shipment_number;
                    $customer_firstname = $asset->customer_firstname;
                    $customer_lastname = $asset->customer_lastname;
                    $customer_phoneno = $asset->customer_phoneno;
                    $customer_address = $asset->customer_address;
                }
            }
            else{
                $company_id = $asset_details->company_id;
                $user_details = Auth::user();
                $user_id = $user_details->id;
                if($user_id == 1){
                    $category_name = $asset_details->category_name;
                    $product_name = $asset_details->name;
                    $vendor_id = $asset_details->supplier_id;
                    $sql_2 = "SELECT * from suppliers where id = $vendor_id";
                    $query_2 = DB::select($sql_2);
                    foreach($query_2 as $val_2){
                        $vendor_name = $val_2->name;
                    }
                    $order_number = $asset_details->order_number;
                    $shipment_number = $asset_details->shipment_number;
                    $quantity = $asset_details->quantity;
                    $customer_firstname = $asset_details->customer_firstname;
                    $customer_lastname = $asset_details->customer_lastname;
                    $customer_phoneno = $asset_details->customer_phoneno;
                    $customer_address = $asset_details->customer_address;
                }
                else{
                    $logged_in_user_company_id = $user_details->company_id;
                    if($company_id == $logged_in_user_company_id){
                        $category_name = $asset_details->category_name;
                        $product_name = $asset_details->name;
                        $vendor_id = $asset_details->supplier_id;
                        $sql_2 = "SELECT * from suppliers where id = $vendor_id";
                        $query_2 = DB::select($sql_2);
                        foreach($query_2 as $val_2){
                            $vendor_name = $val_2->name;
                        }
                        $quantity = $asset_details->quantity;
                        $order_number = $asset_details->order_number;
                        $shipment_number = $asset_details->shipment_number;
                        $customer_firstname = $asset_details->customer_firstname;
                        $customer_lastname = $asset_details->customer_lastname;
                        $customer_phoneno = $asset_details->customer_phoneno;
                        $customer_address = $asset_details->customer_address;
                    }
                    else{
                        $status = 'error';
                        $message = 'Shipment Number '.$barcode_number.' not allowed for Hub Checkin';
                        $asset_id = $category_name = $product_name = $vendor_name = $quantity = $order_number = $shipment_number = $customer_firstname = $customer_lastname = $customer_phoneno = $customer_address = null;
                    }
                }
            }
        }
        else{
            $status = 'error';
            $message = 'Invalid Barcode Number '.$barcode_number.'';
            $asset_id = $category_name = $product_name = $vendor_name = $quantity = $order_number = $shipment_number = $customer_firstname = $customer_lastname = $customer_phoneno = $customer_address = null;
        }
        $json = array(
            'status' => $status,
            'message' => $message,
            'asset_id' => $asset_id,
            'category_name' => $category_name,
            'product_name' => $product_name,
            'vendor_name' => $vendor_name,
            'order_number' => $order_number,
            'shipment_number' => $shipment_number,
            'quantity' => $quantity,
            'customer_firstname' => $customer_firstname,
            'customer_lastname' => $customer_lastname,
            'customer_phoneno' => $customer_phoneno,
            'customer_address' => $customer_address,
        );
        return response()->json($json);
    }

    /**
    * Get the Bar Code Details For Bulk Third Party Checkout.
    * @author prabinthapamagar
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function showBarCodeNumberForThirdParty(Request $request)
    {
        $barcode_number = $request->barcode_number;
        $barcode_number = trim($barcode_number);
        $asset_details = Asset::where('barcode_number',$barcode_number)->get();
        if(!empty($asset_details)){
            $status = $message = 'success';
            $asset = $asset_details[0];
            $asset_id = $asset->id;
            $company_id = $asset->company_id;
            $user_details = Auth::user();
            $user_id = $user_details->id;
            if($user_id == 1){
                $category_name = $asset->category_name;
                $product_name = $asset->name;
                $vendor_id = $asset->supplier_id;
                $sql_2 = "SELECT * from suppliers where id = $vendor_id";
                $query_2 = DB::select($sql_2);
                foreach($query_2 as $val_2){
                    $vendor_name = $val_2->name;
                }
                $quantity = $asset->quantity;
                $order_number = $asset->order_number;
                $shipment_number = $asset->shipment_number;
            }
            else{
                $logged_in_user_company_id = $user_details->company_id;
                if($company_id == $logged_in_user_company_id){
                    $category_name = $asset->category_name;
                    $product_name = $asset->name;
                    $vendor_id = $asset->supplier_id;
                    $sql_2 = "SELECT * from suppliers where id = $vendor_id";
                    $query_2 = DB::select($sql_2);
                    foreach($query_2 as $val_2){
                        $vendor_name = $val_2->name;
                    }
                    $quantity = $asset->quantity;
                    $order_number = $asset->order_number;
                    $shipment_number = $asset->shipment_number;
                }
                else{
                    $status = 'error';
                    $message = 'Shipment Number '.$barcode_number.' not allowed for checkout';
                    $asset_id = $category_name = $product_name = $vendor_name = $quantity = $order_number = $shipment_number = null;
                }
            }
        }
        else{
            $status = 'error';
            $message = 'Invalid Barcode Number '.$barcode_number.'';
            $asset_id = $category_name = $product_name = $vendor_name = $quantity = $order_number = $shipment_number = null;
        }
        $json = array(
            'status' => $status,
            'message' => $message,
            'asset_id' => $asset_id,
            'category_name' => $category_name,
            'product_name' => $product_name,
            'vendor_name' => $vendor_name,
            'order_number' => $order_number,
            'shipment_number' => $shipment_number,
            'quantity' => $quantity
        );
        return response()->json($json);
    }

    public function syncCscartShipment($shipment_id, $cscart_carrier_id){
        $ApiRequest = new ApiRequest();
        $response = $ApiRequest->apiCall('PUT', 'shipments/'.$shipment_id, ['carrier' => 'nepal_can_move','external_sync'=> true]);
        return isset($response['sync'][1])? $response['sync'][1]: 'Error';
    }

    /**
    * Check Bar Code Number Exists In First Time Check In Form.
    * @author prabin.thapa
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function checkBarCodeNumberExistFirstTimeCheckIn(Request $request)
    {
        $barcode_number = $request->barcode_number;
        $barcode_number = trim($barcode_number);
        $sql_1 = "SELECT * from assets where barcode_number = '$barcode_number' and deleted_at is null";
        $asset_details = DB::select($sql_1);
        if(!empty($asset_details)){
            $status = 'error';
            $message = 'Barcode Number Already exists!';
        }
        else{
            $status = 'success';
            $message = 'Valid Barcode Number '.$barcode_number.'';
        }
        $json = array(
            'status' => $status,
            'message' => $message,
            'barcode_number' => $barcode_number
        );
        return response()->json($json);
    }

    /**
    * Check Asset Tag Exists.
    * @author prabin.thapa
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function checkAssetTagExist(Request $request)
    {
        $asset_tag = $request->asset_tag;
        $asset_tag = trim($asset_tag);
        $sql_1 = "SELECT * from assets where asset_tag = '$asset_tag' and deleted_at is null";
        $asset_details = DB::select($sql_1);
        $tr_html = null;
        if(!empty($asset_details)){
            $status = $message = 'success';
            foreach($asset_details as $asset){
                $asset_id = $asset->id;
                $asset_check = Asset::find($asset_id);
                if (!$asset_check->availableForCheckout()) {
                    $status = 'error';
                    $message = 'Shipment Tag '.$asset_tag.' Not available for checkout';
                    $asset_id = $category_name = $product_name = $vendor_name = $quantity = $order_number = $shipment_number = $customer_firstname = $customer_lastname = $customer_phoneno = $customer_address = null;
                }
                else{
                    $company_id = $asset->company_id;
                    $user_details = Auth::user();
                    $user_id = $user_details->id;
                    if($user_id == 1){
                        $category_name = $asset->category_name;
                        $product_name_val = $asset->name;
                        $product_name_replace = htmlspecialchars($product_name_val, ENT_QUOTES, 'UTF-8');
                        $product_name = $product_name_replace;
                        $vendor_id = $asset->supplier_id;
                        $sql_2 = "SELECT * from suppliers where id = $vendor_id";
                        $query_2 = DB::select($sql_2);
                        foreach($query_2 as $val_2){
                            $vendor_name = $val_2->name;
                        }
                        $quantity = $asset->quantity;
                        $order_number = $asset->order_number;
                        $shipment_number = $asset->shipment_number;
                        $barcode_number = $asset->barcode_number;
                        $customer_firstname = $asset->customer_firstname;
                        $customer_lastname = $asset->customer_lastname;
                        $customer_fullname = $customer_firstname.' '.$customer_lastname;
                        $customer_phoneno = $asset->customer_phoneno;
                        $customer_address = $asset->customer_address;
                        $tr_html .= '<tr style="display:none;">';
                        $tr_html .= '<td>';
                        $tr_html .= '<input type="text" name="asset_tag[]" class="form-control" value="'.$asset_tag.'" readonly>';
                        $tr_html .= '</td>';
                        $tr_html .= '</tr>';
                        $tr_html .= '<tr>';
                        $tr_html .= '<td>';
                        $tr_html .= '<input type="text" name="asset_tag_read[]" class="form-control asset_tag_read" value="'.$asset_tag.'" readonly>';
                        $tr_html .= '</td>';
                        $tr_html .= '<td>';
                        $tr_html .= '<input type="hidden" name="asset_id[]" class="form-control" value="'.$asset_id.'">';
                        $tr_html .= '<input type="hidden" name="shipment_number[]" class="form-control shipment_number" value="'.$shipment_number.'">';
                        $tr_html .= '<input type="text" name="barcode_number_val[]" class="form-control barcode_number_val" value="'.$barcode_number.'">';
                        $tr_html .= '</td>';
                        $tr_html .= '<td>';
                        $tr_html .= '<input type="text" name="category_name[]" class="form-control" value="'.$category_name.'" readonly>';
                        $tr_html .= '</td>';
                        $tr_html .= '<td>';
                        $tr_html .= '<input type="text" name="product_name[]" class="form-control product_name" value="'.$product_name.'" readonly>';
                        $tr_html .= '</td>';
                        $tr_html .= '<td>';
                        $tr_html .= '<input type="text" name="vendor_name[]" class="form-control vendor_name" value="'.$vendor_name.'" readonly>';
                        $tr_html .= '</td>';
                        $tr_html .= '<td>';
                        $tr_html .= '<input type="text" name="order_number[]" class="form-control order_number" value="'.$order_number.'" readonly>';
                        $tr_html .= '</td>';
                        $tr_html .= '<td>';
                        $tr_html .= '<input type="text" name="quantity[]" class="form-control quantity" value="'.$quantity.'" readonly>';
                        $tr_html .= '</td>';
                        $tr_html .= '<td>';
                        $tr_html .= '<input type="hidden" name="customer_firstname[]" class="form-control customer_firstname" value="'.$customer_firstname.'" readonly>';
                        $tr_html .= '<input type="hidden" name="customer_lastname[]" class="form-control customer_lastname" value="'.$customer_lastname.'" readonly>';
                        $tr_html .= '<input type="text" name="customer_fullname[]" class="form-control customer_fullname" value="'.$customer_fullname.'" readonly>';
                        $tr_html .= '</td>';
                        $tr_html .= '<td>';
                        $tr_html .= '<input type="text" name="customer_phoneno[]" class="form-control customer_phoneno" value="'.$customer_phoneno.'" readonly>';
                        $tr_html .= '</td>';
                        $tr_html .= '<td>';
                        $tr_html .= '<input type="text" name="customer_address[]" class="form-control customer_address" value="'.$customer_address.'" readonly>';
                        $tr_html .= '</td>';
                        $tr_html .= '</tr>';
                    }
                    else{
                        $logged_in_user_company_id = $user_details->company_id;
                        if($company_id == $logged_in_user_company_id){
                            $category_name = $asset->category_name;
                            $product_name_val = $asset->name;
                            $product_name_replace = htmlspecialchars($product_name_val, ENT_QUOTES, 'UTF-8');
                            $product_name = $product_name_replace;
                            $vendor_id = $asset->supplier_id;
                            $sql_2 = "SELECT * from suppliers where id = $vendor_id";
                            $query_2 = DB::select($sql_2);
                            foreach($query_2 as $val_2){
                                $vendor_name = $val_2->name;
                            }
                            $quantity = $asset->quantity;
                            $order_number = $asset->order_number;
                            $shipment_number = $asset->shipment_number;
                            $barcode_number = $asset->barcode_number;
                            $customer_firstname = $asset->customer_firstname;
                            $customer_lastname = $asset->customer_lastname;
                            $customer_fullname = $customer_firstname.' '.$customer_lastname;
                            $customer_phoneno = $asset->customer_phoneno;
                            $customer_address = $asset->customer_address;
                            $tr_html .= '<tr style="display:none;">';
                            $tr_html .= '<td>';
                            $tr_html .= '<input type="text" name="asset_tag[]" class="form-control" value="'.$asset_tag.'" readonly>';
                            $tr_html .= '</td>';
                            $tr_html .= '</tr>';
                            $tr_html .= '<tr>';
                            $tr_html .= '<td>';
                            $tr_html .= '<input type="text" name="asset_tag_read[]" class="form-control asset_tag_read" value="'.$asset_tag.'" readonly>';
                            $tr_html .= '</td>';
                            $tr_html .= '<td>';
                            $tr_html .= '<input type="hidden" name="asset_id[]" class="form-control" value="'.$asset_id.'">';
                            $tr_html .= '<input type="hidden" name="shipment_number[]" class="form-control shipment_number" value="'.$shipment_number.'">';
                            $tr_html .= '<input type="text" name="barcode_number_val[]" class="form-control barcode_number_val" value="'.$barcode_number.'">';
                            $tr_html .= '</td>';
                            $tr_html .= '<td>';
                            $tr_html .= '<input type="text" name="category_name[]" class="form-control" value="'.$category_name.'" readonly>';
                            $tr_html .= '</td>';
                            $tr_html .= '<td>';
                            $tr_html .= '<input type="text" name="product_name[]" class="form-control product_name" value="'.$product_name.'" readonly>';
                            $tr_html .= '</td>';
                            $tr_html .= '<td>';
                            $tr_html .= '<input type="text" name="vendor_name[]" class="form-control vendor_name" value="'.$vendor_name.'" readonly>';
                            $tr_html .= '</td>';
                            $tr_html .= '<td>';
                            $tr_html .= '<input type="text" name="order_number[]" class="form-control order_number" value="'.$order_number.'" readonly>';
                            $tr_html .= '</td>';
                            $tr_html .= '<td>';
                            $tr_html .= '<input type="text" name="quantity[]" class="form-control quantity" value="'.$quantity.'" readonly>';
                            $tr_html .= '</td>';
                            $tr_html .= '<td>';
                            $tr_html .= '<input type="hidden" name="customer_firstname[]" class="form-control customer_firstname" value="'.$customer_firstname.'" readonly>';
                            $tr_html .= '<input type="hidden" name="customer_lastname[]" class="form-control customer_lastname" value="'.$customer_lastname.'" readonly>';
                            $tr_html .= '<input type="text" name="customer_fullname[]" class="form-control customer_fullname" value="'.$customer_fullname.'" readonly>';
                            $tr_html .= '</td>';
                            $tr_html .= '<td>';
                            $tr_html .= '<input type="text" name="customer_phoneno[]" class="form-control customer_phoneno" value="'.$customer_phoneno.'" readonly>';
                            $tr_html .= '</td>';
                            $tr_html .= '<td>';
                            $tr_html .= '<input type="text" name="customer_address[]" class="form-control customer_address" value="'.$customer_address.'" readonly>';
                            $tr_html .= '</td>';
                            $tr_html .= '</tr>';
                        }
                        else{
                            $status = 'error';
                            $message = 'Shipment Number '.$asset_tag.' not allowed for checkout';
                            $asset_id = $category_name = $product_name = $vendor_name = $quantity = $order_number = $shipment_number = $customer_firstname = $customer_lastname = $customer_phoneno = $customer_address = null;
                        }
                    }
                }
            }
        }
        else{
            $status = 'error';
            $message = 'Invalid Asset Tag '.$asset_tag.'';
            $asset_id = $category_name = $product_name = $vendor_name = $quantity = $order_number = $shipment_number = $customer_firstname = $customer_lastname = $customer_phoneno = $customer_address = null;
        }
        $json = array(
            'status' => $status,
            'message' => $message,
            'asset_tag' => $asset_tag,
            'tr_html' => $tr_html
        );
        return response()->json($json);
    }
}
