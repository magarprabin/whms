
@push('css')
<link rel="stylesheet" href="{{ url('css/dist/bb_pod.css')}}">
@endpush

@extends('layouts/edit-form', [
    'createText' => trans('admin/pod/general.create') ,
    'updateText' => trans('admin/pod/general.update'),
    'topSubmit'  => 'true',
    'formAction' => (isset($pod->id)) ? route('pod.update', ['pod_id' => $pod->id]) : route('pod.store'),
])

@section('inputFields')
<div class="topflex">
      <div class="box-2">
        <div class="b4">
          <label for="supplier">{{ $pod->supplier->name }}</label>
        </div>
        <div class="b3">
          <label>{{ $pod->supplier->phone }}</label>
        </div>
      </div>
    </div>

    @php
      $product_code = 'StaticCode';
      $item_id = 'StaticId';
      $unit_price = '100';
      $subtotal = '100';
      $dicount = '100';
      $weight = '100';
    @endphp

    <div class="vendor-wrap">      
      <table class="tableclass">
        <tr>
          <th rowspan="2" id="width3">Order ID</th>
          <th rowspan="2" id="width3">Item ID</th>
          <th rowspan="2">Product Name</th>
          <th rowspan="2" id="width3">Product Code</th>
          <th rowspan="2" id="width3">Qty</th>
          <th rowspan="2" id="width3">Unit Price</th>
          <th rowspan="2" id="width3">Sub Total</th>
          <th rowspan="2" id="width3">Discount</th>
          <th rowspan="2" id="width3">Weight</th>
          <th rowspan="1" colspan="3"><center>Handed Over</center></th>
        </tr>
          <tr>
            <th>Yes</th>
            <th>No</th>
            <th>Cancel</th>
          </tr>
        </tr>
          @php
            $totalOrder = 0;
            $totalQty = 0;
            $totalWt = 0;
          @endphp
        @foreach($pod_assets as $pod_asset)
          <tr>
            <td id="width3">{{$pod_asset->asset->order_number}}</td>
            <td id="width3">{{$item_id}}</td>
            <td id="td-200">
              <div class="three-line-text">{{$pod_asset->asset->name}}</div>
            </td>
            <td id="width3">{{$product_code}}</td>
            <td id="width3">{{$pod_asset->asset->quantity}}</td>
            <td id="width3">{{$unit_price}}</td>
            <td id="width3">{{$subtotal}}</td>
            <td id="width3">{{$dicount}}</td>
            <td id="width3">{{$weight}}</td>
            <td id="width3"><center><input type="radio" class="myRadio" name="myRadio[{{$loop->index}}]"></center></td>
            <td id="width3"><center><input type="radio" class="myRadio" name="myRadio[{{$loop->index}}]"></center></td>
            <td id="width3"><center><input type="radio" class="myRadio" name="myRadio[{{$loop->index}}]"></center></td>
          </tr>  
            
        @endforeach
      </table>
    </div>
@stop