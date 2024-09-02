<div class="form-group" id="assignto_selector_picked"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>
    {{ Form::label('checkout_to_type', trans('admin/hardware/form.checkout_to'), array('class' => 'col-md-3 control-label')) }}
    <div class="col-md-8">
        <div class="btn-group" data-toggle="buttons">
            @if ((isset($user_select)) && ($user_select!='false'))
            <label class="btn btn-default active">
                <input name="checkout_to_type" value="user" aria-label="checkout_to_type" type="radio" checked="checked"><i class="fa fa-user" aria-hidden="true"></i> {{ trans('general.user') }}
            </label>
            @endif
            @if ((isset($location_select)) && ($location_select!='false'))
            <label class="btn btn-default">
                <input name="checkout_to_type" value="location" aria-label="checkout_to_type" class="active" type="radio"><i class="fa fa-map-marker" aria-hidden="true"></i> {{ trans('general.location') }}
            </label>
            @endif

            {!! $errors->first('checkout_to_type', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>
</div>
