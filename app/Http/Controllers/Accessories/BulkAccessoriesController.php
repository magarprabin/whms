<?php

namespace App\Http\Controllers\Accessories;

use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Models\Actionlog;
use App\Models\Accessory;
use App\Models\Setting;
use Helper\Acceptance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BulkAccessoriesController extends Controller
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
        $this->authorize('update', Accessories::class);

        if (!$request->filled('ids')) {
            return redirect()->back()->with('error', 'No assets selected');
        }

        
        $asset_ids = array_values(array_unique($request->input('ids')));
        if ($request->filled('bulk_actions')) {
            switch($request->input('bulk_actions')) {
                case 'labels':
                    return view('accessories/labels')
                        ->with('accessories', Accessory::find($asset_ids))
                        ->with('settings', Setting::getSettings())
                        ->with('bulkedit', true)
                        ->with('count', 0);
                case 'delete':
                    $assets = Accessory::with('assignedTo', 'location')->find($asset_ids);
                    $assets->each(function ($asset) {
                        $this->authorize('delete', $asset);
                    });
                    return view('hardware/bulk-delete')->with('assets', $assets);
                case 'edit':
                    return view('hardware/bulk')
                        ->with('assets', $asset_ids)
                        ->with('statuslabel_list', Helper::statusLabelList());
            }
        }
        return redirect()->back()->with('error', 'No action selected');
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
     * Display the bulk edit page.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return View
     * @internal param int $accessoryId
     * @since [v2.0]
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function printlabel($accessoryId)
    {
        $accessory_details = Accessory::find($accessoryId);
        $inventory_tag = $accessory_details->inventory_tag;
        $inventory_tag = strtolower($inventory_tag);
        $qrcode_image_name = 'qr-'.$inventory_tag.'-'.$accessoryId.'.png';
        $qrcode_path = './uploads/barcodes/'.$qrcode_image_name;
        $barcode_image_name = 'c128-'.$inventory_tag.'.png';
        $barcode_path = './uploads/barcodes/'.$barcode_image_name;
        if(file_exists($barcode_path)){
            $is_generated = true;
        }
        else{
            $is_generated = false;
        }
        return view('accessories/printlabel')
                        ->with('accessories', Accessory::find($accessoryId))
                        ->with('settings', Setting::getSettings())
                        ->with('bulkedit', true)
                        ->with('count', 0)
                        ->with('image_name',$qrcode_image_name)
                        ->with('qrcode_path',$qrcode_path)
                        ->with('barcode_path',$barcode_path)
                        ->with('is_generated',$is_generated);
        return redirect()->back()->with('error', 'No action selected');
    }

    /**
     * Generate QR and Bar Code.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return View
     * @internal param int $assetId
     * @since [v2.0]
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function generateqr($accessoryId)
    {
        $sql_1 = "SELECT * from accessories where id = $accessoryId";
        $data['accessories'] = DB::select($sql_1);
        $data['settings'] = Setting::getSettings();
        $data['count'] = 0;
        return view('accessories/generatelabel', $data);
    }

    /**
     * Bulk Genearate Label.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return View
     * @internal param int $assetId
     * @since [v2.0]
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function generateMultipleLabel(Request $request)
    {
        if (!$request->filled('ids')) {
            return redirect()->back()->with('error', 'No assets selected');
        }
        $asset_ids = array_values(array_unique($request->input('ids')));
        if ($request->filled('bulk_actions')) {
            switch($request->input('bulk_actions')) {
                case 'labels':
                    $lists = Accessory::find($asset_ids);
                    return view('accessories/bulkgenerate')
                        ->with('lists', $lists)
                        ->with('settings', Setting::getSettings())
                        ->with('bulkedit', true)
                        ->with('count', 0);
                case 'delete':
                    $assets = Accessory::with('assignedTo', 'location')->find($asset_ids);
                    $assets->each(function ($asset) {
                        $this->authorize('delete', $asset);
                    });
                    return view('hardware/bulk-delete')->with('assets', $assets);
                case 'edit':
                    return view('hardware/bulk')
                        ->with('assets', $asset_ids)
                        ->with('statuslabel_list', Helper::statusLabelList());
            }
        }
        return redirect()->back()->with('error', 'No action selected');
    }
}
