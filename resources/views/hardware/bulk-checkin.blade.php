<style type="text/css">
    blink {
  animation: 1s linear infinite condemned_blink_effect;
  color: #ff0000;
}

.shipment-details-col{
    margin-left:30px;
    margin-right:20px;
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
</style>
@extends('layouts/edit-form-bulk-checkin', [
    'createText' => trans('admin/hardware/form.create'),
    'updateText' => trans('admin/hardware/form.update'),
    'topSubmit' => true,
    'helpText' => trans('help.assets'),
    'helpPosition' => 'right',
    'formAction' => ($item->id) ? route('hardware.update', ['hardware' => $item->id]) : route('hardware/bulkcheckin'),
])


{{-- Page content --}}

@section('inputFields')
<div id="entry-form">
    <div class="row">
        <div class="col-md-12">
            <label for="shipment_id_search" class="col-md-3">{{ trans('admin/hardware/form.shipment_orderno') }}</label>
            <div class="col-md-3">
                <input type="text" name="shipment_id_search[]" class="form-control shipment_id_search" id="shipment_id_search">
                <input type="hidden" name="last_index" value="0" id="last_index">
                <input type="hidden" name="location_id" value="1">
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
    <div class="box box-default" style="margin-top: 5px;width: 100%;">
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
                <tbody id="checkin-form">
                </tbody>
            </table>
        </div>
        <input type="hidden" name="supplier_id" value="1">
        <input type="hidden" name="model_id" value="3">
        <input type="hidden" name="status_id" value="5">
    </div>
</div>
@stop

@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
    var transformed_oldvals={};
    const shipment_number_search = [];
    function fetchCustomFields() {
        //save custom field choices
        var oldvals = $('#custom_fields_content').find('input,select').serializeArray();
        for(var i in oldvals) {
            transformed_oldvals[oldvals[i].name]=oldvals[i].value;
        }

        var modelid = $('#model_select_id').val();
        if (modelid == '') {
            $('#custom_fields_content').html("");
        } else {

            $.ajax({
                type: 'GET',
                url: "{{url('/') }}/models/" + modelid + "/custom_fields",
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                _token: "{{ csrf_token() }}",
                dataType: 'html',
                success: function (data) {
                    $('#custom_fields_content').html(data);
                    $('#custom_fields_content select').select2(); //enable select2 on any custom fields that are select-boxes
                    //now re-populate the custom fields based on the previously saved values
                    $('#custom_fields_content').find('input,select').each(function (index,elem) {
                        if(transformed_oldvals[elem.name]) {
                            $(elem).val(transformed_oldvals[elem.name]).trigger('change'); //the trigger is for select2-based objects, if we have any
                        }

                    });
                }
            });
        }
    }

    function user_add(status_id) {

        if (status_id != '') {
            $(".status_spinner").css("display", "inline");
            $.ajax({
                url: "{{url('/') }}/api/v1/statuslabels/" + status_id + "/deployable",
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data) {
                    $(".status_spinner").css("display", "none");
                    $("#selected_status_status").fadeIn();
                    var status = $('#status_select_id').val();
                    if (data == true) {
                        if(status == '1'){
                            $("#assignto_selector_picked").show();
                            $("#assignto_selector").hide();
                            $("#assigned_user").show();
                        }
                        else if(status == '5'){
                            $("#assignto_selector_picked").hide();
                            $("#assignto_selector").hide();
                            $("#assigned_user").hide();
                        }
                        else{
                            $("#assignto_selector_picked").hide();
                            $("#assignto_selector").show();
                            $("#assigned_user").show();
                        }

                        $("#selected_status_status").removeClass('text-danger');
                        $("#selected_status_status").removeClass('text-warning');
                        $("#selected_status_status").addClass('text-success');
                        $("#selected_status_status").html('<i class="fa fa-check"></i> That status is deployable. This asset can be checked out.');


                    } else {
                        $("#assignto_selector").hide();
                        $("#selected_status_status").removeClass('text-danger');
                        $("#selected_status_status").removeClass('text-success');
                        $("#selected_status_status").addClass('text-warning');
                        $("#selected_status_status").html('<i class="fa fa-warning"></i> That asset status is not deployable. This asset cannot be checked out. ');
                    }
                }
            });
        }
    }
    $(function () {
        //grab custom fields for this model whenever model changes.
        $('#model_select_id').on("change", fetchCustomFields);

        //initialize assigned user/loc/asset based on statuslabel's statustype
        user_add($(".status_id option:selected").val());

        //whenever statuslabel changes, update assigned user/loc/asset
        $(".status_id").on("change", function () {
            user_add($(".status_id").val());
        });
    });


    // Add another asset tag + serial combination if the plus sign is clicked
    $(document).ready(function() {
        $("#shipment_id_search").focus();
        $(".btn-first-time-check-in").attr('disabled',true);
        var form_field= `<tr>
                    <td><input type="text" name="shipment_id[1]" class="form-control shipment_id"></td>
                    <td><input type="text" name="model_id[1]" class="form-control model_id"></td>
                    <td><input type="text" name="product_name[1]" class="form-control product_name"></td>
                    <td><input type="text" name="supplier_id[1]" class="form-control supplier_id"></td>
                    <td><input type="text" name="order_number[1]" class="form-control order_number"></td>
                    <td><input type="text" name="quantity[1]" class="form-control quantity"></td>
                </tr>`;

        var max_fields      = 100; //maximum input boxes allowed
        var wrapper         = $(".input_fields_wrap"); //Fields wrapper
        var add_button      = $(".add_field_button"); //Add button ID
        var x               = 0; //initial text box count
        var wrapper_product_name = $(".product_name_wrap");
        var image_field = $(".product_image_wrap");
        var order_number_field = $(".product_order_number_wrap");

        //get asset data from storefront by shipment number
        //$('#shipment_id_search').off('keyup').on('keyup', function(event) {
        $(".search").click(function( event ) {
            //e.preventDefault();
            $(".btn-first-time-check-in").attr('disabled',true);
            var barcode = $("#shipment_id_search").val();
            if(barcode == ''){
                alert('Barcode Number required');
                return false;
            }
            $("#shipment_id_search").val('');
            $("#loading-gif").show();
            if(shipment_number_search.includes(barcode)){
                $("#shipment_id_search").val('');
                var tr_html = '';
                tr_html += '<tr style="background-color:#FF0000">';
                var error_message = 'Barcode Already Scanned!';
                var barcode_number = barcode;
                tr_html += '<td style="background-color:#E7E7E7">';
                tr_html += barcode_number;
                tr_html += '</td>';
                tr_html += '<td colspan="5" style="color:#fff;text-align:center;">';
                tr_html += '<i>'+error_message+'</i>';
                tr_html += '</td>';
                tr_html += '<td style="background-color:#fff">';
                tr_html += '<a href="javascript:void" class="remove_field btn btn-default btn-sm"><i class="fa fa-minus"></i></a>';
                tr_html += '</td>';
                tr_html += '</tr>';
                $("#checkin-form").append(tr_html);
                $("#loading-gif").hide();
                $(".remove_field").click(function( event ) {
                    $(this).parents("tr").remove();
                });
                if(x == '0'){
                    $(".btn-first-time-check-in").attr('disabled',true);
                }
                else{
                    $(".btn-first-time-check-in").attr('disabled',false);
                }
                return false;
            }
            shipment_number_search.push(barcode);
            var last_index = $('#last_index').val();
            var csrf_token = "{{ csrf_token() }}";
            $(".btn-first-time-check-in").attr('disabled',true);
            $.ajax({
                type:'POST',
                url: "{{url('/') }}/hardware/shipmentcheckfirsttimecheckin",
                data:{barcode_number:barcode},
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                _token: "{{ csrf_token() }}",
                success:function(response){
                    if(response.status == 'error'){
                        $("#shipment_id_search").val('');
                        var tr_html = '';
                        tr_html += '<tr style="background-color:#FF0000">';
                        var error_message = response.message;
                        var barcode_number = response.barcode_number;
                        tr_html += '<td style="background-color:#E7E7E7">';
                        tr_html += barcode_number;
                        tr_html += '</td>';
                        tr_html += '<td colspan="5" style="color:#fff;text-align:center;">';
                        tr_html += '<i>'+error_message+'</i>';
                        tr_html += '</td>';
                        tr_html += '<td style="background-color:#fff">';
                        tr_html += '<a href="javascript:void" class="remove_field btn btn-default btn-sm"><i class="fa fa-minus"></i></a>';
                        tr_html += '</td>';
                        tr_html += '</tr>';
                        $("#checkin-form").append(tr_html);
                        $("#loading-gif").hide();
                        $(".btn-first-time-check-in").attr('disabled',false);
                        if(x == '0'){
                            $(".btn-first-time-check-in").attr('disabled',true);
                        }
                        $(".remove_field").click(function( event ) {
                            $(this).parents("tr").remove();
                        });
                        return false;
                    }
                    else{
                        $(".btn-first-time-check-in").attr('disabled',true);
                        $.ajax({
                            type:'POST',
                            url: "{{url('/') }}/hardware/shipmentdatafirsttimecheckin",
                            data:{barcode:barcode,last_index:last_index},
                            headers: {
                                "X-Requested-With": 'XMLHttpRequest',
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                            },
                            _token: "{{ csrf_token() }}",
                            success:function(response){
                                if(response.status == 'success'){
                                    x++;
                                    $("#checkin-form").append(response.shipment_data);
                                    $('#last_index').val(response.last_index);
                                    $("#loading-gif").hide();
                                    $(".btn-first-time-check-in").attr('disabled',false);
                                    $(".remove_field").click(function( event ) {
                                        $(this).parents("tr").remove();
                                    });
                                }
                                else{
                                    $(".btn-first-time-check-in").attr('disabled',true);
                                    $("#shipment_id_search").val('');
                                    var tr_html = '';
                                    tr_html += '<tr style="background-color:#FF0000">';
                                    var error_message = response.message;
                                    var barcode_number = response.barcode_number;
                                    tr_html += '<td style="background-color:#E7E7E7">';
                                    tr_html += barcode_number;
                                    tr_html += '</td>';
                                    tr_html += '<td colspan="5" style="color:#fff;text-align:center;">';
                                    tr_html += '<i>'+error_message+'</i>';
                                    tr_html += '</td>';
                                    tr_html += '<td style="background-color:#fff">';
                                    tr_html += '<a href="javascript:void" class="remove_field btn btn-default btn-sm"><i class="fa fa-minus"></i></a>';
                                    tr_html += '</td>';
                                    tr_html += '</tr>';
                                    $("#checkin-form").append(tr_html);
                                    $("#loading-gif").hide();
                                    $(".btn-first-time-check-in").attr('disabled',false);
                                    $(".remove_field").click(function( event ) {
                                        $(this).parents("tr").remove();
                                    });
                                    return false;
                                }
                            }
                        });
                    }
                }
            });
            return false;
        });
        //Bulk Checkin form submission
        $("#create-form").submit(function( event ) {
            $(".btn-first-time-check-in").attr('disabled',true);
            $("#loading-gif").show();
        });
    });
</script>
@stop
