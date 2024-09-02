@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('admin/hardware/general.checkin') }}
  @parent
@stop

{{-- Page content --}}
@section('content')
  <style>
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
  <form class="form-horizontal" method="post" action="" autocomplete="off" id="hub-checkin-form">
                  {{csrf_field()}}
  <div class="row">
    <!-- left column -->
    <div class="col-md-12">
      <div class="box box-default">
        <div class="box-header with-border">
          <h2 class="box-title">{{ trans('admin/hardware/form.hubcheckin') }}</h2>
        </div><!-- /.box-header -->

        <div class="box-body">
          <div class="row">
    <div class="col-md-11">
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
            <span id="invalid-barcode"></span>
        </div>
    </div>
</div>
</div>
<div class="box-body">
<div class="row">
          <div class="col-md-12">

                  <div class="box box-default">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                          <th style="width: 8%;">Shipment No</th>
                          <th style="width: 9%;">Category</th>
                          <th style="width: 18%;">Product Name</th>
                          <th style="width: 6%;">Vendor</th>
                          <th style="width: 6%;">Order No</th>
                          <th style="width: 5%;">Quantity</th>
                          <th style="width: 14%;">Customer Name</th>
                          <th style="width: 9%;">Phone No.</th>
                          <th style="width: 17%;">Address</th>
                          <th>#</th>
                        </tr>
                      </thead>
                      <tbody id="shipment_data">
                      </tbody>
                    </table>
                  </div>
                </div>
          </div> <!--/.col-md-12-->
        </div>
        </div> <!--/.box-body-->
        <div class="box-footer">
          <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
          <input type="hidden" name="status_id" class="form-control" value="5">
          <div class="pull-right">
            <button type="submit" class="btn btn-primary pull-right btn-hub-checkin" disabled><i class="fa fa-check icon-white" aria-hidden="true"></i> Submit</button>
            <div class="col-md-1" id="loading-gif-submit" style="display: none;">
              <img src="{{URL::asset('/uploads/assets/loading-waiting.gif')}}" style="height:50px;">
            </div>
          </div>
        </div>
      </form>
      </div> <!--/.box.box-default-->
    </div>
  </div>
@stop
@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
  $(document).ready(function() {
    var x = 0;
    const shipment_number_search = [];
        //get asset data from storefront by shipment number
        //$('#shipment_id_search').off('keyup').on('keyup', function(event) {
        $(".search").click(function( event ) {
            //e.preventDefault();
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
              }, 3000);
              return false;
            }
            var csrf_token = "{{ csrf_token() }}";
            $.ajax({
                type:'POST',
                url: "{{url('/') }}/hardware/shipmentnumberhubcheckin",
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
                      var category_name = response.category_name;
                      var product_name = response.product_name;
                      var vendor_name = response.vendor_name;
                      var order_number = response.order_number;
                      var shipment_number = response.shipment_number;
                      var quantity = response.quantity;
                      var customer_firstname = response.customer_firstname;
                      var customer_lastname = response.customer_lastname;
                      var customer_fullname = customer_firstname+' '+customer_lastname;
                      var customer_phoneno = response.customer_phoneno;
                      var customer_address = response.customer_address;
                      var newrow = '<tr>';
                      newrow += '<td>';
                      newrow += '<input type="hidden" name="asset_id[]" class="form-control asset_id'+x+'" shipment_sn="'+x+'" value="'+asset_id+'">';
                      newrow += '<input type="hidden" name="shipment_number[]" class="form-control shipment_number'+x+'" shipment_sn="'+x+'" value="'+shipment_number+'">';
                      newrow += barcode_number;
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += category_name;
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += product_name;
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += vendor_name;
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += order_number;
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += quantity;
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += customer_fullname;
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += customer_phoneno;
                      newrow += '</td>';
                      newrow += '<td>';
                      newrow += customer_address;
                      newrow += '</td>';
                      newrow += '<td><a href="javascript:void" class="remove_field btn btn-default btn-sm" tag_id="'+x+'"><i class="fa fa-minus"></i></a></td>';
                      newrow += '</tr>';
                      $("#shipment_data").append(newrow);
                      $("#loading-gif").hide();
                      $(".remove_field").click(function( event ) {
                        x--;
                        $(this).parents("tr").remove();
                        if(x == '0'){
                          $(".btn-hub-checkin").attr('disabled',true);
                          shipment_number_search .length = 0;
                        }
                      });
                      $("#shipment_id_search").val('');
                      $(".btn-hub-checkin").attr('disabled',false);
                      shipment_number_search.push(barcode_number);
                  }
                  else{
                    $("#shipment_id_search").val('');
                    $("#invalid-barcode").html(response.message);
                    $("#loading-gif").hide();
                    setTimeout(function() { 
                        $("#invalid-barcode").html('');
                    }, 5000);
                    $(".btn-hub-checkin").attr('disabled',true);
                  }
                }
            });
            return false;
        });
        
        //Hub Checkin form submission
        $("#hub-checkin-form").submit(function( event ) {
            $(".btn-hub-checkin").attr('disabled',true);
            $("#loading-gif-submit").show();
        });
  });
</script>
@include('partials/assets-assigned')

@stop