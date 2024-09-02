@extends('layouts/edit-form', [
    'createText' => trans('admin/vehicles/general.create') ,
    'updateText' => trans('admin/vehicles/general.update'),
    'helpPosition'  => 'right',
    'helpText' => trans('help.vehicles'),
    'topSubmit'  => 'true',
    'formAction' => (isset($item->id)) ? route('vehicles.update', ['vehicle' => $item->id]) : route('vehicles.store'),
])

@section('inputFields')

    @include ('partials.forms.edit.name', ['translated_name' => trans('general.name'),'fieldname' => 'name', 'required' => 'true'])

    @include('partials.forms.edit.vehicle-type',['translated_name'=>trans('admin/vehicles/general.vehicle_type'),'field_name'=>'vehicle_type','required'=>'true','vehicleTypesList'=>['two_wheeler'=>'Two Wheeler','four_wheeler'=>'Four Wheeler']])
    <div class="form-group {{ $errors->has('vehicle_no') ? ' has-error' : '' }}">
        <label for="vehicle_no" class="col-md-3 control-label">{{ trans('admin/vehicles/general.vehicle_no') }}</label>
        <div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'vehicle_no')) ? ' required' : '' }}">
            <input class="form-control" type="text" name="vehicle_no" aria-label="vehicle_no" id="vehicle_no" value="{{ old('vehicle_no', $item->vehicle_no) }}"{!!  (\App\Helpers\Helper::checkIfRequired($item, 'vehicle_no')) ? ' data-validation="required" required' : '' !!} />
            {!! $errors->first('vehicle_no', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
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
