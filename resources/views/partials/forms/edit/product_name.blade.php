<!-- Name -->
<div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
    <label for="name" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'name')) ? ' required' : '' }}">
        <input class="form-control" type="text" name="product_name[1]" aria-label="name" id="product_name1" value="{{ old('name', $item->name) }}"{!!  (\App\Helpers\Helper::checkIfRequired($item, 'name')) ? ' data-validation="required" required' : '' !!} />
        {!! $errors->first('name', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>