
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
      <div class="poid">
        <div class="barcode"><img src="{{$barcode}}" /></div>
        <label for="podid">{{ $pod->pod_id }}</label>
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
          <th>Order ID</th>
          <th>Item ID</th>
          <th>Product Name</th>
          <th>Product Code</th>
          <th>Qty</th>
          <th>Unit Price</th>
          <th>Sub Total</th>
          <th>Discount</th>
          <th>Weight</th>
        </tr>
          @php
            $totalOrder = 0;
            $totalQty = 0;
            $totalWt = 0;
          @endphp
        @foreach($pod_assets as $pod_asset)
          <tr>
            <td>{{$pod_asset->asset->order_number}}</td>
            <td>{{$item_id}}</td>
            <td id="td-200">
              <div class="three-line-text">{{$pod_asset->asset->name}}</div>
            </td>
            <td>{{$product_code}}</td>
            <td>{{$pod_asset->asset->quantity}}</td>
            <td>{{$unit_price}}</td>
            <td>{{$subtotal}}</td>
            <td>{{$dicount}}</td>
            <td>{{$weight}}</td>
          </tr>  
            @if(!empty($pod_asset->asset->order_number))
              @php
                $totalOrder++;
              @endphp
            @endif
            @if(!empty($pod_asset->asset->quantity))
              @php
                $totalQty=$totalQty+$pod_asset->asset->quantity;
              @endphp
            @endif
            @if(!empty($pod_asset->asset->weight))
              @php
                $totalWt=$totalWt+$pod_asset->asset->weight;
              @endphp
            @endif
        @endforeach
      </table>
      <div class="total">
        <div class="total1">Total no. of order: {{$totalOrder}}</div>
        <div class="total2">Total Qty: {{$totalQty}}</div>
        <div class="total3">Total Weight: {{$totalWt}}</div>
      </div>
    </div>
    <h3>Vendor Pickup Details</h3>
    <div class="form-group">
        <label for="address" class="col-md-3 control-label">Address</label>
        <div class="col-md-7 col-sm-12">
            <input class="form-control" type="text" id="address" name="address" value="{{ old('address', $pod->address) }}">
        </div>
    </div>
    <div class="form-group">
        <label for="contact" class="col-md-3 control-label">Contact Person</label>
        <div class="col-md-7 col-sm-12">
            <input class="form-control" type="text" id="contact" name="contact_person" value="{{ old('contact_person', $pod->contact_person) }}">
        </div>
    </div>
    <div class="form-group">
        <label for="phone" class="col-md-3 control-label">Phone No.</label>
        <div class="col-md-7 col-sm-12">
            <input class="form-control" type="tel" id="phone" name="phone_no" value="{{ old('phone_no', $pod->phone_no) }}">
        </div>
    </div>
    <div class="form-group">
        <label for="rider" class="col-md-3 control-label">Rider:</label>
        <div class="col-md-7 col-sm-12">
            <select id="rider" class="form-control" name="rider">
                @foreach($riders as $rider)
                <option value="{{ $rider->id }}" @if($rider->id == $pod->rider_id) selected @endif>{{ $rider->user->username }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="vehicle" class="col-md-3 control-label">Vehicle:</label>
        <div class="col-md-7 col-sm-12">
            <select id="vehicle" class="form-control" name="vehicle">
                @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}" @if($vehicle->id == $pod->vehicle_id) selected @endif>{{ $vehicle->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
   
@stop



