@extends('layouts/default')

{{-- Page title --}}
@section('title')
     Bulk Third Party Checkout
@parent
@stop

{{-- Page content --}}
@section('content')

<style type="text/css">
    blink {
  animation: 1s linear infinite condemned_blink_effect;
  color: #ff0000;
}

@keyframes condemned_blink_effect {
  0% {
    visibility: hidden;
  }
  50% {
    visibility: hidden;
  }
  100% {
    visibility: visible;
  }
}
  .input-group {
    padding-left: 0px !important;
  }
</style>
<!-- Package Checkout -->
<div class="row">
  <div class="col-md-8">
    <form class="form-horizontal" method="post" action="" autocomplete="off">
          {{ csrf_field() }}
    <div class="box box-default">
      <div class="box-body">
        <div class="row">
    <div class="col-md-12">
        <label for="shipment_id_search" class="col-md-3">Order Shipment Barcode Number</label>
        <div class="col-md-3">
            <input type="text" name="shipment_id_search[]" class="form-control shipment_id_search" id="shipment_id_search">
            <input type="hidden" name="last_index" value="0" id="last_index">
        </div>
        <div class="col-md-2">
            <button type="submit" class="search btn btn-success">Show</button>
        </div>
        <div class="col-md-1" id="loading-gif" style="display: none;">
            <img src="{{URL::asset('/uploads/assets/loading-waiting.gif')}}" style="height:50px;">

        </div>
        <div class="col-md-3">
            <blink><span id="invalid-barcode"></span></blink>
        </div>
    </div>
</div>
</div>
<div class="box-body">
          <!-- Checkout selector -->
          <div class="box box-default">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                          <th>Shipment No</th>
                          <th>Category</th>
                          <th>Product Name</th>
                          <th>Vendor</th>
                          <th>Order No</th>
                          <th>Quantity</th>
                        </tr>
                      </thead>
                      <tbody id=shipment_data>
                      </tbody>
                    </table>
                    <div class="form-group col-md-3">
                      <label for="carrier_id">Carrier</label>
                      <select class="carrier_id form-control" name="carrier_id" id="carrier_id" required>
                        <option value="">--Select--</option>
                        @foreach($carrier_lists as $carrier_list)
                            <option value="{{ $carrier_list->id }}">{{ $carrier_list->carrier_name }}</option>
                          @endforeach
                      </select>
                    </div>
                  </div>
                </div>
      </div> <!--./box-body-->
      <div class="box-footer">
        <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
        <input type="hidden" name="checkout_to_type" value="location">
        <input type="hidden" name="assigned_location" value="{{ $logged_in_user_location_id }}">
        <input type="hidden" name="status_id" value="9">
        <button type="submit" class="btn btn-primary pull-right btn-bulk-checkout" disabled><i class="fa fa-check icon-white" aria-hidden="true"></i> {{ trans('general.checkout') }}</button>
      </div>
    </div>
      </form>
  </div> <!--/.col-md-7-->

  <!-- right column -->
  <div class="col-md-5" id="current_assets_box" style="display:none;">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h2 class="box-title">{{ trans('admin/users/general.current_assets') }}</h2>
      </div>
      <div class="box-body">
        <div id="current_assets_content">
        </div>
      </div>
    </div>
  </div>
</div>
@stop

@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
  $(document).ready(function() {
    var x = 0;
    $(".search").click(function( event ) {
            //e.preventDefault();
            $("#loading-gif").show();
            var barcode_number = $("#shipment_id_search").val();
            var csrf_token = "{{ csrf_token() }}";
            $.ajax({
                type:'POST',
                url: "{{url('/') }}/hardware/shipmentnumbercheckthirdparty",
                data:{barcode_number:barcode_number},
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                _token: "{{ csrf_token() }}",
                success:function(response){
                    if(response.status == 'success'){
                      x++;
                      var asset_id = response.asset_id;
                      var shipment_number = response.shipment_number;
                      var category_name = response.category_name;
                      var product_name = response.product_name;
                      var vendor_name = response.vendor_name;
                      var order_number = response.order_number;
                      var quantity = response.quantity;
                      var newrow = '<tr>';
                      newrow += '<td>';
                      newrow += '<input type="hidden" name="asset_id[]" class="form-control asset_id'+x+'" shipment_sn="'+x+'" value="'+asset_id+'">';
                      newrow += '<input type="hidden" name="shipment_number[]" class="form-control shipment_number'+x+'" shipment_sn="'+x+'" value="'+shipment_number+'">';
                      newrow += '<input type="text" name="barcode_number[]" class="form-control barcode_number'+x+'" shipment_sn="'+x+'" value="'+barcode_number+'">';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '<input type="text" name="category_name[]" class="form-control category_name'+x+'" shipment_sn="'+x+'" value="'+category_name+'">';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '<input type="text" name="product_name[]" class="form-control product_name'+x+'" shipment_sn="'+x+'" value="'+product_name+'">';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '<input type="text" name="vendor_name[]" class="form-control vendor_name'+x+'" shipment_sn="'+x+'" value="'+vendor_name+'">';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '<input type="text" name="order_number[]" class="form-control order_number'+x+'" shipment_sn="'+x+'" value="'+order_number+'">';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '<input type="text" name="quantity[]" class="form-control quantity'+x+'" shipment_sn="'+x+'" value="'+quantity+'">';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '</td>';
                      newrow += '</tr>';
                      $("#shipment_data").append(newrow);
                      $("#loading-gif").hide();
                      $("#shipment_id_search").val('');
                      $(".btn-bulk-checkout").attr('disabled',false);
                  }
                  else{
                    if(x == '0'){
                      $(".btn-bulk-checkout").attr('disabled',true);
                    }
                    $("#invalid-barcode").html(response.message);
                    $("#loading-gif").hide();
                    $("#shipment_id_search").val('');
                    setTimeout(function() { 
                        $("#invalid-barcode").html('');
                    }, 3000);
                  }
                }
            });
            return false;
        });
  });
</script>
@include('partials/assets-assigned')

@stop
