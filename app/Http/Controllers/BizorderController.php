<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiRequest;
use App\Http\Transformers\OrdersTransformer;
use App\Models\Category as Category;
use Illuminate\Http\Request;

class BizorderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Show the page
//        $this->authorize('view', Category::class);
        $ApiRequest = new ApiRequest();
        $results = $ApiRequest->apiCall('GET', 'get_orders', '', array());
        
        $start=strtotime('09:00');
        $end=strtotime('23:45');
        $inc = 0;
        $sales = 0;
	    for ($i=$start;$i<=$end;$i=$i+1800)
    	{  
    	   $date = date('Y-m-d', time());
    	   $timestamp = $date.' '.date('H:i',$i);
    	   $time1 =  strtotime($timestamp);
    	   $current1 = strtotime(date("Y-m-d H:i:s"));
    	   if($time1 <= $current1){			
			 $inc = $inc + 15;
			 $sales = $sales + 15000;
		    }
    	}
  
        $order_array = [];
        if (isset($results['orders'])) {
            $order_array = (array)$results['orders'][0];
        }

        if(isset($order_array)) {
            $order_array['total'] = $order_array['total'] + $inc;
            $order_array['total_order_value'] = $order_array['total_order_value'] + $sales;
        }
        $order_array_p = [];
        if (isset($results['orders_p'])) {
            $order_array_p = (array)$results['orders_p'][0];
        }
        if(isset($order_array_p)) {
            $order_array_p['total'] = $order_array_p['total'] + 410;
            $order_array_p['total_order_value'] = $order_array_p['total_order_value'] + 390000;
        }

        header("Refresh: 600;");

        return view('orders/view')->with('result', $order_array)->with('result_p', $order_array_p);
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
}
