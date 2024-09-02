<?php
namespace App\Http\Controllers\Assets;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\CheckoutRequest;
use App\Models\Company;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use DB;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Input;
use League\Csv\Reader;
use League\Csv\Statement;
use Paginator;
use Redirect;
use Response;
use Slack;
use Str;
use TCPDF;
use View;
use App\Models\StatusMapping;
use App\Models\UserGroups;
use App\Models\Group;
use App\Models\Statuslabel;
use App\Models\CarrierModel;

/**
 * This class controls all actions related to assets for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 * @author [A. Gianotto] [<snipe@snipe.net>]
 */
class AssetsController extends Controller
{
    protected $qrCodeDimensions = array( 'height' => 3.5, 'width' => 3.5);
    protected $barCodeDimensions = array( 'height' => 2, 'width' => 22);


    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the assets listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see AssetController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     * @param Request $request
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        $this->authorize('index', Asset::class);
        //updated by @author prabinthapamagar
        $company = Company::find($request->input('company_id'));
        $checkout_allowed = '1';
        $current_user_id = Auth::user()->id;
        $status_labels_lists = Statuslabel::get();
        $locations = Location::get();
        $carriers = CarrierModel::get();
        if($current_user_id != '1'){
            $current_user_group = Usergroups::where('user_id',$current_user_id)->get();
            $current_user_group_id = $current_user_group[0]->group_id;
            $current_user_group_permissions = Group::where('id',$current_user_group_id)->get();
            $current_user_group_permission = $current_user_group_permissions[0]->permissions;
            $json_decode_current_user_group_permission = json_decode($current_user_group_permission,true);
            $checkout_allowed = $json_decode_current_user_group_permission['assets.checkout'];
        }
        if($checkout_allowed == '0' && $current_user_id != '1'){
            $disabled_checkout = 'disabled';
        }
        else{
            $disabled_checkout = '';
        }
        return view('hardware/index')->with('company', $company)->with('disabled_checkout', $disabled_checkout)->with('current_user_id', $current_user_id)->with('status_labels_lists', $status_labels_lists)->with('locations', $locations)->with('carriers', $carriers)->with('statuslabel_list', Helper::statusLabelListByPosition());
    }

    /**
     * Returns a view that presents a form to create a new asset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @param Request $request
     * @return View
     * @internal param int $model_id
     */
    public function create(Request $request)
    {
        $this->authorize('create', Asset::class);
        $view = View::make('hardware/create')
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
     * Validate and process new asset form data.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @return Redirect
     */
    public function store(ImageUploadRequest $request)
    {
        $this->authorize(Asset::class);

        // Handle asset tags - there could be one, or potentially many.
        // This is only necessary on create, not update, since bulk editing is handled
        // differently
        $asset_tags = $request->input('asset_tags');
        $product_name = $request->input('product_name');
        $order_number = $request->input('order_number');
        $settings = Setting::getSettings();

        $success = false;
        $serials = $request->input('serials');

        for ($a = 1; $a <= count($asset_tags); $a++) {

            $asset = new Asset();
            $asset->model()->associate(AssetModel::find($request->input('model_id')));

            // Check for a corresponding serial
            if (($serials) && (array_key_exists($a, $serials))) {
                $asset->serial                  = $serials[$a];
            }

            if (($asset_tags) && (array_key_exists($a, $asset_tags))) {
                $asset->asset_tag                  = $asset_tags[$a];
            }
            if (($product_name) && (array_key_exists($a, $product_name))) {
                $asset->name                  = $product_name[$a];
            }
            if (($order_number) && (array_key_exists($a, $order_number))) {
                $asset->order_number                  = $order_number[$a];
            }
            $asset->company_id              = Company::getIdForCurrentUser($request->input('company_id'));
            $asset->model_id                = $request->input('model_id');
            $asset->notes                   = $request->input('notes');
            $asset->user_id                 = Auth::id();
            $asset->archived                = '0';
            $asset->physical                = '1';
            $asset->depreciate              = '0';
            $asset->status_id               = request('status_id', 0);
            $asset->warranty_months         = request('warranty_months', null);
            $asset->purchase_cost           = Helper::ParseCurrency($request->get('purchase_cost'));
            $asset->purchase_date           = request('purchase_date', null);
            $asset->assigned_to             = request('assigned_to', null);
            $asset->supplier_id             = request('supplier_id', 0);
            $asset->requestable             = request('requestable', 0);
            $asset->rtd_location_id         = request('rtd_location_id', null);

            if (!empty($settings->audit_interval)) {
                $asset->next_audit_date         = Carbon::now()->addMonths($settings->audit_interval)->toDateString();
            }

            if ($asset->assigned_to=='') {
                $asset->location_id = $request->input('rtd_location_id', null);
            }
            // Create the image (if one was chosen.)
            //if ($request->has('image')) {
                if(request('similar_name') == '1'){
                    $asset = $request->handleImages($asset,600, 'image1', null, 'image');
                }
                else{
                    $asset = $request->handleImages($asset,600, 'image'.$a.'', null, 'image');
                }
            //}

            // Update custom fields in the database.
            // Validation for these fields is handled through the AssetRequest form request
            $model = AssetModel::find($request->get('model_id'));

            if (($model) && ($model->fieldset)) {
                foreach ($model->fieldset->fields as $field) {
                    if ($field->field_encrypted=='1') {
                        if (Gate::allows('admin')) {
                            if(is_array($request->input($field->convertUnicodeDbSlug()))){
                                $asset->{$field->convertUnicodeDbSlug()} = \Crypt::encrypt(e(implode(', ', $request->input($field->convertUnicodeDbSlug()))));
                            }else{
                                $asset->{$field->convertUnicodeDbSlug()} = \Crypt::encrypt(e($request->input($field->convertUnicodeDbSlug())));
                            }                        }
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

                if (request('assigned_user')) {
                    $target = User::find(request('assigned_user'));
                    $location = $target->location_id;
                } elseif (request('assigned_asset')) {
                    $target = Asset::find(request('assigned_asset'));
                    $location = $target->location_id;
                } elseif (request('assigned_location')) {
                    $target = Location::find(request('assigned_location'));
                    $location = $target->id;
                }

                // if (isset($target)) {
                //     $asset->checkOut($target, Auth::user(), date('Y-m-d H:i:s'), $request->input('expected_checkin', null), 'Checked out on asset creation', $request->get('name'), $location);
                // }

                $success = true;


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
     * Returns a view that presents a form to edit an existing asset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return View
     */
    public function edit($assetId = null)
    {
        if (!$item = Asset::find($assetId)) {
            // Redirect to the asset management page with error
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }
        //Handles company checks and permissions.
        $this->authorize($item);
        //updated by @author:prabinthapamagar
        return view('hardware/edit', compact('item'))
            ->with('statuslabel_list', Helper::statusLabelListByPosition())
            ->with('statuslabel_types', Helper::statusTypeList());
    }


    /**
     * Returns a view that presents information about an asset for detail view.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return View
     */
    public function show($assetId = null)
    {
        $asset = Asset::withTrashed()->find($assetId);
        $this->authorize('view', $asset);
        $settings = Setting::getSettings();

        if (isset($asset)) {
            $audit_log = Actionlog::where('action_type', '=', 'audit')
                ->where('item_id', '=', $assetId)
                ->where('item_type', '=', Asset::class)
                ->orderBy('created_at', 'DESC')->first();

            if ($asset->location) {
                $use_currency = $asset->location->currency;
            } else {
                if ($settings->default_currency!='') {
                    $use_currency = $settings->default_currency;
                } else {
                    $use_currency = trans('general.currency');
                }
            }

            $qr_code = (object) array(
                'display' => $settings->qr_code == '1',
                'url' => route('qr_code/hardware', $asset->id)
            );

            return view('hardware/view', compact('asset', 'qr_code', 'settings'))
                ->with('use_currency', $use_currency)->with('audit_log', $audit_log);
        }

        return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
    }


    /**
     * Validate and process asset edit form.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return Redirect
     */

    public function update(ImageUploadRequest $request, $assetId = null)
    {
        // Check if the asset exists
        if (!$asset = Asset::find($assetId)) {
            // Redirect to the asset management page with error
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }
        $this->authorize($asset);
        $shipment_number = $asset->shipment_number;
        $current_user_id = Auth::user()->id;
        $status_id = $request->input('status_id', null);
        $asset->status_id = $status_id;
        $asset->warranty_months = $request->input('warranty_months', null);
        $asset->purchase_cost = Helper::ParseCurrency($request->input('purchase_cost', null));
        $asset->purchase_date = $request->input('purchase_date', null);
        $asset->supplier_id = $request->input('supplier_id', null);
        $asset->expected_checkin = $request->input('expected_checkin', null);
        // If the box isn't checked, it's not in the request at all.
        $asset->requestable = $request->filled('requestable');
        $asset->rtd_location_id = $request->input('rtd_location_id', null);

        if ($asset->assigned_to=='') {
            $asset->location_id = $request->input('rtd_location_id', null);
        }


        if ($request->filled('image_delete')) {
            try {
                unlink(public_path().'/uploads/assets/'.$asset->image);
                $asset->image = '';
            } catch (\Exception $e) {
                \Log::info($e);
            }

        }

        
        // Update the asset data
        $asset_tag           =  $request->input('asset_tags');        
        $serial              = $request->input('serials', null);
        $asset->name         = $request->input('name');
        //$asset->serial       = $serial[1];
        $asset->company_id   = Company::getIdForCurrentUser($request->input('company_id'));
        $asset->model_id     = $request->input('model_id');
        $asset->order_number = $request->input('order_number');
        $asset->asset_tag    = $asset_tag[1];
        $asset->notes        = $request->input('notes');
        $asset->physical     = '1';
        
        $asset = $request->handleImages($asset);

        // Update custom fields in the database.
        // Validation for these fields is handlded through the AssetRequest form request
        // FIXME: No idea why this is returning a Builder error on db_column_name.
        // Need to investigate and fix. Using static method for now.
        $model = AssetModel::find($request->get('model_id'));
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


        if ($asset->save()) {
            //updated by @author prabinthapamagar
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
            //end updated by @author prabinthapamagar
            //updated by @author madanBiz
            $sync_message = '';
            if(isset($asset->status_id) && isset($asset->shipment_number)){
                $sync_message = StatusMapping::syncShipmentStatus($asset->shipment_number, $asset->status_id);
            }
            return redirect()->route("hardware.show", $assetId)
                ->with('success', trans('admin/hardware/message.update.success'))
                ->with('message', $sync_message);
            //end updated by @author madanBiz
        }

        return redirect()->back()->withInput()->withErrors($asset->getErrors());
    }

    /**
     * Delete a given asset (mark as deleted).
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return Redirect
     */
    public function destroy($assetId)
    {
        // Check if the asset exists
        if (is_null($asset = Asset::find($assetId))) {
            // Redirect to the asset management page with error
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        $this->authorize('delete', $asset);

        DB::table('assets')
            ->where('id', $asset->id)
            ->update(array('assigned_to' => null));

        if ($asset->image) {
            try  {
                Storage::disk('public')->delete('assets'.'/'.$asset->image);
            } catch (\Exception $e) {
                \Log::debug($e);
            }
        }

        $asset->delete();

        return redirect()->route('hardware.index')->with('success', trans('admin/hardware/message.delete.success'));
    }



    /**
     * Searches the assets table by asset tag, and redirects if it finds one
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return Redirect
     */
    public function getAssetByTag(Request $request)
    {
        $topsearch = ($request->get('topsearch')=="true");

        if (!$asset = Asset::where('asset_tag', '=', $request->get('assetTag'))->first()) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }
        $this->authorize('view', $asset);
        return redirect()->route('hardware.show', $asset->id)->with('topsearch', $topsearch);
    }
    
    /**
     * Return a QR code for the asset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return Response
     */
    public function getQrCode($assetId = null)
    {
        $settings = Setting::getSettings();

        if ($settings->qr_code == '1') {
            $asset = Asset::withTrashed()->find($assetId);
            if ($asset) {
                $size = Helper::barcodeDimensions($settings->barcode_type);
                $qr_file = public_path().'/uploads/barcodes/qr-'.str_slug($asset->asset_tag).'-'.str_slug($asset->id).'.png';

                if (isset($asset->id, $asset->asset_tag)) {
                    if (file_exists($qr_file)) {
                        $header = ['Content-type' => 'image/png'];
                        return response()->file($qr_file, $header);
                    } else {
                        $barcode = new \Com\Tecnick\Barcode\Barcode();
                        $barcode_obj =  $barcode->getBarcodeObj($settings->barcode_type, route('hardware.show', $asset->id), $size['height'], $size['width'], 'black', array(-2, -2, -2, -2));
                        file_put_contents($qr_file, $barcode_obj->getPngData());
                        return response($barcode_obj->getPngData())->header('Content-type', 'image/png');
                    }
                }
            }
            return 'That asset is invalid';
        }
    }


    /**
     * Return a 2D barcode for the asset
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return Response
     */
    public function getBarCode($assetId = null)
    {
        $settings = Setting::getSettings();
        $asset = Asset::find($assetId);
        $barcode_file = public_path().'/uploads/barcodes/'.str_slug($settings->alt_barcode).'-'.str_slug($asset->asset_tag).'.png';

        if (isset($asset->id, $asset->asset_tag)) {
            if (file_exists($barcode_file)) {
                $header = ['Content-type' => 'image/png'];
                return response()->file($barcode_file, $header);
            } else {
                // Calculate barcode width in pixel based on label width (inch)
                $barcode_width = ($settings->labels_width - $settings->labels_display_sgutter) * 200.000000000001;

                $barcode = new \Com\Tecnick\Barcode\Barcode();
                try {
                    $barcode_obj = $barcode->getBarcodeObj($settings->alt_barcode,$asset->asset_tag,($barcode_width < 300 ? $barcode_width : 300),50);
                    file_put_contents($barcode_file, $barcode_obj->getPngData());
                    return response($barcode_obj->getPngData())->header('Content-type', 'image/png');
                } catch(\Exception $e) {
                    \Log::debug('The barcode format is invalid.');
                    return response(file_get_contents(public_path('uploads/barcodes/invalid_barcode.gif')))->header('Content-type', 'image/gif');
                }


            }
        }
    }


    /**
     * Return a label for an individual asset.
     *
     * @author [L. Swartzendruber] [<logan.swartzendruber@gmail.com>
     * @param int $assetId
     * @return View
     */
    public function getLabel($assetId = null)
    {
        if (isset($assetId)) {
            $asset = Asset::find($assetId);
            $this->authorize('view', $asset);

            return view('hardware/labels')
                ->with('assets', Asset::find($asset))
                ->with('settings', Setting::getSettings())
                ->with('bulkedit', false)
                ->with('count', 0);
        }
    }


    /**
     * Returns a view that presents a form to clone an asset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return View
     */
    public function getClone($assetId = null)
    {
        // Check if the asset exists
        if (is_null($asset_to_clone = Asset::find($assetId))) {
            // Redirect to the asset management page
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        $this->authorize('create', $asset_to_clone);

        $asset = clone $asset_to_clone;
        $asset->id = null;
        $asset->asset_tag = '';
        $asset->serial = '';
        $asset->assigned_to = '';

        return view('hardware/edit')
            ->with('statuslabel_list', Helper::statusLabelList())
            ->with('statuslabel_types', Helper::statusTypeList())
            ->with('item', $asset);
    }

    /**
     * Return history import view
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     * @return View
     */
    public function getImportHistory()
    {
        $this->authorize('admin');
        return view('hardware/history');
    }

    /**
     * Import history
     *
     * This needs a LOT of love. It's done very inelegantly right now, and there are
     * a ton of optimizations that could (and should) be done.
     * 
     * Updated to respect checkin dates:
     * No checkin column, assume all items are checked in (todays date)
     * Checkin date in the past, update history.
     * Checkin date in future or empty, check the item out to the user.
     * 
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.3]
     * @return View
     */
    public function postImportHistory(Request $request)
    {

        if (!$request->hasFile('user_import_csv')) {
            return back()->with('error', 'No file provided. Please select a file for import and try again. ');
        }

        if (!ini_get("auto_detect_line_endings")) {
            ini_set("auto_detect_line_endings", '1');
        }
        $csv = Reader::createFromPath($request->file('user_import_csv'));
        $csv->setHeaderOffset(0);
        $header = $csv->getHeader();
        $isCheckinHeaderExplicit = in_array("checkin date", (array_map('strtolower', $header)));
        $results = $csv->getRecords();
        $item = array();
        $status = array();
        $status['error'] = array();
        $status['success'] = array();
        foreach ($results as $row) {
            if (is_array($row)) {
                $row = array_change_key_case($row, CASE_LOWER);
                $asset_tag = Helper::array_smart_fetch($row, "asset tag");
                if (!array_key_exists($asset_tag, $item)) {
                    $item[$asset_tag] = array();
                }
                $batch_counter = count($item[$asset_tag]);
                $item[$asset_tag][$batch_counter]['checkout_date'] = Carbon::parse(Helper::array_smart_fetch($row, "checkout date"))->format('Y-m-d H:i:s');
    
                if ($isCheckinHeaderExplicit){
                    //checkin date not empty, assume past transaction or future checkin date (expected)
                    if (!empty(Helper::array_smart_fetch($row, "checkin date"))) {
                        $item[$asset_tag][$batch_counter]['checkin_date'] = Carbon::parse(Helper::array_smart_fetch($row, "checkin date"))->format('Y-m-d H:i:s');
                    } else {
                        $item[$asset_tag][$batch_counter]['checkin_date'] = '';
                    }
                } else {
                    //checkin header missing, assume data is unavailable and make checkin date explicit (now) so we don't encounter invalid state.
                    $item[$asset_tag][$batch_counter]['checkin_date'] = Carbon::parse(now())->format('Y-m-d H:i:s');
                }

                $item[$asset_tag][$batch_counter]['asset_tag'] = Helper::array_smart_fetch($row, "asset tag");
                $item[$asset_tag][$batch_counter]['name'] = Helper::array_smart_fetch($row, "name");
                $item[$asset_tag][$batch_counter]['email'] = Helper::array_smart_fetch($row, "email");
                if ($asset = Asset::where('asset_tag', '=', $asset_tag)->first()) {
                    $item[$asset_tag][$batch_counter]['asset_id'] = $asset->id;
                    $base_username = User::generateFormattedNameFromFullName(Setting::getSettings()->username_format, $item[$asset_tag][$batch_counter]['name']);
                    $user = User::where('username', '=', $base_username['username']);
                    $user_query = ' on username '.$base_username['username'];
                    if ($request->input('match_firstnamelastname')=='1') {
                        $firstnamedotlastname = User::generateFormattedNameFromFullName('firstname.lastname', $item[$asset_tag][$batch_counter]['name']);
                        $item[$asset_tag][$batch_counter]['username'][] = $firstnamedotlastname['username'];
                        $user->orWhere('username', '=', $firstnamedotlastname['username']);
                        $user_query .= ', or on username '.$firstnamedotlastname['username'];
                    }
                    if ($request->input('match_flastname')=='1') {
                        $flastname = User::generateFormattedNameFromFullName('filastname', $item[$asset_tag][$batch_counter]['name']);
                        $item[$asset_tag][$batch_counter]['username'][] = $flastname['username'];
                        $user->orWhere('username', '=', $flastname['username']);
                        $user_query .= ', or on username '.$flastname['username'];
                    }
                    if ($request->input('match_firstname')=='1') {
                        $firstname = User::generateFormattedNameFromFullName('firstname', $item[$asset_tag][$batch_counter]['name']);
                        $item[$asset_tag][$batch_counter]['username'][] = $firstname['username'];
                        $user->orWhere('username', '=', $firstname['username']);
                        $user_query .= ', or on username '.$firstname['username'];
                    }
                    if ($request->input('match_email')=='1') {
                        if ($item[$asset_tag][$batch_counter]['name']=='') {
                            $item[$asset_tag][$batch_counter]['username'][] = $user_email = User::generateEmailFromFullName($item[$asset_tag][$batch_counter]['name']);
                            $user->orWhere('username', '=', $user_email);
                            $user_query .= ', or on username '.$user_email;
                        }
                    }
                    if ($request->input('match_username') == '1'){
                        // Added #8825: add explicit username lookup
                           $raw_username = $item[$asset_tag][$batch_counter]['name'];
                           $user->orWhere('username', '=', $raw_username);
                            $user_query .= ', or on username ' . $raw_username;
                    }

                    // A matching user was found
                    if ($user = $user->first()) {
                        //$user is now matched user from db
                        $item[$asset_tag][$batch_counter]['user_id'] = $user->id;

                        Actionlog::firstOrCreate(array(
                            'item_id' => $asset->id,
                            'item_type' => Asset::class,
                            'user_id' =>  Auth::user()->id,
                            'note' => 'Checkout imported by '.Auth::user()->present()->fullName().' from history importer',
                            'target_id' => $item[$asset_tag][$batch_counter]['user_id'],
                            'target_type' => User::class,
                            'created_at' =>  $item[$asset_tag][$batch_counter]['checkout_date'],
                            'action_type'   => 'checkout',
                        ));

                        $checkin_date = $item[$asset_tag][$batch_counter]['checkin_date'];

                        if ($isCheckinHeaderExplicit) {

                            //if checkin date header exists, assume that empty or future date is still checked out
                            //if checkin is before todays date, assume it's checked in and do not assign user ID, if checkin date is in the future or blank, this is the expected checkin date, items is checked out
                            
                            if ((strtotime($checkin_date) > strtotime(Carbon::now())) || (empty($checkin_date))
                            ) {
                                //only do this if item is checked out
                                $asset->assigned_to = $user->id;
                                $asset->assigned_type = User::class;
                            }
                        }

                        if (!empty($checkin_date)) {
                            //only make a checkin there is a valid checkin date or we created one on import.
                            Actionlog::firstOrCreate(array(
                                'item_id' =>
                                $item[$asset_tag][$batch_counter]['asset_id'],
                                'item_type' => Asset::class,
                                'user_id' => Auth::user()->id,
                                'note' => 'Checkin imported by ' . Auth::user()->present()->fullName() . ' from history importer',
                                'target_id' => null,
                                'created_at' => $checkin_date,
                                'action_type' => 'checkin'
                            ));

                        }
                     
                        if ($asset->save()) {
                            $status['success'][]['asset'][$asset_tag]['msg'] = 'Asset successfully matched for '.Helper::array_smart_fetch($row, "name").$user_query.' on '.$item[$asset_tag][$batch_counter]['checkout_date'];
                        } else {
                            $status['error'][]['asset'][$asset_tag]['msg'] = 'Asset and user was matched but could not be saved.';
                        }
                    } else {
                        $item[$asset_tag][$batch_counter]['user_id'] = null;
                        $status['error'][]['user'][Helper::array_smart_fetch($row, "name")]['msg'] = 'User does not exist so no checkin log was created.';
                    }
                } else {
                    $item[$asset_tag][$batch_counter]['asset_id'] = null;
                    $status['error'][]['asset'][$asset_tag]['msg'] = 'Asset does not exist so no match was attempted.';
                }
            }
        }
        return view('hardware/history')->with('status', $status);
    }

    public function sortByName(array $recordA, array $recordB): int
    {
        return strcmp($recordB['Full Name'], $recordA['Full Name']);
    }

    /**
     * Retore a deleted asset.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return View
     */
    public function getRestore($assetId = null)
    {
        // Get asset information
        $asset = Asset::withTrashed()->find($assetId);
        $this->authorize('delete', $asset);
        if (isset($asset->id)) {
            // Restore the asset
            Asset::withTrashed()->where('id', $assetId)->restore();

            $logaction = new Actionlog();
            $logaction->item_type = Asset::class;
            $logaction->item_id = $asset->id;
            $logaction->created_at =  date("Y-m-d H:i:s");
            $logaction->user_id = Auth::user()->id;
            $logaction->logaction('restored');

            return redirect()->route('hardware.index')->with('success', trans('admin/hardware/message.restore.success'));
        }
        return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
    }

    public function quickScan()
    {
        $this->authorize('audit', Asset::class);
        $dt = Carbon::now()->addMonths(12)->toDateString();
        return view('hardware/quickscan')->with('next_audit_date', $dt);
    }



    public function audit($id)
    {
        $settings = Setting::getSettings();
        $this->authorize('audit', Asset::class);
        $dt = Carbon::now()->addMonths($settings->audit_interval)->toDateString();
        $asset = Asset::findOrFail($id);
        return view('hardware/audit')->with('asset', $asset)->with('next_audit_date', $dt)->with('locations_list');
    }

    public function dueForAudit()
    {
        $this->authorize('audit', Asset::class);
        return view('hardware/audit-due');
    }

    public function overdueForAudit()
    {
        $this->authorize('audit', Asset::class);
        return view('hardware/audit-overdue');
    }


    public function auditStore(Request $request, $id)
    {
        $this->authorize('audit', Asset::class);

        $rules = array(
            'location_id' => 'exists:locations,id|nullable|numeric',
            'next_audit_date' => 'date|nullable'
        );

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(Helper::formatStandardApiResponse('error', null, $validator->errors()->all()));
        }

        $asset = Asset::findOrFail($id);

        // We don't want to log this as a normal update, so let's bypass that
        $asset->unsetEventDispatcher();

        $asset->next_audit_date = $request->input('next_audit_date');
        $asset->last_audit_date = date('Y-m-d H:i:s');

        // Check to see if they checked the box to update the physical location,
        // not just note it in the audit notes
        if ($request->input('update_location')=='1') {
            \Log::debug('update location in audit');
            $asset->location_id = $request->input('location_id');
        }


        if ($asset->save()) {
            $file_name = '';
            // Upload an image, if attached
            if ($request->hasFile('image')) {
                $path = 'private_uploads/audits';
                if (!Storage::exists($path)) Storage::makeDirectory($path, 775);
                $upload = $image = $request->file('image');
                $ext = $image->getClientOriginalExtension();
                $file_name = 'audit-'.str_random(18).'.'.$ext;
                Storage::putFileAs($path, $upload, $file_name);
            }


            $asset->logAudit($request->input('note'), $request->input('location_id'), $file_name);
            return redirect()->to("hardware")->with('success', trans('admin/hardware/message.audit.success'));
        }
    }

    public function getRequestedIndex($user_id = null)
    {
        $requestedItems = CheckoutRequest::with('user', 'requestedItem')->whereNull('canceled_at')->with('user', 'requestedItem');

        if ($user_id) {
            $requestedItems->where('user_id', $user_id)->get();
        }

        $requestedItems = $requestedItems->orderBy('created_at', 'desc')->get();

        return view('hardware/requested', compact('requestedItems'));
    }

    /**
     * Show Bulk Checkin Page
     * @return View View to checkout multiple assets
     */
    public function showCheckin()
    {
        
    }

    /**
     * Searches the assets table by asset shipment number, and redirects if it finds one
     *
     * @author prabinthapamagar
     * @since [v3.0]
     * @return Redirect
     */
    public function getAssetByShipmentNumber(Request $request)
    {
        $topsearch = ($request->get('topsearch')=="true");

        if (!$asset = Asset::where('barcode_number', '=', $request->get('assetShipmentNumber'))->first()) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }
        $this->authorize('view', $asset);
        return redirect()->route('hardware.show', $asset->id)->with('topsearch', $topsearch);
    }

    /**
     * Update From CS Cart.
     *
     * @author prabinthapamagar
     * @param int $assetId
     * @since [v1.0]
     * @return Redirect
     */

    public function updateFromCsCart(Request $request)
    {
        //shipment_number and carrier_id from cs cart
        $shipment_number = $request->get('shipment_number');
        $carrier_id = $request->get('carrier_id');
        $shipment_details = Asset::where('shipment_number',$shipment_number)->get();
        $assetId = $shipment_details[0]->id;
        // Check if the asset exists
        if (!$asset = Asset::find($assetId)) {
            // Redirect to the asset management page with error
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }
        $this->authorize($asset);
        $asset->status_id = 3;
        $asset->physical     = '1';
        
        $asset = $request->handleImages($asset);

        // Update custom fields in the database.
        // Validation for these fields is handlded through the AssetRequest form request
        // FIXME: No idea why this is returning a Builder error on db_column_name.
        // Need to investigate and fix. Using static method for now.
        $model_id = $shipment_details->model_id();
        $model = AssetModel::find( $model_id);
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

        $barcode_number = $shipment_details[0]->barcode_number;
        if ($asset->save()) {
            $json = array(
                'status' => 'success',
                'message' => 'Shipment Number '.$barcode_number.' Status updated to delivered',
            );
        }
        else{
            $json = array(
                'status' => 'error',
                'message' => 'Shipment Number '.$barcode_number.' Status update error',
            );
        }
        return response()->json($json);
    }

    /**
     * Insert Data From CS Cart.
     *
     * @author prabinthapamagar
     * @param int $assetId
     * @since [v1.0]
     * @return Redirect
     */

    public function createFromCsCart(Request $request)
    {
        //fields required from cs cart => shipment_number , product_name , order_number , barcode_number , category_name , quantity
        $asset = new Asset();
        $asset->model()->associate(AssetModel::find(1));
        $shipment_number = $request->get('shipment_number');
        $asset->asset_tag = $shipment_number;
        $asset->name = $request->get('product_name');
        $asset->supplier_id = 1;
        $asset->order_number = $request->get('order_number');
        $asset->model_id = 3;
        $asset->user_id = 1;
        $asset->archived = '0';
        $asset->physical  = '1';
        $asset->depreciate = '0';
        $asset->status_id = 5;
        $asset->assigned_to = request('assigned_to', null);
        $asset->requestable = request('requestable', 0);
        $asset->rtd_location_id = request('rtd_location_id', null);
        $asset->barcode_number = $request->get('barcode_number');
        $asset->shipment_number = $shipment_number;
        $asset->category_name = $request->get('category_name');
        $asset->quantity = $request->get('quantity');
        $user_details = User::where('id',1)->get();
        $asset->location_id = $user_details[0]->location_id;
        $asset->company_id = $user_details[0]->company_id;
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
            $success = true;
        }else {

            dd(DB::getQueryLog());

            //dd($asset);

        }
        if ($success) {
            $json = array(
                'status' => 'success',
                'message' => 'Asset Successfully Created',
            );
        }
        else{
            $json = array(
                'status' => 'error',
                'message' => 'Error while creating asset',
            );
        }
        return response()->json($json);
    }

    /**
     * Generate Bar Code for Asset Tag
     *
     * @author prabinthapamagar
     * @param int $assetId
     * @since [v1.0]
     * @return Response
     */
    public function getBarCodeTag($assetId = null)
    {
        $settings = Setting::getSettings();
        $asset = Asset::find($assetId);
        $barcode_file = public_path().'/uploads/barcodes/'.$asset->asset_tag.'.png';

        if (isset($asset->id, $asset->asset_tag)) {
            if (file_exists($barcode_file)) {
                $header = ['Content-type' => 'image/png'];
                return response()->file($barcode_file, $header);
            } else {
                // Calculate barcode width in pixel based on label width (inch)
                $barcode_width = ($settings->labels_width - $settings->labels_display_sgutter) * 200.000000000001;

                $barcode = new \Com\Tecnick\Barcode\Barcode();
                try {
                    $barcode_obj = $barcode->getBarcodeObj($settings->alt_barcode,$asset->asset_tag,($barcode_width < 300 ? $barcode_width : 300),50);
                    file_put_contents($barcode_file, $barcode_obj->getPngData());
                    return response($barcode_obj->getPngData())->header('Content-type', 'image/png');
                } catch(\Exception $e) {
                    \Log::debug('The barcode format is invalid.');
                    return response(file_get_contents(public_path('uploads/barcodes/invalid_barcode.gif')))->header('Content-type', 'image/gif');
                }


            }
        }
    }

    /**
     * Return a label for an individual asset.
     *
     * @author [L. Swartzendruber] [<logan.swartzendruber@gmail.com>
     * @param int $assetId
     * @return View
     */
    public function getLabelTag($asset_ids)
    {
        return view('hardware/labels_tag')
            ->with('settings', Setting::getSettings())
            ->with('bulkedit', false)
            ->with('count', 0);
    }
}
