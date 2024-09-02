@extends('layouts/edit-form', [
    'createText' => trans('admin/riders/general.create') ,
    'updateText' => trans('admin/riders/general.update'),
    'helpPosition'  => 'right',
    'helpText' => trans('help.riders'),
    'topSubmit'  => 'true',
    'formAction' => (isset($item->id)) ? route('riders.update', ['rider' => $item->id]) : route('riders.store'),
])

@section('inputFields')

    @include ('partials.forms.edit.filtered-user-select', ['translated_name' => trans('general.riders'),'fieldname' => 'user_id', 'required' => 'true'])

    @include('partials.forms.edit.vehicle-type',['translated_name'=>trans('admin/riders/general.vehicle_type'),'field_name'=>'vehicle_type','required'=>'true','vehicleTypesList'=>['two_wheeler'=>'Two Wheeler','four_wheeler'=>'Four Wheeler']])
    <div class="form-group {{ $errors->has('shift_from_time') ? ' has-error' : '' }}">
        <label for="shift_from_time" class="col-md-3 control-label">{{ trans('admin/riders/general.shift_from_time') }}</label>
        <div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'shift_from_time')) ? ' required' : '' }}">
            <input class="form-control" type="text" name="shift_from_time" aria-label="shift_from_time" id="shift_from_time" value="{{ old('shift_from_time', $item->shift_from_time) }}"{!!  (\App\Helpers\Helper::checkIfRequired($item, 'shift_from_time')) ? ' data-validation="required" required' : '' !!} />
            {!! $errors->first('shift_from_time', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>
    <div class="form-group {{ $errors->has('shift_to_time') ? ' has-error' : '' }}">
        <label for="shift_to_time" class="col-md-3 control-label">{{ trans('admin/riders/general.shift_to_time') }}</label>
        <div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'shift_to_time')) ? ' required' : '' }}">
            <input class="form-control" type="text" name="shift_to_time" aria-label="shift_to_time" id="shift_to_time" value="{{ old('shift_to_time', $item->shift_to_time) }}"{!!  (\App\Helpers\Helper::checkIfRequired($item, 'shift_to_time')) ? ' data-validation="required" required' : '' !!} />
            {!! $errors->first('shift_to_time', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>
    <!-- Suppliers -->
    @include('partials.forms.edit.route-status',['translated_name'=>trans('general.status'),'field_name'=>'status','required'=>'true','statuslabel_list'=>['1'=>'Active','0'=>'Inactive']])
@stop

@section('content')
    @parent

    @if ($snipeSettings->default_eula_text!='')
        <!-- Modal -->
        <div class="modal fade" id="eulaModal" tabindex="-1" role="dialog" aria-labelledby="eulaModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h2 class="modal-title" id="eulaModalLabel">{{ trans('admin/settings/general.default_eula_text') }}</h2>
                    </div>
                    <div class="modal-body">
                        {{ \App\Models\Setting::getDefaultEula() }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('button.cancel') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@stop
