<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiRequest;
use App\Http\Transformers\OrdersTransformer;
use App\Models\Category as Category;
use Illuminate\Http\Request;

class OrdersController extends Controller
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

        $order_array = [];
        if (isset($results['orders'])) {
            $order_array = (array)$results['orders'][0];
        }

        $order_array_p = [];
        if (isset($results['orders_p'])) {
            $order_array_p = (array)$results['orders_p'][0];
        }

        header("Refresh: 600;");

        return view('orders/index')->with('result', $order_array)->with('result_p', $order_array_p);
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
