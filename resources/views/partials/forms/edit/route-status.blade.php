<!-- Status -->
<div class="form-group {{ $errors->has('status') ? ' has-error' : '' }}">
    <label for="status" class="col-md-3 control-label">{{ trans('admin/hardware/form.status') }}</label>
    <div class="col-md-7 col-sm-11{{  (\App\Helpers\Helper::checkIfRequired($item, 'status')) ? ' required' : '' }}">
        {{ Form::select('status', $statuslabel_list , old('status', $item->status), array('class'=>'select2 status', 'style'=>'width:100%','id'=>'status_select_id', 'aria-label'=>'status', 'data-validation' => "required")) }}
        {!! $errors->first('status', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>

    <div class="col-md-7 col-sm-11 col-md-offset-3" id="status_helptext">
        <p id="selected_status_status" style="display:none;"></p>
    </div>

</div>
