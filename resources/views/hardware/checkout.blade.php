@extends('layouts/default')

{{-- Page title --}}
@section('title')
   Shipment Package Checkout
    @parent
@stop

{{-- Page content --}}
@section('content')

    <style>

        .input-group {
            padding-left: 0px !important;
        }
    </style>

    <div class="row">
        <!-- left column -->
        <div class="col-md-7">
            <div class="box box-default">
                <div class="box-body">
          <div class="col-md-12">
                <form class="form-horizontal" method="post" action="" autocomplete="off" id="checkout_form">
                    {{csrf_field()}}
                    <!-- AssetModel name -->
                    <input type="hidden" name="model_id" value="3">
                    <div class="form-group">
                        <label class="col-md-3 control-label">Shipment Number</label>
                        <div class="col-md-8">
                            <input class="form-control" type="text" name="barcode_number" aria-label="barcode_number" id="barcode_number"
                           value="{{ old('barcode_number', $asset->barcode_number) }}" readonly />
                           <input type="hidden" name="shipment_number" id="shipment_number" class="shipment_number form-control" value="{{ old('shipment_number', $asset->shipment_number) }}">
                        </div>
                    </div>
                    <!-- Asset Name -->
                    <div class="form-group {{ $errors->has('name') ? 'error' : '' }}">
                        {{ Form::label('name', trans('admin/hardware/form.name'), array('class' => 'col-md-3 control-label')) }}
                        <div class="col-md-8">
                            <input class="form-control" type="text" name="name" id="name" value="{{ old('name', $asset->name) }}" tabindex="1">
                            {!! $errors->first('name', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="form-group {{ $errors->has('status_id') ? 'error' : '' }}" id="status-form">
                        {{ Form::label('status_id', trans('admin/hardware/form.status'), array('class' => 'col-md-3 control-label')) }}
                        <div class="col-md-7 required">
                            {{ Form::select('status_id', $statusLabel_list, $asset->status_id, array('class'=>'select2', 'style'=>'width:100%','', 'aria-label'=>'status_id')) }}
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
                    <div id="location-select-individual">
                        @include ('partials.forms.edit.location-select-multiple', ['translated_name' => 'Checkout to Location', 'fieldname' => 'assigned_location', 'required'=>'true'])
                    </div>
                    <div id="location-select-individual-hidden" style="display:none;" class="form-group">
                        <label class="col-md-3 control-label">Checkout to</label>
                        <div class="col-md-7">
                            <input type="text" id="current_user_location_name" name="current_user_location_name" value="{{ $current_user_location_name }}" class="form-control" readonly>
                        </div>
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
                                <textarea class="col-md-6 form-control" id="note" name="note">{{ old('note', $asset->note) }}</textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        @if ($asset->requireAcceptance() || $asset->getEula() || ($snipeSettings->slack_endpoint!=''))
                            <div class="form-group notification-callout">
                                <div class="col-md-8 col-md-offset-3">
                                    <div class="callout callout-info">

                                        @if ($asset->requireAcceptance())
                                            <i class="fa fa-envelope" aria-hidden="true"></i>
                                            {{ trans('admin/categories/general.required_acceptance') }}
                                            <br>
                                        @endif

                                        @if ($asset->getEula())
                                            <i class="fa fa-envelope" aria-hidden="true"></i>
                                            {{ trans('admin/categories/general.required_eula') }}
                                            <br>
                                        @endif

                                        @if ($snipeSettings->slack_endpoint!='')
                                            <i class="fa fa-slack" aria-hidden="true"></i>
                                            A slack message will be sent
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    <div class="box-footer pull-right">
                        <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-primary btn-checkout"><i class="fa fa-check icon-white" aria-hidden="true"></i> {{ trans('general.checkout') }}</button>
                        <div class="col-md-1" id="loading-gif" style="display: none;">
                            <img src="{{URL::asset('/uploads/assets/loading-waiting.gif')}}" style="height:50px;">
                        </div>
                    </div>
                </form>
            </div>
        </div> <!--/.col-md-7-->
    </div>
</div>
</div>
@stop

@section('moar_scripts')
    @include('partials/assets-assigned')

    <script>
        $("#status_id").change(function( event ) {
            var status = $(this).val();
            var user_location = '{{ $logged_in_user_location_id }}';
            if(status == 7){
                $("#expected_checkin_label").html('<label for="expected_checkin" class="col-md-3 control-label">Expected Delivery Date</label>');
                
                $("#carrier-form").hide();
                $("#carrier_id").attr('required',false);
            }
            else if(status == 9){
                $("#carrier-form").show();
                $("#carrier_id").attr('required',true);
            }
            else{
                $("#expected_checkin_label").html('<label for="expected_checkin" class="col-md-3 control-label">Expected Checkin Date</label>');
                $("#carrier-form").hide();
                $("#carrier_id").attr('required',false);
            }
            if(status == 6){
                $("#assigned_location_location_select").val('');
                $("#location-select-individual").show();
                $("#location-select-individual-hidden").hide();
            }
            else{
                $("#assigned_location_location_select").val(user_location);
                $("#location-select-individual").hide();
                $("#location-select-individual-hidden").show();
            }
        });
        
        //Checkout form submission
        $("#checkout_form").submit(function( event ) {
            $(".btn-checkout").attr('disabled',true);
            $("#loading-gif").show();
        });

        $("#status_id").trigger('change');
    </script>
@stop