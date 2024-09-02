<?php

namespace App\Http\Controllers\Assets;

use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCheckoutRequest;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Setting;
use App\Models\StatusMapping;
use App\Models\CarrierModel;
use App\Models\CarrierMappingModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\AssetModel;
use App\Models\Rider;
use App\Models\Vehicle;
use View;
use Gate;
use App\Http\Requests\ApiRequest;

class BulkAssetsController extends Controller
{
    use CheckInOutRequest;

    /**
     * Display the bulk edit page.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return View
     * @internal param int $assetId
     * @since [v2.0]
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Request $request)
    {
        $this->authorize('update', Asset::class);

        if (!$request->filled('ids')) {
            return redirect()->back()->with('error', 'No assets selected');
        }
        
        
        $asset_ids = array_values(array_unique($request->input('ids')));

        if ($request->filled('bulk_actions')) {
            switch($request->input('bulk_actions')) {
                case 'labels':
                    return view('hardware/labels')
                        ->with('assets', Asset::find($asset_ids))
                        ->with('settings', Setting::getSettings())
                        ->with('bulkedit', true)
                        ->with('count', 0);
                case 'delete':
                    $assets = Asset::with('assignedTo', 'location')->find($asset_ids);
                    $assets->each(function ($asset) {
                        $this->authorize('delete', $asset);
                    });
                    return view('hardware/bulk-delete')->with('assets', $assets);
                case 'edit':
                    return view('hardware/bulk')
                        ->with('assets', $asset_ids)
                        ->with('statuslabel_list', Helper::statusLabelList());
                case 'tag':
                    $tag_name = $this->generateTag($asset_ids);
                case 'pod':
                    $this->generatePOD($asset_ids);
            }
        }
        $action_type = $request->input('bulk_actions');
        if($action_type == 'tag'){
            return view('hardware/labels_tag')
                ->with('assets_ids', Asset::find($asset_ids))
                ->with('settings', Setting::getSettings())
                ->with('bulkedit', true)
                ->with('count', 0)
                ->with('tag_name', $tag_name);
        }
        elseif($action_type == 'pod'){
            $assets = Asset::find($asset_ids);
            // $supplierIds = $assets->pluck('supplier_id')->unique();

            $groupedAssets = $assets->groupBy('supplier_id');
            return view('hardware/bulk-pod')
                ->with('groupassets', $groupedAssets)
                ->with('vehicles', Vehicle::where('status', '=', 1)->get())
                ->with('riders', Rider::where('status', '=', 1)->get())
                ->with('settings', Setting::getSettings())
                ->with('bulkedit', true);
        }
        else{
            return redirect()->back()->with('error', 'No action selected');
        }
    }

    /**
     * Save bulk edits
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return Redirect
     * @internal param array $assets
     * @since [v2.0]
     */
    public function update(Request $request)
    {
        $this->authorize('update', Asset::class);

        \Log::debug($request->input('ids'));

        if(!$request->filled('ids') || count($request->input('ids')) <= 0) {
            return redirect()->route("hardware.index")->with('warning', trans('No assets selected, so nothing was updated.'));
        }

        $assets = array_keys($request->input('ids'));

        if (($request->filled('purchase_date'))
            || ($request->filled('expected_checkin'))
            || ($request->filled('purchase_cost'))
            || ($request->filled('supplier_id'))
            || ($request->filled('order_number'))
            || ($request->filled('warranty_months'))
            || ($request->filled('rtd_location_id'))
            || ($request->filled('requestable'))
            || ($request->filled('company_id'))
            || ($request->filled('status_id'))
            || ($request->filled('model_id'))
        ) {
            foreach ($assets as $assetId) {

                $this->update_array = [];

                $this->conditionallyAddItem('purchase_date')
                    ->conditionallyAddItem('expected_checkin')
                    ->conditionallyAddItem('model_id')
                    ->conditionallyAddItem('order_number')
                    ->conditionallyAddItem('requestable')
                    ->conditionallyAddItem('status_id')
                    ->conditionallyAddItem('supplier_id')
                    ->conditionallyAddItem('warranty_months');

                if ($request->filled('purchase_cost')) {
                    $this->update_array['purchase_cost'] =  Helper::ParseCurrency($request->input('purchase_cost'));
                }

                if ($request->filled('company_id')) {
                    $this->update_array['company_id'] =  $request->input('company_id');
                    if ($request->input('company_id')=="clear") {
                        $this->update_array['company_id'] = null;
                    }
                }

                if ($request->filled('rtd_location_id')) {
                    $this->update_array['rtd_location_id'] = $request->input('rtd_location_id');
                    if (($request->filled('update_real_loc')) && (($request->input('update_real_loc')) == '1')) {
                        $this->update_array['location_id'] = $request->input('rtd_location_id');
                    }
                }

                $changed = [];
                $asset = Asset::where('id' ,$assetId)->get();

                foreach ($this->update_array as $key => $value) {
                    if ($this->update_array[$key] != $asset->toArray()[0][$key]) {
                        $changed[$key]['old'] = $asset->toArray()[0][$key];
                        $changed[$key]['new'] = $this->update_array[$key];
                    }
                }

                $logAction = new Actionlog();
                $logAction->item_type = Asset::class;
                $logAction->item_id = $assetId;
                $logAction->created_at =  date("Y-m-d H:i:s");
                $logAction->user_id = Auth::id();
                $logAction->log_meta = json_encode($changed);
                $logAction->logaction('update');

                DB::table('assets')
                    ->where('id', $assetId)
                    ->update($this->update_array);
            } // endforeach
            return redirect()->route("hardware.index")->with('success', trans('admin/hardware/message.update.success'));
        // no values given, nothing to update
        }
        return redirect()->route("hardware.index")->with('warning', trans('admin/hardware/message.update.nothing_updated'));

    }

    /**
     * Array to store update data per item
     * @var Array
     */
    private $update_array;

    /**
     * Adds parameter to update array for an item if it exists in request
     * @param  String $field field name
     * @return BulkAssetsController Model for Chaining
     */
    protected function conditionallyAddItem($field)
    {
        if(request()->filled($field)) {
            $this->update_array[$field] = request()->input($field);
        }
        return $this;
    }

    /**
     * Save bulk deleted.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param Request $request
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @internal param array $assets
     * @since [v2.0]
     */
    public function destroy(Request $request)
    {
        $this->authorize('delete', Asset::class);

        if ($request->filled('ids')) {
            $assets = Asset::find($request->get('ids'));
            foreach ($assets as $asset) {
                $update_array['deleted_at'] = date('Y-m-d H:i:s');
                $update_array['assigned_to'] = null;

                DB::table('assets')
                    ->where('id', $asset->id)
                    ->update($update_array);
            } // endforeach
            return redirect()->to("hardware")->with('success', trans('admin/hardware/message.delete.success'));
            // no values given, nothing to update
        }
        return redirect()->to("hardware")->with('info', trans('admin/hardware/message.delete.nothing_updated'));
    }

    /**
     * Show Bulk Checkout Page
     * @return View View to checkout multiple assets
     */
    public function showCheckout()
    {
        $this->authorize('checkout', Asset::class);
        // Filter out assets that are not deployable.

        return view('hardware/bulk-checkout');
    }

    /**
     * Process Multiple Checkout Request
     * @return View
     */
    public function storeCheckout(Request $request)
    {
        try {
            $admin = Auth::user();

            $target = $this->determineCheckoutTarget();

            if (!is_array($request->get('selected_assets'))) {
                return redirect()->route('hardware/bulkcheckout')->withInput()->with('error', trans('admin/hardware/message.checkout.no_assets_selected'));
            }

            $asset_ids = array_filter($request->get('selected_assets'));

            if(request('checkout_to_type') =='asset') {
                foreach ($asset_ids as $asset_id) {
                    if ($target->id == $asset_id)  {
                        return redirect()->back()->with('error', 'You cannot check an asset out to itself.');
                    }
                }
            }
            $checkout_at = date("Y-m-d H:i:s");
            if (($request->filled('checkout_at')) && ($request->get('checkout_at')!= date("Y-m-d"))) {
                $checkout_at = e($request->get('checkout_at'));
            }

            $expected_checkin = '';

            if ($request->filled('expected_checkin')) {
                $expected_checkin = e($request->get('expected_checkin'));
            }

            $errors = [];
            DB::transaction(function () use ($target, $admin, $checkout_at, $expected_checkin, $errors, $asset_ids, $request) {

                foreach ($asset_ids as $asset_id) {
                    $asset = Asset::findOrFail($asset_id);
                    $this->authorize('checkout', $asset);
                    $error = $asset->checkOut($target, $admin, $checkout_at, $expected_checkin, e($request->get('note')), null);

                    if ($target->location_id!='') {
                        $asset->location_id = $target->location_id;
                        $asset->unsetEventDispatcher();
                        $asset->save();

                    }

                    if ($error) {
                        array_merge_recursive($errors, $asset->getErrors()->toArray());
                    }
                }
            });

            if (!$errors) {
              // Redirect to the new asset page
                return redirect()->to("hardware")->with('success', trans('admin/hardware/message.checkout.success'));
            }
            // Redirect to the asset management page with error
            return redirect()->to("hardware/bulk-checkout")->with('error', trans('admin/hardware/message.checkout.error'))->withErrors($errors);
        } catch (ModelNotFoundException $e) {
            return redirect()->to("hardware/bulk-checkout")->with('error', $e->getErrors());
        }
    }

    /**
     * Show Bulk Checkin Page
     * @return View View to checkout multiple assets
     */
    public function showCheckin(Request $request)
    {
        $this->authorize('create', Asset::class);
        $view = View::make('hardware/bulk-checkin')
            ->with('statuslabel_list', Helper::statusLabelList())
            ->with('item', new Asset)
            ->with('statuslabel_types', Helper::statusTypeList());

        if ($request->filled('model_id')) {
            $selected_model = AssetModel::find($request->input('model_id'));
            $view->with('selected_model', $selected_model);
        }
        return $view;
    }

    /**
     * Validate and process First Time Check In form data.
     *
     * @author prabinthapamagar
     * @since [v1.0]
     * @return Redirect
     */
    public function storeCheckin(Request $request)
    {
        $this->authorize('create', Asset::class);
        $shipment_id = $request->input('shipment_id');
        $model_id = $request->input('model_id');
        $asset_tags = $request->input('asset_tags');
        $product_name = $request->input('product_name');
        $supplier_id = $request->input('supplier_id');
        $order_number = $request->input('order_number');
        $barcode_number = $request->input('barcode_number');
        $shipment_number = $request->input('shipment_number');
        $category_name = $request->input('category_name');
        $supplier_id = $request->input('supplier_id');
        $model_id = $request->input('model_id');
        //Package Received Status is sent in Firs Time Check In
        $status_id = $request->input('status_id');
        $settings = Setting::getSettings();
        $notes = $request->input('notes');
        $quantity = $request->input('quantity');
        $customer_firstname = $request->input('customer_firstname');
        $customer_lastname = $request->input('customer_lastname');
        $customer_phoneno = $request->input('customer_phoneno');
        $customer_address = $request->input('customer_address');
        $current_user_id = Auth::id();
        $success = false;
        $count_shipment_id = count($shipment_id);
        for ($a = 0; $a < $count_shipment_id; $a++) {
            $asset = new Asset();
            // $asset->model()->associate(AssetModel::find($supplier_id[$a]));
            $asset->model()->associate(AssetModel::find(1));
            if (($product_name) && (array_key_exists($a, $product_name))) {
                $asset->name = $product_name[$a];
            }
            $asset->supplier_id = $supplier_id;
            if (($order_number) && (array_key_exists($a, $order_number))) {
                $asset->order_number = $order_number[$a];
            }
            $asset->model_id = $model_id;
            $asset->user_id = $current_user_id;
            $asset->archived = '0';
            $asset->physical = '1';
            $asset->depreciate = '0';
            $asset->status_id = $status_id;
            $asset->assigned_to = request('assigned_to', null);
            $asset->requestable = request('requestable', 0);
            $asset->rtd_location_id = request('rtd_location_id', null);
            $asset->barcode_number = $barcode_number[$a];
            $asset->shipment_number = $shipment_number[$a];
            $asset->category_name = $category_name[$a];
            $asset->quantity = $quantity[$a];
            $asset->customer_firstname = $customer_firstname[$a];
            $asset->customer_lastname = $customer_lastname[$a];
            $asset->customer_phoneno = $customer_phoneno[$a];
            $asset->customer_address = $customer_address[$a];
            $asset->location_id = Auth::user()->location_id;
            $asset->company_id = Auth::user()->company_id;
            // Update custom fields in the database.
            // Validation for these fields is handled through the AssetRequest form request
            DB::enableQueryLog();
            $model = AssetModel::find(1);
            if (($model) && ($model->fieldset)) {
                foreach ($model->fieldset->fields as $field) {
                    if ($field->field_encrypted=='1') {
                        if (Gate::allows('admin')) {
                            if(is_array($request->input($field->convertUnicodeDbSlug()))){
                                $asset->{$field->convertUnicodeDbSlug()} = \Crypt::encrypt(e(implode(', ', $request->input($field->convertUnicodeDbSlug()))));
                            }else{
                                $asset->{$field->convertUnicodeDbSlug()} = \Crypt::encrypt(e($request->input($field->convertUnicodeDbSlug())));
                            }                        
                        }
                    } else {
                        if(is_array($request->input($field->convertUnicodeDbSlug()))){
                            $asset->{$field->convertUnicodeDbSlug()} = implode(', ', $request->input($field->convertUnicodeDbSlug()));
                        }else{
                            $asset->{$field->convertUnicodeDbSlug()} = $request->input($field->convertUnicodeDbSlug());
                        }
                    }
                }
            }
            // Validate the asset before saving
            if ($asset->isValid() && $asset->save()) {
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
                $success = true;
            }else {
                dd(DB::getQueryLog());
            }
        }
        if ($success) {
            // Redirect to the asset listing page
            return redirect()->route('hardware.index')
                ->with('success', trans('admin/hardware/message.create.success'));
        }
        return redirect()->back()->withInput()->withErrors($asset->getErrors());
    }

    /**
     * Show POD Create Page
     * @author shristishrestha
     * @return View View to checkout multiple assets
     */
    public function showBulkPOD()
    {
        $assets = Asset::where('status_id','=',13)->get();
            // $supplierIds = $assets->pluck('supplier_id')->unique();
        $groupedAssets = $assets->groupBy('supplier_id');
        return view('hardware/bulk-pod')
            ->with('groupassets', $groupedAssets)
            ->with('vehicles', Vehicle::where('status', '=', 1)->get())
            ->with('riders', Rider::where('status', '=', 1)->get())
            ->with('settings', Setting::getSettings())
            ->with('bulkedit', true);
    }

    /**
     * Show Bulk Checkout Page
     * @author prabinthapamagar
     * @return View View to checkout multiple assets
     */
    public function showBulkCheckout()
    {
        $this->authorize('checkout', Asset::class);
        $data['current_user_id'] = $logged_in_user_id = Auth::user()->id;
        $data['logged_in_user_location_id'] = Auth::user()->location_id;
        $data['carrier_lists'] = CarrierModel::get();
        return view('hardware/bulk-checkoutsys',$data);
    }

    /**
     * Process Multiple Checkout Request
     * @author prabinthapamagar
     * @return View
     */
    public function storeBulkCheckout(Request $request)
    {
        try {
            if (!is_array($request->input('asset_id'))) {
                return redirect()->route('hardware/bulkcheckout')->withInput()->with('error', trans('admin/hardware/message.checkout.no_assets_selected'));
            }
            $asset_ids = array_filter($request->input('asset_id'));
            $expected_checkin = $request->get('expected_checkin');
            $checkout_at = date("Y-m-d H:i:s");
            $expected_checkin = '';
            $errors = [];
            $product_names = $request->input('product_name');
            $note = '';
            $sql_1 = "SELECT now() as currentdatetime";
            $query_1 = DB::select($sql_1);
            $last_checkout = $checkout_at = $query_1[0]->currentdatetime;
            $is_third_party_transfer = $request->get('is_third_party_transfer');
            $carrier_id = $request->get('carrier_id');
            $status_id = $request->get('status_id');
            $current_user_id = Auth::user()->id;
            $sync_messages = $response_message = [];
            $cscart_carrier_id = null;
            foreach($asset_ids as $key => $assets_id){
                $assetId = $assets_id;
                if (!$asset = Asset::find($assetId)) {
                    return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
                } elseif (!$asset->availableForCheckout()) {
                    return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkout.not_available'));
                }
                $this->authorize('checkout', $asset);
                $admin = Auth::user();
                
                $target = $this->determineCheckoutTarget($asset);
                $asset = $this->updateAssetLocation($asset, $target);

                $checkout_at = date("Y-m-d H:i:s");
                if (($request->filled('checkout_at')) && ($request->get('checkout_at')!= date("Y-m-d"))) {
                    $checkout_at = $request->get('checkout_at');
                }

                $expected_checkin = '';
                if ($request->filled('expected_checkin')) {
                    $expected_checkin = date('Y-md-d');
                }

                $asset->status_id = $status_id;
                if($is_third_party_transfer == 'Y'){
                    $asset->carrier_id = $carrier_id;
                }
                $shipment_number = $asset->shipment_number;
                $product_name = $product_names[$key];
                $asset->checkOut($target, $admin, $checkout_at, $expected_checkin, 'test', $product_name);
                //update status in action log table
                $action_id = DB::table('action_logs')->where('action_logs.user_id', '=', $current_user_id)->max('id');
                $action_log = Actionlog::find($action_id);
                $action_log->status_id = $status_id;
                $action_log->save();
                //end update status in action log table

                if(isset($status_id) && isset($shipment_number)){
                    $sync_messages[] = StatusMapping::syncShipmentStatus($shipment_number, $status_id);
                }
                if($is_third_party_transfer == 'Y'){
                    $cscart_carrier_id = CarrierMappingModel::where('carrier_id',$carrier_id)->value('cscart_carrier_id');
                    if(isset($cscart_carrier_id) && $cscart_carrier_id == env('CSCART_NCM_CARRIER')){
                        $response_message[] = CarrierMappingModel::syncCscartShipment($shipment_number, env('CSCART_NCM_CARRIER'));
                    }
                }
            }
            return redirect()->route("hardware.index")->with('success', 'Bulk Checkout Processed Successfully!')->with('message', implode(' , ' , $sync_messages))->with('notification', implode(' , ', $response_message));
        } catch (ModelNotFoundException $e) {
            return redirect()->to("hardware/bulk-checkout")->with('error', $e->getErrors());
        }
    }

    /**
     * Show Bulk Third Party Checkout Page
     * @author prabinthapamagar
     * @return View View to checkout multiple assets
     */
    public function showBulkCheckoutThirdParty()
    {
        $this->authorize('checkout', Asset::class);
        $data['current_user_id'] = $logged_in_user_id = Auth::user()->id;
        $sql_1 = "SELECT * from users where id = $logged_in_user_id";
        $query_1 = DB::select($sql_1);
        $data['logged_in_user_location_id'] = $query_1[0]->location_id;
        $data['carrier_lists'] = CarrierModel::get();
        return view('hardware/bulk-checkout-thirdparty',$data);
    }

    /**
     * Process Bulk Third party Checkout Request
     * @author prabinthapamagar
     * @return View
     */
    public function storeBulkCheckoutThirdParty(Request $request)
    {
        try {
            $assetIds = $request->get('asset_id');
            $shipment_numbers = $request->get('shipment_number');
            $status_id = $request->get('status_id');
            $current_user_id = Auth::user()->id;
            foreach($assetIds as $key => $assetId){
                if (!$asset = Asset::find($assetId)) {
                    return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
                } elseif (!$asset->availableForCheckout()) {
                    return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkout.not_available'));
                }
                $this->authorize('checkout', $asset);
                $admin = Auth::user();
                $target = $this->determineCheckoutTarget($asset);
                $asset = $this->updateAssetLocation($asset, $target);
                $checkout_at = date("Y-m-d H:i:s");
                if (($request->filled('checkout_at')) && ($request->get('checkout_at')!= date("Y-m-d"))) {
                    $checkout_at = $request->get('checkout_at');
                }
                $expected_checkin = '';
                $asset->status_id = $status_id;
                $shipment_number = $shipment_numbers[$key];
                $carrier_id = $request->get('carrier_id');
                $asset->carrier_id = $carrier_id;
                $asset->location_id = $request->get('assigned_location');
                $mapping_lists = CarrierMappingModel::where('carrier_id',$carrier_id)->get();
                $cscart_carrier_id = $mapping_lists[0]->cscart_carrier_id;
                if($status_id == 6){
                    if ($asset->checkOutIndividual($target, $admin, $checkout_at, $expected_checkin, e($request->get('note')), $request->get('name'))) {
                        //update status in action log table
                        $action_id = DB::table('action_logs')->where('action_logs.user_id', '=', $current_user_id)->max('id');
                        $action_log = Actionlog::find($action_id);
                        $action_log->status_id = $status_id;
                        $action_log->save();
                        //end update status in action log table
                        $sync_message = $response_message = '';
                        if(isset($status_id) && isset($shipment_number)){
                            $sync_message = StatusMapping::syncShipmentStatus($shipment_number, $status_id);
                        }
                        if(isset($cscart_carrier_id)){
                            $response_message = $this->syncBulkCscartShipment($shipment_number, $cscart_carrier_id);
                        }
                    }
                }
                else{
                    if ($asset->checkOut($target, $admin, $checkout_at, $expected_checkin, e($request->get('note')), $request->get('name'))) {
                        //update status in action log table
                        $action_id = DB::table('action_logs')->where('action_logs.user_id', '=', $current_user_id)->max('id');
                        $action_log = Actionlog::find($action_id);
                        $action_log->status_id = $status_id;
                        $action_log->save();
                        //end update status in action log table
                        $sync_message = $response_message = '';
                        if(isset($status_id) && isset($shipment_number)){
                            $sync_message = StatusMapping::syncShipmentStatus($shipment_number, $status_id);
                        }
                        if(isset($cscart_carrier_id)){
                            $response_message = $this->syncBulkCscartShipment($shipment_number, $cscart_carrier_id);
                        }
                    }
                }
            }
            return redirect()->route("hardware.index")->with('success', trans('admin/hardware/message.checkout.success'))->with('message', $sync_message)->with('message', $response_message);
        } catch (ModelNotFoundException $e) {
            return redirect()->to("hardware/bulk-checkout")->with('error', $e->getErrors());
        }
    }

    public function syncBulkCscartShipment($shipment_id, $cscart_carrier_id){
        $ApiRequest = new ApiRequest();
        $response = $ApiRequest->apiCall('PUT', 'shipments/'.$shipment_id, ['carrier' => 'nepal_can_move','external_sync'=> true]);
        return isset($response['sync'][1])? $response['sync'][1] . ' ' . $shipment_id: 'Error'.$shipment_id;
    }
    
    /**
     * Generate Asset POD
     * @author shristishrestha
     * @return List
     */
    public function generatePOD($asset_ids){
        // print_r($asset_ids);
        // die;
        return 1;
    }

    /**
     * Generate Asset tag
     * @author prabinthapamagar
     * @return List
     */
    public function generateTag($asset_ids){
        $settings = Setting::first();
        $next_auto_asset_tag_base = $settings->next_auto_asset_tag_base;
        $asset_tag_prefix = $settings->asset_tag_prefix;
        //$code = str_pad($next_auto_asset_tag_base,5,0,STR_PAD_LEFT);
        $next_auto_asset_tag_base_new = $next_auto_asset_tag_base + 1;
        $code = $next_auto_asset_tag_base_new;
        $new_tag = $asset_tag_prefix.$code;
        foreach($asset_ids as $asset_id){
            $update = Asset::find($asset_id);
            $update->asset_tag = $new_tag;
            $update->save();
        }
        $update_setting = Setting::first();
        $update_setting->next_auto_asset_tag_base = $next_auto_asset_tag_base_new;
        $update_setting->save();
        //update note and action date in action log table
        $current_user_id = Auth::user()->id;
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
        $action_log->note = 'Asset Tag Generated';
        $action_log->action_date = $action_date;
        $action_log->save();
        //end update note and action date in action log table
        return $new_tag;
    }

    /**
     * Show Bulk Tag Checkout Page
     * @author prabinthapamagar
     * @return View View to checkout multiple assets
     */
    public function showBulkCheckoutTag()
    {
        $this->authorize('checkout', Asset::class);
        $data['current_user_id'] = $logged_in_user_id = Auth::user()->id;
        $sql_1 = "SELECT * from users where id = $logged_in_user_id";
        $query_1 = DB::select($sql_1);
        $data['logged_in_user_location_id'] = $query_1[0]->location_id;
        $data['carrier_lists'] = CarrierModel::get();
        return view('hardware/bulk-checkouttag',$data);
    }

    /**
     * Process Bulk Tag Checkout Request
     * @author prabinthapamagar
     * @return View
     */
    public function storeBulkCheckoutTag(Request $request)
    {
        try {
            if (!is_array($request->input('asset_id'))) {
                return redirect()->route('hardware/bulkcheckout')->withInput()->with('error', trans('admin/hardware/message.checkout.no_assets_selected'));
            }
            $asset_ids = array_filter($request->input('asset_id'));
            $expected_checkin = $request->get('expected_checkin');
            $checkout_at = date("Y-m-d H:i:s");
            $expected_checkin = '';
            $errors = [];
            $product_names = $request->input('product_name');
            $note = '';
            $sql_1 = "SELECT now() as currentdatetime";
            $query_1 = DB::select($sql_1);
            $last_checkout = $checkout_at = $query_1[0]->currentdatetime;
            $is_third_party_transfer = $request->get('is_third_party_transfer');
            $carrier_id = $request->get('carrier_id');
            $cscart_carrier_id = CarrierMappingModel::where('carrier_id', $carrier_id)->value('cscart_carrier_id');
            $status_id = $request->get('status_id');
            $current_user_id = Auth::user()->id;
            $tags = $request->get('asset_tag');
            $response_message = $sync_messages = [];
            foreach ($asset_ids as $key => $assets_id) {
                $assetId = $assets_id;
                if (!$asset = Asset::find($assetId)) {
                    return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
                }
                // elseif (!$asset->availableForCheckout()) {
                //     return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkout.not_available'));
                // }
                $this->authorize('checkout', $asset);
                $admin = Auth::user();

                $target = $this->determineCheckoutTarget($asset);
                $asset = $this->updateAssetLocation($asset, $target);

                $checkout_at = date("Y-m-d H:i:s");
                if (($request->filled('checkout_at')) && ($request->get('checkout_at') != date("Y-m-d"))) {
                    $checkout_at = $request->get('checkout_at');
                }

                $expected_checkin = '';
                if ($request->filled('expected_checkin')) {
                    $expected_checkin = date('Y-md-d');
                }

                $asset->status_id = $status_id;
                $asset->carrier_id = $carrier_id;
                $product_name = $product_names[$key];
                $asset->checkOut($target, $admin, $checkout_at, $expected_checkin, 'Bulk Tag Checkout', $product_name);
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
            }

            //to  combine common asset tag with shipment ids
            $unique_tags = array_unique($tags, SORT_REGULAR);
            foreach($unique_tags as $asset_tag){
                $shipments = Asset::where('asset_tag', $asset_tag)->pluck('shipment_number')->toArray();
                $response_message[] = CarrierMappingModel::syncTagShipmentCarrier($shipments, $status_id, $asset_tag, $cscart_carrier_id);
            }
            return redirect()->route("hardware.index")->with('success', 'Bulk Checkout Processed Successfully!')->with('message', implode(" , ", $sync_messages))->with('notification', implode(" , ", $response_message));
        } catch (ModelNotFoundException $e) {
            return redirect()->to("hardware/bulk-checkout")->with('error', $e->getErrors());
        }
    }

    /**
     * Print Asset tag
     * @author prabinthapamagar
     * @return List
     */
    public function printTag($asset_id){
        $settings = Setting::first();
        $asset_details = Asset::find($asset_id);
        $tag_name = $asset_details->asset_tag;
        $barcode_image_name = $tag_name.'.png';
        $barcode_path = './uploads/barcodes/'.$barcode_image_name;
        return view('hardware/labels_tag_print')
                ->with('assets_ids', Asset::find($asset_id))
                ->with('settings', Setting::getSettings())
                ->with('bulkedit', true)
                ->with('count', 0)
                ->with('tag_name', $tag_name)
                ->with('barcode_path', $barcode_path);
    }

    /**
     * Update bulk status
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return Redirect
     * @internal param array $assets
     * @since [v2.0]
     */
    public function updateBulkStatus(Request $request)
    {
        $this->authorize('update', Asset::class);

        \Log::debug($request->input('ids'));

        if(!$request->filled('ids') || count($request->input('ids')) <= 0) {
            return redirect()->route("hardware.index")->with('warning', trans('No assets selected, so nothing was updated.'));
        }

        $assets = array_keys($request->input('ids'));

        if ($request->filled('status_id'))
        {
            $true_count = 0;
            $false_count = 0;
            foreach ($assets as $assetId) {

                $changed = [];
                $asset = Asset::where('id' ,$assetId)->get();

                $this->update_array = [];

                $this->conditionallyAddItem('status_id');

                if(Helper::checkStatusLabelByPosition($asset->toArray()[0]['status_id'],$this->update_array['status_id']))
                {             
                    foreach ($this->update_array as $key => $value) {
                        if ($this->update_array[$key] != $asset->toArray()[0][$key]) {
                            $changed[$key]['old'] = $asset->toArray()[0][$key];
                            $changed[$key]['new'] = $this->update_array[$key];
                        }
                    }

                    $logAction = new Actionlog();
                    $logAction->item_type = Asset::class;
                    $logAction->item_id = $assetId;
                    $logAction->created_at =  date("Y-m-d H:i:s");
                    $logAction->user_id = Auth::id();
                    $logAction->log_meta = json_encode($changed);
                    $logAction->logaction('update');

                    DB::table('assets')
                        ->where('id', $assetId)
                        ->update($this->update_array);
                    $true_count++;
                }else{
                    $false_count++;
                }
            } // endforeach
            if($true_count>0 && $false_count>0)
            {
               return redirect()->back()->with('success', $true_count.' Selected '.trans('admin/hardware/message.update.success'))->with('warning', $false_count.' Selected '.trans('admin/hardware/message.update.error'));
            }elseif($true_count>0 && $false_count==0)
            {
               return redirect()->back()->with('success', $true_count.' Selected '.trans('admin/hardware/message.update.success'));
            }else
            {
               return redirect()->back()->with('warning', $false_count.' Selected '.trans('admin/hardware/message.update.error'));
            }
        // no values given, nothing to update
        }
        return redirect()->back()->with('warning', trans('admin/hardware/message.update.nothing_updated'));

    }

    /**
     * Update status via ajax call
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return Redirect
     * @internal param array $assets
     * @since [v2.0]
     */
    public function updateStatus(Request $request)
    {
        $this->authorize('update', Asset::class);

        \Log::debug($request->input('ids'));

        if(!$request->filled('ids') || count($request->input('ids')) <= 0) {
            return redirect()->route("hardware.index")->with('warning', trans('No assets selected, so nothing was updated.'));
        }

        $assets = array_values($request->input('ids'));

        if ($request->filled('status_id'))
        {
            $true_count = 0;
            $false_count = 0;
            foreach ($assets as $assetId) {

                $changed = [];
                $asset = Asset::where('id' ,$assetId)->get();

                $this->update_array = [];

                $this->conditionallyAddItem('status_id');

                if(Helper::checkStatusLabelByPosition($asset->toArray()[0]['status_id'],$this->update_array['status_id']))
                {             
                    foreach ($this->update_array as $key => $value) {
                        if ($this->update_array[$key] != $asset->toArray()[0][$key]) {
                            $changed[$key]['old'] = $asset->toArray()[0][$key];
                            $changed[$key]['new'] = $this->update_array[$key];
                        }
                    }

                    $logAction = new Actionlog();
                    $logAction->item_type = Asset::class;
                    $logAction->item_id = $assetId;
                    $logAction->created_at =  date("Y-m-d H:i:s");
                    $logAction->user_id = Auth::id();
                    $logAction->log_meta = json_encode($changed);
                    $logAction->logaction('update');

                    DB::table('assets')
                        ->where('id', $assetId)
                        ->update($this->update_array);
                    $true_count++;
                }else{
                    $false_count++;
                }
            } // endforeach
            if($true_count>0 && $false_count>0)
            {
                $request->session()->flash('success', 'Selected '.trans('admin/hardware/message.update.success'));
                $request->session()->flash('warning', 'Selected '.trans('admin/hardware/message.update.error'));
            }elseif($true_count>0 && $false_count==0)
            {
                $request->session()->flash('success', 'Selected '.trans('admin/hardware/message.update.success'));
            }else
            {
                $request->session()->flash('warning', 'Selected '.trans('admin/hardware/message.update.error'));
            }

            $json = array(
                'status' => 'success'
            );
            
        // no values given, nothing to update
        }else{
            $request->session()->flash('warning', trans('admin/hardware/message.update.nothing_updated'));
            $json = array(
                'status' => 'error'
            );
        }

        return response()->json($json);

    }
}
