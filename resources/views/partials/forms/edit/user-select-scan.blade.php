<div id="assigned_user" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>

    {{ Form::label($fieldname, $translated_name, array('class' => 'col-md-3 control-label')) }}

    <div class="col-md-6{{  ((isset($required)) && ($required=='true')) ? ' required' : '' }}">
        <input type="hidden" name="assigned_to" class="form-control" value="{{ $user_id }}">
        <input type="text" name="assigned_to_name" class="form-control" value="{{ \App\Models\User::find($user_id)->present()->fullName }}" readonly>
    </div>

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span></div>') !!}

</div>