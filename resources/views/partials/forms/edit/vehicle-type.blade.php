<!-- Status -->
<div class="form-group {{ $errors->has('vehicle_type') ? ' has-error' : '' }}">
    <label for="vehicle_type" class="col-md-3 control-label">{{ trans('admin/riders/general.vehicle_type') }}</label>
    <div class="col-md-7 col-sm-11{{  (\App\Helpers\Helper::checkIfRequired($item, 'vehicle_type')) ? ' required' : '' }}">
        {{ Form::select('vehicle_type', $vehicleTypesList , old('vehicle_type', $item->vehicle_type), array('class'=>'select2 vehicle_type', 'style'=>'width:100%','id'=>'vehicle_type_id', 'aria-label'=>'vehicle_type', 'data-validation' => "required")) }}
        {!! $errors->first('vehicle_type', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>

    <div class="col-md-7 col-sm-11 col-md-offset-3" id="vehicle_types_helptext">
        <p id="selected_vehicle_types" style="display:none;"></p>
    </div>

</div>
