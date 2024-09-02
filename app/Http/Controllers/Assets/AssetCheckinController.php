<?php

namespace App\Http\Controllers\Assets;

use App\Events\CheckoutableCheckedIn;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCheckinRequest;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ApiRequest;
use App\Models\StatusMapping;
use App\Models\Actionlog;

class AssetCheckinController extends Controller
{

    /**
     * Returns a view that presents a form to check an asset back into inventory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @param string $backto
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @since [v1.0]
     */
    public function create($assetId, $backto = null)
    {
        // Check if the asset exists
        if (is_null($asset = Asset::find($assetId))) {
            // Redirect to the asset management page with error
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        $this->authorize('checkin', $asset);
        $sql_1 = "SELECT * from locations";
        $query_1 = DB::select($sql_1);
        $locations = $query_1;
        $current_user_location_id = Auth::user()->location_id;
        return view('hardware/checkin', compact('asset'))->with('statusLabel_list', Helper::deployableStatusLabelListCheckin())->with('backto', $backto)->with('locations', $locations)->with('current_user_location_id', $current_user_location_id);
    }

    /**
     * Validate and process the form data to check an asset back into inventory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param AssetCheckinRequest $request
     * @param int $assetId
     * @param null $backto
     * @return Redirect
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @since [v1.0]
     */
    public function store(AssetCheckinRequest $request, $assetId = null, $backto = null)
    {
        // Check if the asset exists
        if (is_null($asset = Asset::find($assetId))) {
            // Redirect to the asset management page with error
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        if (is_null($target = $asset->assignedTo)) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkin.already_checked_in'));
        }
        $this->authorize('checkin', $asset);
        $shipment_number = $asset->shipment_number;
        if ($asset->assignedType() == Asset::USER) {
            $user = $asset->assignedTo;
        }
        $current_user_id = Auth::user()->id;
        $status_id = $request->get('status_id');
        $asset->expected_checkin = null;
        $asset->last_checkout = null;
        $asset->assigned_to = null;
        $asset->assignedTo()->disassociate($asset);
        $asset->assigned_type = null;
        $asset->accepted = null;
        $asset->name = $request->get('name');
        if ($request->filled('status_id')) {
            $asset->status_id =  e($request->get('status_id'));
        }
        $asset->barcode_number = $request->get('barcode_number');
        // This is just meant to correct legacy issues where some user data would have 0
        // as a location ID, which isn't valid. Later versions of Snipe-IT have stricter validation
        // rules, so it's necessary to fix this for long-time users. It's kinda gross, but will help
        // people (and their data) in the long run

        if ($asset->rtd_location_id=='0') {
            \Log::debug('Manually override the RTD location IDs');
            \Log::debug('Original RTD Location ID: '.$asset->rtd_location_id);
            $asset->rtd_location_id = '';
            \Log::debug('New RTD Location ID: '.$asset->rtd_location_id);
        }

        if ($asset->location_id=='0') {
            \Log::debug('Manually override the location IDs');
            \Log::debug('Original Location ID: '.$asset->location_id);
            $asset->location_id = '';
            \Log::debug('New RTD Location ID: '.$asset->location_id);
        }

        $asset->location_id = $asset->rtd_location_id;
        \Log::debug('After Location ID: '.$asset->location_id);
        \Log::debug('After RTD Location ID: '.$asset->rtd_location_id);


        if ($request->filled('location_id')) {
            \Log::debug('NEW Location ID: '.$request->get('location_id'));
            $asset->location_id =  e($request->get('location_id'));
        }
        $input_status_id = $request->get('status_id');
        if($input_status_id == 6){
            $input_location_id = $request->get('location_id');
            $sql_1 = "SELECT company_id FROM users WHERE location_id = $input_location_id 
                    AND manager_id is NULL AND activated = 1";
            $query_1 = DB::select($sql_1);
            $company_id = $query_1[0]->company_id;
            $asset->company_id = $company_id;
        }
        $checkin_at = date('Y-m-d');
        if($request->filled('checkin_at')){
            $checkin_at = $request->input('checkin_at');
        }

        // Was the asset updated?
        if ($asset->save()) {
            event(new CheckoutableCheckedIn($asset, $target, Auth::user(), $request->input('note'), $checkin_at));
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
            $sync_message = '';
            if(isset($asset->status_id) && isset($shipment_number)){
                $sync_message = StatusMapping::syncShipmentStatus($shipment_number, $status_id);
            }
            if ((isset($user)) && ($backto =='user')) {
                return redirect()->route("users.show", $user->id)->with('success', trans('admin/hardware/message.checkin.success'))->with('message', $sync_message);
            }
            return redirect()->route("hardware.index")->with('success', trans('admin/hardware/message.checkin.success'))->with('message', $sync_message);
        }
        // Redirect to the asset management page with error
        return redirect()->route("hardware.index")->with('error', trans('admin/hardware/message.checkin.error').$asset->getErrors());
    }

    /**
    * Return Inventory Data.
    * @author prabin.thapa
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function showShipmentData(Request $request)
    {
        $barcode = $request->barcode;
        $barcode = trim($barcode);
        $last_index = $request->last_index;
        $client = new Client();
        $res = $client->request('GET', env('CSCART_SHIPMENT_URL'), [
            'headers' => ['Storefront-Api-Access-Key'     => env('CSCART_SHIPMENT_KEY')],
            'query'     => ['barcode' => $barcode],
        ]);
        $shipments = $res->getBody()->getContents();
        $decoded = json_decode($shipments, true);
        $order_info = $decoded['order_info'];
        if($order_info == false){
            $json = array(
                'status' => 'error',
                'message' => 'Invalid Barcode '.$barcode.''
            );
            return response()->json($json);
        }
        $product_groups = $order_info['product_groups'];
        $products = $decoded['products'];
        $html = '';
        $i = $last_index + 1;
        $shipment_id = $shipment_number = $decoded['shipment_id'];
        $category_name = $supplier_name = '';
        $category_names = $quantity = [];
        foreach($products as $key => $product){
            $category_id = $order_info['product_groups'][0]['products'][$key]['main_category'];
            $sql_1 = "SELECT * from categories where id = $category_id";
            $query_1 = DB::select($sql_1);
            $ApiRequest = new ApiRequest();
            $res = $ApiRequest->apiCall('GET', 'categories/'.$category_id);
            $category_names[] = $res['category'];
            $product_name[] = $order_info['product_groups'][0]['products'][$key]['product'];
            // $supplier_id = $order_info['product_groups'][0]['products'][$key]['company_id'];
            $supplier_id = 1;
            $sql_2 = "SELECT * from suppliers where id = $supplier_id";
            $query_2 = DB::select($sql_2);
            foreach($query_2 as $val_2){
                $supplier_name = $val_2->name;
            }
            $order_id = $order_info['products'][$key]['order_id'];
            $quantity[] = $order_info['product_groups'][0]['products'][$key]['amount'];
        }
        $category_name = implode(',',$category_names);
        $product_name = implode(',',$product_name);
        $quantity = implode(',',$quantity);
        $html .= '<tr id="bar-code-row-'.$i.'">';
        $html .= '<td>';
        $html .= '<input type="text" name="barcode_number[]" class="form-control barcode_number" value="'.$barcode.'">';
        $html .= '<input type="hidden" name="shipment_id['.$i.']" class="form-control shipment_id" value="'.$shipment_id.'">';
        $html .= '<input type="hidden" name="shipment_number[]" class="form-control shipment_number" value="'.$shipment_number.'">';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="text" name="category_name[]" class="form-control model_name" value="'.$category_name.'">';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="text" name="product_name[]" class="form-control product_name" value="'.$product_name.'">';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="hidden" name="supplier_id[]" class="form-control supplier_id" value="'.$supplier_id.'">';
        $html .= '<input type="text" name="supplier_name[]" class="form-control supplier_name" value="'.$supplier_name.'">';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="text" name="order_number[]" class="form-control order_number" value="'.$order_id.'">';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="text" name="quantity[]" class="form-control quantity" value="'.$quantity.'">';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<a href="javascript:void" class="remove_field btn btn-default btn-sm" tag_id="'.$i.'"><i class="fa fa-minus"></i></a>';
        $html .= '</td>';
        $html .= '</tr>';
        $json = array(
            'status' => 'success',
            'shipment_data' => $html,
            'last_index' => $i
        );
        return response()->json($json);
    }

    /**
     * Returns a view that presents a form to check an asset back into inventory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @since [v1.0]
     */
    public function showHubCheckInForm()
    {
        // Check if the asset exists
        $status = 5;
        return view('hardware/hubcheckin')->with('status', $status);
    }

    /**
    * Store Hub Check In Data.
    * @author prabin.thapa
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function storeHubcheckin(Request $request)
    {
        $assets_ids = $request->input('asset_id');
        $shipment_numbers = $request->input('shipment_number');
        $current_user_id = Auth::user()->id;
        $sql_1 = "SELECT * from users where id = $current_user_id";
        $query_1 = DB::select($sql_1);
        $logged_in_user_location_id = $query_1[0]->location_id;
        $status_id = $request->input('status_id');
        foreach($assets_ids as $key => $assets_id){
            $shipment_number = $shipment_numbers[$key];
            $assetId = $assets_id;
            if (is_null($asset = Asset::find($assetId))) {
                return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
            }
            
            if (is_null($target = $asset->assignedTo)) {
                return redirect()->route('hardware.index')->with('error', 'Already Checked In. Please Checkout First.');
            }
            $this->authorize('checkin', $asset);

            if ($asset->assignedType() == Asset::USER) {
                $user = $asset->assignedTo;
            }
            $asset->expected_checkin = null;
            $asset->last_checkout = null;
            $asset->assigned_to = null;
            $asset->assignedTo()->disassociate($asset);
            $asset->assigned_type = null;
            $asset->accepted = null;
            $asset->name = $asset->name;
            //status id = 5 => Package Received Entry Done in Hub Checkin
            $asset->status_id =  $status_id;
            $asset->location_id = $logged_in_user_location_id;
            $checkin_at = date('Y-m-d');
            DB::enableQueryLog();
            if ($asset->save()) {
                $sync_message = '';
                if(isset($status_id) && isset($shipment_number)){
                    $sync_message = StatusMapping::syncShipmentStatus($shipment_number, $status_id);
                }
                event(new CheckoutableCheckedIn($asset, $target, Auth::user(), 'Hub Checkin', $checkin_at));
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
            }
        }
        return redirect()->route("hardware.index")->with('success', trans('admin/hardware/message.checkin.success'))->with('message', $sync_message);
    }

    /**
    * Get the Shipment information and load entry form for First Time Check In.
    * @author prabin.thapa
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function showShipmentDataFirstTimeCheckIn(Request $request)
    {
        $barcode = $request->barcode;
        $barcode = $barcode_number = trim($barcode);
        $last_index = $request->last_index;
        $client = new Client();
        $res = $client->request('GET', env('CSCART_SHIPMENT_URL'), [
            'headers' => ['Storefront-Api-Access-Key'     => env('CSCART_SHIPMENT_KEY')],
            'query'     => ['barcode' => $barcode],
        ]);
        $shipments = $res->getBody()->getContents();
        $decoded = json_decode($shipments, true);
        $order_info = $decoded['order_info'];
        if($order_info == false){
            $json = array(
                'status' => 'error',
                'message' => 'Invalid Barcode Number',
                'barcode_number' => $barcode_number
            );
            return response()->json($json);
        }
        $customer_firstname = $decoded['s_firstname'];
        $customer_lastname = $decoded['s_lastname'];
        $customer_fullname = $customer_firstname.' '.$customer_lastname;
        $customer_phoneno = $order_info['phone'];
        $customer_address = $order_info['s_address'];
        $product_groups = $order_info['product_groups'];
        $products = $decoded['products'];
        $html = '';
        $i = $last_index + 1;
        $shipment_id = $shipment_number = $decoded['shipment_id'];
        $category_name = $supplier_name = '';
        $category_names = $quantity = [];
        foreach($products as $key => $product){
            $category_id = $order_info['product_groups'][0]['products'][$key]['main_category'];
            $sql_1 = "SELECT * from categories where id = $category_id";
            $query_1 = DB::select($sql_1);
            $ApiRequest = new ApiRequest();
            $res = $ApiRequest->apiCall('GET', 'categories/'.$category_id);
            $category_names[] = $res['category'];
            $product_name_val = $order_info['product_groups'][0]['products'][$key]['product'];
            $product_name_replace = htmlspecialchars($product_name_val, ENT_QUOTES, 'UTF-8');
            $product_name[] = $product_name_replace;
            // $supplier_id = $order_info['product_groups'][0]['products'][$key]['company_id'];
            $supplier_id = 1;
            $sql_2 = "SELECT * from suppliers where id = $supplier_id";
            $query_2 = DB::select($sql_2);
            foreach($query_2 as $val_2){
                $supplier_name = $val_2->name;
            }
            $order_id = $order_info['products'][$key]['order_id'];
            $quantity[] = $order_info['product_groups'][0]['products'][$key]['amount'];
        }
        $category_name = implode(',',$category_names);
        $product_name = implode(',',$product_name);
        $quantity = implode(',',$quantity);
        $html .= '<tr id="bar-code-row-'.$i.'">';
        $html .= '<td>';
        $html .= '<input type="text" name="barcode_number[]" class="form-control barcode_number" value="'.$barcode.'" readonly>';
        $html .= '<input type="hidden" name="shipment_id[]" class="form-control shipment_id" value="'.$shipment_id.'">';
        $html .= '<input type="hidden" name="shipment_number[]" class="form-control shipment_number" value="'.$shipment_number.'">';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="text" name="category_name[]" class="form-control model_name" value="'.$category_name.'" readonly>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="text" name="product_name[]" class="form-control product_name" value="'.$product_name.'" readonly>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="hidden" name="supplier_id[]" class="form-control supplier_id" value="'.$supplier_id.'">';
        $html .= '<input type="text" name="supplier_name[]" class="form-control supplier_name" value="'.$supplier_name.'" readonly>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="text" name="order_number[]" class="form-control order_number" value="'.$order_id.'" readonly>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="text" name="quantity[]" class="form-control quantity" value="'.$quantity.'" readonly>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="hidden" name="customer_firstname[]" class="form-control customer_firstname" value="'.$customer_firstname.'" readonly>';
        $html .= '<input type="hidden" name="customer_lastname[]" class="form-control customer_lastname" value="'.$customer_lastname.'" readonly>';
        $html .= '<input type="text" name="customer_fullname[]" class="form-control customer_fullname" value="'.$customer_fullname.'" readonly>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="text" name="customer_phoneno[]" class="form-control customer_phoneno" value="'.$customer_phoneno.'" readonly>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<input type="text" name="customer_address[]" class="form-control customer_address" value="'.$customer_address.'" readonly>';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<a href="javascript:void" class="remove_field btn btn-default btn-sm" tag_id="'.$i.'"><i class="fa fa-minus"></i></a>';
        $html .= '</td>';
        $html .= '</tr>';
        $json = array(
            'status' => 'success',
            'shipment_data' => $html,
            'last_index' => $i
        );
        return response()->json($json);
    }
}