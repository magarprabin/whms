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

#invalid-shipment-tag {
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
  <div class="col-md-12">
    <form class="form-horizontal" method="post" action="" autocomplete="off" id="bulk-tag-checkout-form">
          {{ csrf_field() }}
    <div class="box box-default">
      <div class="box-body">
        <div class="row">
    <div class="col-md-12">
        <label for="asset_tag_search" class="col-md-2">Order Shipment Tag</label>
        <div class="col-md-3">
            <input type="text" name="asset_tag_search[]" class="form-control asset_tag_search" id="asset_tag_search">
            <input type="hidden" name="last_index" value="0" id="last_index">
        </div>
        <div class="col-md-2">
            <button type="submit" class="search btn btn-success">Show</button>
        </div>
        <div class="col-md-1" id="loading-gif" style="display: none;">
            <img src="{{URL::asset('/uploads/assets/loading-waiting.gif')}}" style="height:50px;">
        </div>
        <div class="col-md-3"><span id="invalid-shipment-tag"></span></div>
    </div>
</div>
</div>
<div class="box-body">
  <div class="box box-default">
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width: 8%;">Asset Tag</th>
            <th style="width: 8%;">Shipment No</th>
            <th style="width: 9%;">Category</th>
            <th style="width: 18%;">Product Name</th>
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
      <div class="form-group col-md-3" id="carrier_form" style="margin-left: 5px;">
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
        <input type="hidden" name="status_id" id="status_id" class="status_id form-control" value="9">
        <div class="pull-right">
          <button type="submit" class="btn btn-primary pull-right btn-bulk-checkout" disabled><i class="fa fa-check icon-white" aria-hidden="true"></i> {{ trans('general.checkout') }}</button>
          <div class="col-md-1" id="loading-gif-submit" style="display: none;">
            <img src="{{URL::asset('/uploads/assets/loading-waiting.gif')}}" style="height:50px;">
        </div>
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
    const asset_tag_search = [];
    var x = 0;
    $(".search").click(function( event ) {
            var asset_tag = $("#asset_tag_search").val();
            if(asset_tag == ''){
              return false;
            }
            $("#loading-gif").show();
            $("#asset_tag_search").val('');
            if(asset_tag_search.includes(asset_tag)){
              $("#invalid-shipment-tag").html('Barcode Number '+asset_tag+' already scanned!');
              $("#loading-gif").hide();
              setTimeout(function() { 
                  $("#invalid-shipment-tag").html('');
              }, 3000);
              return false;
            }
            var csrf_token = "{{ csrf_token() }}";
            $.ajax({
                type:'POST',
                url: "{{url('/') }}/hardware/assettagcheck",
                data:{asset_tag:asset_tag},
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                _token: "{{ csrf_token() }}",
                success:function(response){
                    if(response.status == 'success'){
                      x++;
                      var asset_tag = response.asset_tag;
                      var asset_id = response.asset_id;
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
                      var newrow = response.tr_html;
                      $("#shipment_data").append(newrow);
                      $("#loading-gif").hide();
                      $("#asset_tag_search").val('');
                      $(".btn-bulk-checkout").attr('disabled',false);
                      asset_tag_search.push(asset_tag);
                  }
                  else{
                    if(x == '0'){
                      $(".btn-bulk-checkout").attr('disabled',true);
                    }
                    $("#invalid-shipment-tag").html(response.message);
                    $("#loading-gif").hide();
                    $("#asset_tag_search").val('');
                    setTimeout(function() { 
                        $("#invalid-shipment-tag").html('');
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

    //Bulk Tag Checkout form submission
    $("#bulk-tag-checkout-form").submit(function( event ) {
        $(".btn-bulk-checkout").attr('disabled',true);
        $("#loading-gif-submit").show();
    });
  });
</script>
@include('partials/assets-assigned')

@stop
