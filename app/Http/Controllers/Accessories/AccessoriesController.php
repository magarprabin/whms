<?php
namespace App\Http\Controllers\Accessories;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use App\Models\Accessory;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Redirect;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use App\Models\User;

/** This controller handles all actions related to Accessories for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class AccessoriesController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the accessories listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see AccessoriesController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('index', Accessory::class);
        return view('accessories/index');
    }


    /**
     * Returns a view with a form to create a new Accessory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create()
    {
        $this->authorize('create', Accessory::class);
        $category_type = 'accessory';
        $current_user_id = Auth::user()->id;
        $current_user_location_id = Auth::user()->location_id;
        return view('accessories/create')->with('category_type', $category_type)
          ->with('item', new Accessory)->with('location_id',$current_user_location_id)->with('form_type','add');
    }


    /**
     * Validate and save new Accessory from form post
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param ImageUploadRequest $request
     * @return Redirect
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(ImageUploadRequest $request)
    {
        $this->authorize(Accessory::class);
        $inventory_tags = $request->input('inventory_tags');
        $inventory_name = $request->input('name');
        //$category_id = $request->input('category_id');
        $model_number = $request->input('model_number');
        $success = false;
        $settings = Setting::getSettings();
        for ($a = 1; $a <= count($inventory_tags); $a++) {
            // create a new model instance
            $accessory = new Accessory();

            if (($inventory_tags) && (array_key_exists($a, $inventory_tags))) {
                $accessory->inventory_tag   =   $inventory_tags[$a];
            }
            if (($inventory_name) && (array_key_exists($a, $inventory_name))) {
                $accessory->name   =   $inventory_name[$a];
            }
            if(request('similar_name') == '1' && $a > 1){
                $accessory->name   =   $inventory_name[1];
            }
            if (($model_number) && (array_key_exists($a, $model_number))) {
                $accessory->model_number   =   $model_number[$a];
            }
            // Update the accessory data
            $accessory->category_id             = request('category_id');
            $accessory->location_id             = request('location_id');
            $accessory->min_amt                 = '1';
            $accessory->company_id              = Company::getIdForCurrentUser(request('company_id'));
            $accessory->purchase_date           = request('purchase_date');
            $accessory->qty                     = '1';
            $accessory->user_id                 = Auth::user()->id;
            $accessory->supplier_id             = request('supplier_id');
            $accessory->notes                   = request('notes');
            $accessory->physical                = '1';

            if(request('similar_name') == '1'){
                $accessory = $request->handleImages($accessory,600, 'image1', null, 'image');
            }
            else{
                $accessory = $request->handleImages($accessory,600, 'image'.$a.'', null, 'image');
            }
            if ($accessory->save()) {
                $success = true;
            }
            // Was the accessory created?
//            if ($accessory->save()) {
//                // Redirect to the new accessory  page
//                return redirect()->route('accessories.index')->with('success', trans('admin/accessories/message.create.success'));
//            }
        }
        if ($success) {
            return redirect()->route('accessories.index')->with('success', trans('admin/accessories/message.create.success'));
        }

        return redirect()->back()->withInput()->withErrors($accessory->getErrors());
    }

    /**
     * Return view for the Accessory update form, prepopulated with existing data
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $accessoryId
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit($accessoryId = null)
    {

        if ($item = Accessory::find($accessoryId)) {
            $this->authorize($item);
            return view('accessories/edit', compact('item'))->with('category_type', 'accessory')->with('form_type','edit');
        }

        return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.does_not_exist'));

    }


    /**
     * Save edited Accessory from form post
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param ImageUploadRequest $request
     * @param  int $accessoryId
     * @return Redirect
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(ImageUploadRequest $request, $accessoryId = null)
    {
        if (is_null($accessory = Accessory::find($accessoryId))) {
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.does_not_exist'));
        }

        $this->authorize($accessory);

        $inventory_tag = $request->input('inventory_tags');
        $model_number = $request->input('model_number');
        // Update the accessory data
        $accessory->name                    = request('name');
        $accessory->location_id             = request('location_id');
        $accessory->min_amt                 = request('min_amt');
        $accessory->category_id             = request('category_id');
        $accessory->company_id              = Company::getIdForCurrentUser(request('company_id'));
        $accessory->manufacturer_id         = request('manufacturer_id');
        $accessory->order_number            = request('order_number');
        $accessory->model_number            = $model_number;
        $accessory->purchase_date           = request('purchase_date');
        $accessory->purchase_cost           = Helper::ParseCurrency(request('purchase_cost'));
        $accessory->supplier_id             = request('supplier_id');
        $accessory->notes                   = request('notes');
        $accessory->inventory_tag           = $inventory_tag[1];

        $accessory = $request->handleImages($accessory);

        // Was the accessory updated?
        DB::enableQueryLog();
        if ($accessory->save()) {
            return redirect()->route('accessories.index')->with('success', trans('admin/accessories/message.update.success'));
        }
        return redirect()->back()->withInput()->withErrors($accessory->getErrors());
    }

    /**
     * Delete the given accessory.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $accessoryId
     * @return Redirect
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($accessoryId)
    {
        if (is_null($accessory = Accessory::find($accessoryId))) {
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.not_found'));
        }

        $this->authorize($accessory);


        if ($accessory->hasUsers() > 0) {
             return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.assoc_users', array('count'=> $accessory->hasUsers())));
        }

        if ($accessory->image) {
            try  {
                Storage::disk('public')->delete('accessories'.'/'.$accessory->image);
            } catch (\Exception $e) {
                \Log::debug($e);
            }
        }

        $accessory->delete();
        return redirect()->route('accessories.index')->with('success', trans('admin/accessories/message.delete.success'));
    }


    /**
     * Returns a view that invokes the ajax table which  contains
     * the content for the accessory detail view, which is generated in getDataView.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $accessoryID
     * @see AccessoriesController::getDataView() method that generates the JSON response
     * @since [v1.0]
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($accessoryID = null)
    {
        $accessory = Accessory::find($accessoryID);
        $this->authorize('view', $accessory);
        if (isset($accessory->id)) {
            return view('accessories/view', compact('accessory'));
        }
        return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.does_not_exist', ['id' => $accessoryID]));
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
            $asset = Accessory::withTrashed()->find($assetId);
            if ($asset) {
                $size = Helper::barcodeDimensions($settings->barcode_type);
                $qr_file = public_path().'/uploads/barcodes/qr-'.str_slug($asset->inventory_tag).'-'.str_slug($asset->id).'.png';

                if (isset($asset->id, $asset->inventory_tag)) {
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
        $asset = Accessory::find($assetId);
        $barcode_file = public_path().'/uploads/barcodes/'.str_slug($settings->alt_barcode).'-'.str_slug($asset->inventory_tag).'.png';

        if (isset($asset->id, $asset->inventory_tag)) {
            if (file_exists($barcode_file)) {
                $header = ['Content-type' => 'image/png'];
                return response()->file($barcode_file, $header);
            } else {
                // Calculate barcode width in pixel based on label width (inch)
                $barcode_width = ($settings->labels_width - $settings->labels_display_sgutter) * 200.000000000001;

                $barcode = new \Com\Tecnick\Barcode\Barcode();
                try {
                    $barcode_obj = $barcode->getBarcodeObj($settings->alt_barcode,$asset->inventory_tag,($barcode_width < 300 ? $barcode_width : 300),50);
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
    * Return Inventory Data.
    * @author prabin.thapa
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function showInventoryData(Request $request)
    {
        $accessory_name = "here";
        $json = array(
            'accessory_name' => $accessory_name
        );
        return response()->json($json);
    }
}
