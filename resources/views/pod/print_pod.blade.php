@extends('layouts/default')
@section('title')
  Print POD page
@stop
@if ((Request::get('company_id')) && ($company))
  {{ $company->name }}
@endif
@push('css')
<link rel="stylesheet" href="{{ url('css/dist/bb_pod.css')}}">
@endpush
@section('content')
<!-- Print button -->
<div class="buttonflex">
  <button class="print-button" onclick="window.print()">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer printpod" viewBox="0 0 16 16">
      <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
      <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
    </svg>
    Print POD
  </button>
</div>
<div class="main-content">
  @foreach($data as $item)
    <div class="topflex">
      <div class="box-2">
        <div class="b4">
          <label for="supplier">{{ $item['pod']->supplier->name }}</label>
        </div>
        <div class="b3">
          <label>{{ $item['pod']->supplier->phone }}</label>
        </div>
      </div>  
      <div class="barcode"><img src="{{$item['barcode']}}" />
        <div class="poid">
          <label for="podid">{{ $item['pod']->pod_id }}</label>
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
    <div class="table-flex">
      <table style="width: 48%">
        <tr>
          <th colspan="2">Vendor Pick Up Details</th>
        </tr>
        <th>Address:</th>
          <td>{{ $item['pod']->address}}</td>
        </tr>
        <tr>
          <th>Phone/Email:</th>
          <td>{{ $item['pod']->phone_no}}</td>
        </tr>
        <tr>
          <th>Contact Person:</th>
          <td>{{ $item['pod']->contact_person}}</td>
        </tr>        
      </table>
      <table style="width: 48%">
        <tr>
          <th colspan="2">Vendor Warehouse Details</th>
        <tr>
        <tr>
          <th>Address:</th>
          <td>{{ $item['pod']->supplier->state}}, {{ $item['pod']->supplier->city}}, {{ $item['pod']->supplier->address}}</td>
        </tr>
        <tr>
          <th>Phone / Email:</th>
          <td>{{ $item['pod']->supplier->phone }} / {{ $item['pod']->supplier->email }}</td>
        </tr>
        <tr>
          <th>Contact:</th>
          <td>{{ $item['pod']->supplier->name}}</td>
        </tr>
      </table>
    </div>
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
          @php
            $totalOrder = 0;
            $totalQty = 0;
            $totalWt = 0;
          @endphp
        @foreach($item['pod_assets'] as $pod_asset)
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
            <td id="width3"></td>
            <td id="width3"></td>
            <td id="width3"></td>
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
    <div class="confirmation">
      <div class="cflex-items">
        <label>Received By</label>
        <p>Signature:</p>
        <p>Name: {{ $item['pod']->rider->user->first_name }} {{ $item['pod']->rider->user->last_name }}</p>
        <p>Date: {{ now()->format('Y-m-d') }}</p>
      </div>
      <div class="cflex-items">
        <label>Handed Over By</label>
        <p>Signature:</p>
        <p>Name:</p>
        <p>Date:</p>
      </div>
      <div class="cflex-items">
        <label>At Warehouse, Received By</label>
        <p>Signature:</p>
        <p>Name:</p>
        <p>Date:</p>
      </div>
    </div>
    <div class="hr">
    </div>
    @if (!$loop->last)
    <div class="page-divider"></div>
    <br><br>
    @else
    <div class="no-page-break"></div>
    @endif
  @endforeach
</div>  
@stop