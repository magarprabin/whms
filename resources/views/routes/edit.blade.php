@extends('layouts/edit-form', [
    'createText' => trans('admin/routes/general.create') ,
    'updateText' => trans('admin/routes/general.update'),
    'helpPosition'  => 'right',
    'helpText' => trans('help.routes'),
    'topSubmit'  => 'true',
    'formAction' => (isset($item->id)) ? route('routes.update', ['route' => $item->id]) : route('routes.store'),
])

@section('inputFields')

@include ('partials.forms.edit.name', ['translated_name' => trans('admin/routes/general.name')])
<!-- Name -->
<div class="form-group {{ $errors->has('start_location') ? ' has-error' : '' }}">
    <label for="start_location" class="col-md-3 control-label">{{ trans('admin/routes/general.start_location') }}</label>
    <div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'start_location')) ? ' required' : '' }}">
        <input class="form-control" type="text" name="start_location" aria-label="start_location" id="start_location" value="{{ old('start_location', $item->start_location) }}"{!!  (\App\Helpers\Helper::checkIfRequired($item, 'start_location')) ? ' data-validation="required" required' : '' !!} />
        {!! $errors->first('start_location', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>
<div class="form-group {{ $errors->has('end_location') ? ' has-error' : '' }}">
    <label for="end_location" class="col-md-3 control-label">{{ trans('admin/routes/general.end_location') }}</label>
    <div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'end_location')) ? ' required' : '' }}">
        <input class="form-control" type="text" name="end_location" aria-label="end_location" id="end_location" value="{{ old('end_location', $item->end_location) }}"{!!  (\App\Helpers\Helper::checkIfRequired($item, 'end_location')) ? ' data-validation="required" required' : '' !!} />
        {!! $errors->first('end_location', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>
<!-- Suppliers -->
@include ('partials.forms.edit.supplier-select-multiple', ['translated_name' => trans('admin/routes/general.supplier'),'fieldname' => 'supplier_id', 'required' => 'true','multiple'=>'true'])
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
@section('moar_scripts')
    @include ('routes.script')
@stop