@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Individual Package Checkout
    @parent
@stop

{{-- Page content --}}
@section('content')

    <style>
        .input-group {
            padding-left: 0px !important;
        }
        blink {
          animation: 1s linear infinite condemned_blink_effect;
          color: #ff0000;
        }

        #invalid-barcode{
            color: #ff0000;
            font-weight: bold;
        }

        #valid-barcode{
            color: #008000;
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
    </style>

    <div class="row">
        <!-- left column -->
        <div class="col-md-10">
            <div class="box box-default">
                <form class="form-horizontal" method="post" action="" autocomplete="off" id="individual-checkout-form">
                    <div class="box-body">
                    {{csrf_field()}}
                    <div class="form-group">
                        <label class="col-md-3 control-label">{{ trans('admin/hardware/form.shipment_orderno') }}</label>
                        <div class="col-md-2">
                            <input type="text" name="barcode_number" id="barcode_number" class="barcode_number form-control">
                            <input type="hidden" name="shipment_number" id="shipment_number" class="shipment_number form-control">
                            <input type="hidden" name="asset_id" class="form-control asset_id" id="asset_id">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="search btn btn-success btn-sm">Check</button>
                        </div>
                        <div class="col-md-1" id="loading-gif" style="display: none;">
                            <img src="{{URL::asset('/uploads/assets/loading-waiting.gif')}}" style="height:50px;">

                        </div>
                        <div class="col-md-3">
                            <span id="invalid-barcode"></span>
                            <span id="valid-barcode"></span>
                        </div>
                    </div>

                        <!-- Status -->
                        <div class="form-group {{ $errors->has('status_id') ? 'error' : '' }}" id="status-form">
                            {{ Form::label('status_id', trans('admin/hardware/form.status'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-7 required">
                                {{ Form::select('status_id', $statusLabel_list, 0, array('class'=>'select2', 'style'=>'width:100%','', 'aria-label'=>'status_id')) }}
                                {!! $errors->first('status_id', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group" id="carrier-form" style="display:none;">
                            {{ Form::label('carrier_id', trans('admin/hardware/form.carrier'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-7 required">
                                <select name="carrier_id" id="carrier_id" class="carrier_id form-control">
                                    <option value="">--Select--</option>
                                    @foreach($carrier_lists as $carrier_list)
                                    <option value="{{ $carrier_list->id }}">{{ $carrier_list->carrier_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <input name="checkout_to_type" value="location" aria-label="checkout_to_type" class="active" type="hidden">
                        <div id="location-select-individual" id="location-form">
                             @include ('partials.forms.edit.location-select-multiple', ['translated_name' => 'Checkout to Location', 'fieldname' => 'assigned_location', 'required'=>'true'])
                        </div>
                        <div id="location-select-individual" id="location-form-hidden" style="display:none;">
                            <input type="hidden" name="assigned_location_user" value="{{ $logged_in_user_location_id }}">
                        </div>

                    <!-- Checkout/Checkin Date -->
                        <div class="form-group {{ $errors->has('checkout_at') ? 'error' : '' }}">
                            {{ Form::label('checkout_at', trans('admin/hardware/form.checkout_date'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <div class="input-group date col-md-7" data-date-format="yyyy-mm-dd" data-date-end-date="0d">
                                    <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="checkout_at" id="checkout_at" value="{{ old('checkout_at', date('Y-m-d')) }}" readonly>
                                </div>
                                {!! $errors->first('checkout_at', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Expected Checkin Date -->
                        <div class="form-group {{ $errors->has('expected_checkin') ? 'error' : '' }}">
                            <span id="expected_checkin_label">
                                {{ Form::label('expected_checkin', trans('admin/hardware/form.expected_checkin'), array('class' => 'col-md-3 control-label')) }}
                            </span>
                            <div class="col-md-8">
                                <div class="input-group date col-md-7" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-start-date="0d">
                                    <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="expected_checkin" id="expected_checkin" value="{{ old('expected_checkin') }}">
                                    <span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                                </div>
                                {!! $errors->first('expected_checkin', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                            {{ Form::label('note', trans('admin/hardware/form.notes'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <textarea class="col-md-6 form-control" id="note" name="note"></textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>
                    </div> <!--/.box-body-->
                    <div class="box-footer">
                        <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
                        <div class="pull-right">
                            <button type="submit" class="btn btn-primary btn-individual-checkout" disabled><i class="fa fa-check icon-white" aria-hidden="true"></i>Submit</button>
                            <div class="col-md-1 pull-right" id="loading-gif-submit" style="display: none;">
                                <img src="{{URL::asset('/uploads/assets/loading-waiting.gif')}}" style="height:50px;">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div> <!--/.col-md-7-->
    </div>
@stop

@section('moar_scripts')
<script nonce="{{ csrf_token() }}">
    $(document).ready(function() {
        $(".search").click(function( event ) {
            var barcode_number = $("#barcode_number").val();
            var csrf_token = "{{ csrf_token() }}";
            $.ajax({
                type:'POST',
                url: "{{url('/') }}/hardware/barcodedata",
                data:{barcode_number:barcode_number},
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                _token: "{{ csrf_token() }}",
                success:function(response){
                    if(response.status == 'success'){
                        $('#asset_id').val(response.asset_id);
                        $("#valid-barcode").html(response.message);
                        $("#shipment_number").val(response.shipment_number);
                        setTimeout(function() { 
                            $("#valid-barcode").html('');
                        }, 5000);
                        $(".btn-individual-checkout").attr('disabled',false);
                    }
                    else{
                        $(".btn-individual-checkout").attr('disabled',true);
                        $("#invalid-barcode").html(response.message);
                        $("#loading-gif").hide();
                        $("#barcode_number").val('');
                        setTimeout(function() { 
                            $("#invalid-barcode").html('');
                        }, 3000);
                    }
                }
            });
            return false;
        });
        $("#status_id").change(function( event ) {
            var status = $(this).val();
            if(status == 7 || status == 10){
                var user_location = '{{ $logged_in_user_location_id }}';
                console.log(user_location);
                if(status == 7){
                    $("#expected_checkin_label").html('<label for="expected_checkin" class="col-md-3 control-label">Expected Delivery Date</label>');
                }
                else{
                    $("#expected_checkin_label").html('<label for="expected_checkin" class="col-md-3 control-label">Expected Checkin Date</label>');
                }
                $("#assigned_location_location_select").val(user_location);
                $("#location-form-hidden").hide();
                $("#carrier-form").hide();
                $("#location-select-individual").hide();
                $("#carrier_id").attr('required',false);
            }
            else if(status == 9){
                var user_location = '{{ $logged_in_user_location_id }}';
                $("#assigned_location_location_select").val(user_location);
                $("#location-form-hidden").show();
                $("#carrier-form").show();
                $("#location-select-individual").hide();
                $("#carrier_id").attr('required',true);
            }
            else{
                $("#expected_checkin_label").html('<label for="expected_checkin" class="col-md-3 control-label">Expected Checkin Date</label>');
                $("#location-select-individual").show();
                $("#location-form-hidden").hide();
                $("#carrier-form").hide();
                $("#carrier_id").attr('required',false);
            }
        });
        //Individual Checkout form submission
        $("#individual-checkout-form").submit(function( event ) {
            $(".btn-individual-checkout").attr('disabled',true);
            $("#loading-gif-submit").show();
        });
        $("#status_id").trigger('change');
    });
</script>
@stop