@extends('layouts/default')

{{-- Page title --}}
@section('title')
     Bulk Package Checkout
@parent
@stop

{{-- Page content --}}
@section('content')

<style type="text/css">
    blink {
  animation: 1s linear infinite condemned_blink_effect;
  color: #ff0000;
}

#invalid-barcode {
  color: #ff0000;
  font-weight: bold;
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
  <div class="col-md-11">
    <form class="form-horizontal" method="post" action="" autocomplete="off" id="bulk-checkout-form">
          {{ csrf_field() }}
    <div class="box box-default">
      <div class="box-body">
        <div class="row">
    <div class="col-md-12">
        <label for="shipment_id_search" class="col-md-3">{{ trans('admin/hardware/form.shipment_orderno') }}</label>
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
            <span id="invalid-barcode"></span>
        </div>
    </div>
</div>
</div>
<div class="box-body">
  <div class="box box-default">
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 8%;">Shipment No</th>
            <th style="width: 9%;">Category</th>
            <th style="width: 19%;">Product Name</th>
            <th style="width: 6%;">Vendor</th>
            <th style="width: 6%;">Order No</th>
            <th style="width: 5%;">Quantity</th>
            <th style="width: 14%;">Customer Name</th>
            <th style="width: 9%;">Phone No.</th>
            <th style="width: 17%;">Address</th>
          </tr>
        </thead>
        <tbody id=shipment_data>
        </tbody>
      </table>
      <div class="form-group col-md-3">
        <label for="is_third_party_transfer">Third party Transfer</label>
        <select class="is_third_party_transfer form-control" name="is_third_party_transfer" id="is_third_party_transfer" required>
          <option value="">--Select--</option>
          <option value="Y">Yes</option>
          <option value="N">No</option>
        </select>
      </div>
      <div class="form-group col-md-3" id="carrier_form" style="display: none;margin-left: 5px;">
        <label for="carrier_id">Carrier</label>
        <select class="carrier_id form-control" name="carrier_id" id="carrier_id">
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
        <input type="hidden" name="status_id" value="7" id="status_id">
        <button type="submit" class="btn btn-primary pull-right btn-bulk-checkout" disabled><i class="fa fa-check icon-white" aria-hidden="true"></i> {{ trans('general.checkout') }}</button>
        <div class="col-md-1 pull-right" id="loading-gif-submit" style="display: none;">
            <img src="{{URL::asset('/uploads/assets/loading-waiting.gif')}}" style="height:50px;">
        </div>
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
    const shipment_number_search = [];
    var x = 0;
    $(".search").click(function( event ) {
            var barcode_number = $("#shipment_id_search").val();
            if(barcode_number == ''){
              return false;
            }
            $("#loading-gif").show();
            $("#shipment_id_search").val('');
            if(shipment_number_search.includes(barcode_number)){
              $("#invalid-barcode").html('Barcode Number '+barcode_number+' already scanned!');
              $("#loading-gif").hide();
              $("#shipment_id_search").val('');
              setTimeout(function() { 
                  $("#invalid-barcode").html('');
              }, 5000);
              return false;
            }
            var csrf_token = "{{ csrf_token() }}";
            $.ajax({
                type:'POST',
                url: "{{url('/') }}/hardware/shipmentnumbercheck",
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
                      var barcode_number = response.barcode_number;
                      var shipment_number = response.shipment_number;
                      var category_name = response.category_name;
                      var product_name = response.product_name;
                      var vendor_name = response.vendor_name;
                      var order_number = response.order_number;
                      var quantity = response.quantity;
                      var customer_firstname = response.customer_firstname;
                      var customer_lastname = response.customer_lastname;
                      var customer_fullname = customer_firstname+' '+customer_lastname;
                      var customer_phoneno = response.customer_phoneno;
                      var customer_address = response.customer_address;
                      var newrow = '<tr>';
                      newrow += '<td>';
                      newrow += '<input type="hidden" name="asset_id[]" class="form-control asset_id'+x+'" shipment_sn="'+x+'" value="'+asset_id+'">';
                      newrow += '<input type="hidden" name="shipment_number['+x+']" class="form-control shipment_number'+x+'" shipment_sn="'+x+'" value="'+shipment_number+'">';
                      newrow += '<input type="text" name="barcode_number['+x+']" class="form-control barcode_number'+x+'" shipment_sn="'+x+'" value="'+barcode_number+'" readonly>';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '<input type="text" name="category_name['+x+']" class="form-control category_name'+x+'" shipment_sn="'+x+'" value="'+category_name+'" readonly>';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '<input type="text" name="product_name[]" class="form-control product_name'+x+'" shipment_sn="'+x+'" value="'+product_name+'" readonly>';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '<input type="text" name="vendor_name['+x+']" class="form-control vendor_name'+x+'" shipment_sn="'+x+'" value="'+vendor_name+'" readonly>';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '<input type="text" name="order_number['+x+']" class="form-control order_number'+x+'" shipment_sn="'+x+'" value="'+order_number+'" readonly>';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '<input type="text" name="quantity['+x+']" class="form-control quantity'+x+'" shipment_sn="'+x+'" value="'+quantity+'" readonly>';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '<input type="hidden" name="customer_firstname['+x+']" class="form-control customer_firstname'+x+'" shipment_sn="'+x+'" value="'+customer_firstname+'" readonly>';
                      newrow += '<input type="hidden" name="customer_lastname['+x+']" class="form-control customer_lastname'+x+'" shipment_sn="'+x+'" value="'+customer_lastname+'" readonly>';
                      newrow += '<input type="text" name="customer_fullname['+x+']" class="form-control customer_fullname'+x+'" shipment_sn="'+x+'" value="'+customer_fullname+'" readonly>';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '<input type="text" name="customer_phoneno['+x+']" class="form-control customer_phoneno'+x+'" shipment_sn="'+x+'" value="'+customer_phoneno+'" readonly>';
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += '<input type="text" name="customer_address['+x+']" class="form-control customer_address'+x+'" shipment_sn="'+x+'" value="'+customer_address+'" readonly>';
                      newrow += '</td>';
                      newrow += '</tr>';
                      $("#shipment_data").append(newrow);
                      $("#loading-gif").hide();
                      $("#shipment_id_search").val('');
                      $(".btn-bulk-checkout").attr('disabled',false);
                      shipment_number_search.push(barcode_number);
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
    $(".is_third_party_transfer").change(function( event ) {
      var is_third_party_transfer = $(this).val();
      if(is_third_party_transfer == 'Y'){
        $("#carrier_form").show();
        $("#status_id").val('9');
        $("#carrier_id").attr('required',true);
      }
      else{
        $("#carrier_form").hide();
        $("#status_id").val('7');
        $("#carrier_id").attr('required',false);
      }
    });
    //Bulk Checkout form submission
      $("#bulk-checkout-form").submit(function( event ) {
          $(".btn-bulk-checkout").attr('disabled',true);
          $("#loading-gif-submit").show();
      });
  });
</script>
@include('partials/assets-assigned')

@stop
