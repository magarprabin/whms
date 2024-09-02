@extends('layouts/default')

@section('title')
  Create POD page
@stop

@if ((Request::get('company_id')) && ($company))
  {{ $company->name }}
@endif

@push('css')
<link rel="stylesheet" href="{{ url('css/dist/bb_pod.css')}}">
@endpush

@section('content')
{{ Form::open([
  'method' => 'POST',
  'route' => ['generate_pod'],
  'class' => 'form',
  'id' => 'podForm']) }}
    <div class="box-1-flex">
      <div class="box-1">
        <label for="riders">Rider:</label>
        <select id="riders" name="main_rider">
          <option value="" disabled selected>Select a rider</option>
          @foreach($riders as $rider)
            <option value="{{ $rider->id }}">{{ $rider->user->username }}</option>
          @endforeach
        </select>
        <label for="vehicles">Vehicle:</label>
        <select id="vehicles" name="main_vehicle">
          <option value="" disabled selected>Select a Vehicle</option>
          @foreach($vehicles as $vehicle)
            <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="box-1">
        <button type="submit" class="btn btn-info">Generate POD</button>
      </div>
    </div>
      
    @foreach ($groupassets as $suppliers=>$assets)
    <div class="border-vendor">
      @php
        $supplier = \App\Models\Supplier::find($suppliers);
        $product_code = 'StaticCode';
        $item_id = 'StaticId';
        $unit_price = '100';
        $subtotal = '100';
        $dicount = '100';
        $weight = '100';
        $index = $loop->index;
      @endphp
      <div class="vr">
      <label for="Vendor">Vendor Name</label><span id="Vendor">:  {{$supplier->name}}</span><br>
      <label for="Phone">Phone No</label><span id="Phone">:  {{$supplier->phone}}</span><br>
      <label for="Address">Address</label><span id="Address">:  {{$supplier->address}}</span><br>
      </div>
      <table>
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
        @foreach ($assets as $asset)
          <tr>
            <td>{{$asset->order_number}}</td>
            <td>{{$item_id}}</td>
            <td id="td-200">
              <div class="three-line-text">{{$asset->name}}</div></td>
            <td>{{$product_code}}</td>
            <td>{{$asset->quantity}}</td>
            <td>{{$unit_price}}</td>
            <td>{{$subtotal}}</td>
            <td>{{$dicount}}</td>
            <td>{{$weight}}</td>
          </tr>
          <input type="hidden" name="asset_ids[{{$index}}][]" value="{{$asset->id}}"/>
        @endforeach
      </table>

      <div class="pickup-details">
        <h3>Vendor Pickup Details</h3>
        <label for="address-{{$supplier->id}}">Address</label>
        <input type="text" id="address-{{$supplier->id}}" name="address[]"><br>
        <label for="contact-{{$supplier->id}}">Contact Person</label>
        <input type="text" id="contact-{{$supplier->id}}" name="contact[]"><br>
        <label for="phone-{{$supplier->id}}">Phone No.</label>
        <input type="tel" id="phone-{{$supplier->id}}" name="phone[]"><br>
      </div>
        <input type="hidden" name="supplier_id[]" value="{{$supplier->id}}"/>
        <input type="hidden" name="no_of_supplier" value="{{$loop->count}}"/>
        <label for="rider-{{$supplier->id}}">Rider:</label>
        <select id="rider-{{$supplier->id}}" class="riders" name="rider[]">
          <option value="" disabled selected>Select a Rider</option>
          @foreach($riders as $rider)
            <option value="{{ $rider->id }}">{{ $rider->user->username }}</option>
          @endforeach
        </select>

        <label for="vehicle-{{$supplier->id}}">Vehicle:</label>
        <select id="vehicle-{{$supplier->id}}" class="vehicles" name="vehicle[]">
          <option value="" disabled selected>Select a Vehicle</option>
          @foreach($vehicles as $vehicle)
            <option value="{{ $vehicle->id }}">{{ $vehicle->name }}</option>
          @endforeach
        </select>
      </div>
    @endforeach
    
    {{ Form::close() }}
@stop