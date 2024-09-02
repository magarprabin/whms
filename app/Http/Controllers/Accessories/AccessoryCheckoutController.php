<?php

namespace App\Http\Controllers\Accessories;

use App\Events\CheckoutableCheckedOut;
use App\Http\Controllers\Controller;
use App\Models\Accessory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Validator;
use App\Http\Transformers\AccessoriesTransformer;

class AccessoryCheckoutController extends Controller
{

    /**
     * Return the form to checkout an Accessory to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param  int $accessoryId
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create($accessoryId)
    {
        // Check if the accessory exists
        if (is_null($accessory = Accessory::find($accessoryId))) {
            // Redirect to the accessory management page with error
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.not_found'));
        }

        if ($accessory->category) {

            $this->authorize('checkout', $accessory);

            // Get the dropdown of users and then pass it to the checkout view
            return view('accessories/checkout', compact('accessory'));
        }

        return redirect()->back()->with('error', 'The category type for this accessory is not valid. Edit the accessory and select a valid accessory category.');
    }

    /**
     * Save the Accessory checkout information.
     *
     * If Slack is enabled and/or asset acceptance is enabled, it will also
     * trigger a Slack message and send an email.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param Request $request
     * @param  int $accessoryId
     * @return Redirect
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, $accessoryId)
    {
      // Check if the accessory exists
        if (is_null($accessory = Accessory::find($accessoryId))) {
            // Redirect to the accessory management page with error
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.user_not_found'));
        }

        $this->authorize('checkout', $accessory);

        if (!$user = User::find($request->input('assigned_to'))) {
            return redirect()->route('checkout/accessory', $accessory->id)->with('error', trans('admin/accessories/message.checkout.user_does_not_exist'));
        }

        $validator = Validator::make($request->all(), [
            'order_number' => 'required|numeric',
        ]);
        $validator->validated();

      // Update the accessory data
//        $accessory->assigned_to = e($request->input('assigned_to')); //This piece of code is causing sql update error

        $accessory->users()->attach($accessory->id, [
            'accessory_id' => $accessory->id,
            'order_number' => $request->input('order_number'),
            'created_at' => Carbon::now(),
            'user_id' => Auth::id(),
            'assigned_to' => $request->get('assigned_to'),
            'note' => $request->input('note')
        ]);
        $accessory->save();

        DB::table('accessories_users')->where('assigned_to', '=', $accessory->assigned_to)->where('accessory_id', '=', $accessory->id)->first();

        event(new CheckoutableCheckedOut($accessory, $user, Auth::user(), $request->input('note')));

      // Redirect to the new accessory page
        return redirect()->route('accessories.index')->with('success', trans('admin/accessories/message.checkout.success'));
    }

    /**
     * Print Label.
     *
     * @author prabinthapamagar
     * @param  int $accessoryId
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function printlabel($accessoryId)
    {
        // Check if the accessory exists
        if (is_null($accessory = Accessory::find($accessoryId))) {
            // Redirect to the accessory management page with error
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.not_found'));
        }

        if ($accessory->category) {

            $this->authorize('checkout', $accessory);

            // Get the dropdown of users and then pass it to the checkout view
            return view('accessories/checkout', compact('accessory'));
        }

        return redirect()->back()->with('error', 'The category type for this accessory is not valid. Edit the accessory and select a valid accessory category.');
    }
    
    /**
     * Scan the inventory.
     *
     * @author prabinthapamagar
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function scan()
    {
        $user_id = Auth::user()->id;
        return view('accessories/scanspecific')->with('user_id', $user_id);
    }

    /**
     * Save the Accessory checkout information.
     *
     * If Slack is enabled and/or asset acceptance is enabled, it will also
     * trigger a Slack message and send an email.
     *
     * @author prabinthapamagar
     * @param Request $request
     * @return Redirect
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function savescan(Request $request)
    {
      // Check if the accessory exists
        $accessoryId = $request->input('inventory_number');
        if (is_null($accessory = Accessory::find($accessoryId))) {
            // Redirect to the accessory management page with error
            return redirect()->route('accessories.index')->with('error', trans('admin/accessories/message.user_not_found'));
        }

        $this->authorize('checkout', $accessory);

        if (!$user = User::find($request->input('assigned_to'))) {
            return redirect()->route('checkout/accessory', $accessory->id)->with('error', trans('admin/accessories/message.checkout.user_does_not_exist'));
        }

        $validator = Validator::make($request->all(), [
            'order_number' => 'required|numeric',
        ]);
        $validator->validated();

      // Update the accessory data
      // $accessory->assigned_to = e($request->input('assigned_to')); //This piece of code is causing sql update error

        $accessory->users()->attach($accessory->id, [
            'accessory_id' => $accessory->id,
            'order_number' => $request->input('order_number'),
            'created_at' => Carbon::now(),
            'user_id' => Auth::id(),
            'assigned_to' => $request->get('assigned_to'),
            'note' => $request->input('note')
        ]);
        $accessory->save();

        DB::table('accessories_users')->where('assigned_to', '=', $accessory->assigned_to)->where('accessory_id', '=', $accessory->id)->first();

        event(new CheckoutableCheckedOut($accessory, $user, Auth::user(), $request->input('note')));

      // Redirect to the new accessory page
        return redirect()->route('accessories.index')->with('success', trans('admin/accessories/message.checkout.success'));
    }
    
    /**
     * Scan the inventory.
     *
     * @author prabinthapamagar
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function scanall()
    {
        $user_id = Auth::user()->id;
        $sql_1 = "SELECT ax.* from accessories ax 
                where ax.id in (select accessory_id from accessories_users)
                ";
        $query_1 = DB::select($sql_1);
        if(!empty($query_1)){
            foreach($query_1 as $key => $list){
                $id_2[$key] = $id_1 = $list->id;
                $qty_1[$key] = $list->qty;
                $sql_2 = "SELECT * from accessories_users where accessory_id = $id_2[$key]";
                $query_2[$key] = DB::select($sql_2);
                $count_2[$key] = count($query_2[$key]);
                if($count_2[$key] == $qty_1[$key]){
                    $id[] = $list->id;
                }
            }
        }
        $accessories = Accessory::select('accessories.*');
        $count = $accessories->count();
        if(isset($id)){
            $accessory = $accessories->whereNotIn('id', $id)->get();
        }
        else{
            $accessory = $accessories->get();
        }
        return view('accessories/scanall')->with('accessory', $accessory)->with('user_id', $user_id);
    }

    /**
     * Scan all inventory.
     *
     * If Slack is enabled and/or asset acceptance is enabled, it will also
     * trigger a Slack message and send an email.
     *
     * @author prabinthapamagar
     * @param Request $request
     * @return Redirect
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function savescanall(Request $request)
    {
        $ids = $request->input('accessory_id');
        $note = $request->input('note');
        $qty = $request->input('qty');
        $note = $request->input('note');
        foreach($ids as $key => $id){
            $accessoryId = $id;
            $accessory = Accessory::find($accessoryId);
            $user = User::find(Auth::id());
            $sql_1 = "SELECT * from accessories_users where accessory_id = $accessoryId";
            $query_1 = DB::select($sql_1);
            $count_1 = count($query_1);
            $j = $key + 1;
            if($count_1 > 0){
                $loop_count = $qty[$key] - $count_1;
            }
            else{
                $loop_count = $qty[$key];
            }
            for($i = 1; $i <= $loop_count; $i++){
                $accessory->users()->attach($accessory->id, [
                    'accessory_id' => $accessory->id,
                    'order_number' => 0,
                    'created_at' => Carbon::now(),
                    'user_id' => Auth::id(),
                    'assigned_to' => Auth::id(),
                    'note' => $note[$i]
                ]);
                $accessory->save();
        
                DB::table('accessories_users')->where('assigned_to', '=', $accessory->assigned_to)->where('accessory_id', '=', $accessory->id)->first();
        
                event(new CheckoutableCheckedOut($accessory, $user, Auth::user(), $note[$i]));   
            }
        }

        // Redirect to the new accessory page
        return redirect()->route('accessories.index')->with('success', trans('admin/accessories/message.checkout.success'));
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
        $inventory_number = $request->inventory_number;
        $accessory = Accessory::find($inventory_number);
        $accessory_name = $accessory->name;
        $category_name = $accessory->category->name;
        $inventory_tag = $accessory->inventory_tag;
        $quantity = $accessory->qty;
        $json = array(
            'accessory_name' => $accessory_name,
            'category_name' => $category_name,
            'inventory_tag' => $inventory_tag,
            'quantity' => $quantity
        );
        return response()->json($json);
    }

    /**
    * Show Inventory Data by Tag Number.
    * @author prabin.thapa
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function showInventoryDataByTag(Request $request)
    {
        $inventory_tag = $request->inventory_tag;
        $inventory_tag = trim($inventory_tag);
        $sql_1 = "SELECT * from accessories where inventory_tag = '$inventory_tag'";
        $accessory_details = DB::select($sql_1);
        if(!empty($accessory_details)){
            $inventory_number = $accessory_details[0]->id;
            $sql_2 = "SELECT * from accessories_users where accessory_id = '$inventory_number'";
            $checkout_details = DB::select($sql_2);
            $quantity = $accessory_details[0]->qty;
            if(!empty($checkout_details)){
                $count_checkout_details = count($checkout_details);
                if($quantity == $count_checkout_details){
                    $json = array(
                        'status' => 'error',
                        'message' => 'Zero available quantity for '.$inventory_tag.''
                    );
                }
            }
            else{
                $accessory_name = $accessory_details[0]->name;
                $category_id = $accessory_details[0]->category_id;
                $sql_3 = "SELECT * from categories where id = $category_id";
                $category_details = DB::select($sql_3);
                $category_name = $category_details[0]->name;
                $inventory_tag = $accessory_details[0]->inventory_tag;
                $status = $message = 'success';
                $json = array(
                    'accessory_name' => $accessory_name,
                    'category_name' => $category_name,
                    'inventory_tag' => $inventory_tag,
                    'quantity' => $quantity,
                    'inventory_number' => $inventory_number,
                    'status' => $status
                );
            }
        }
        else{
            $json = array(
                'status' => 'error',
                'message' => 'Invalid Inventory Tag '.$inventory_tag.''
            );
        }
        return response()->json($json);
    }
}
