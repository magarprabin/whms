<!-- Location -->
<div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>

    <label for="name" class="col-md-3 control-label">{{ $translated_name }}<span class="req">*</span></label>
    <div class="col-md-6{{  ((isset($required) && ($required =='true'))) ?  ' required' : '' }}">
        <select class="location_id form-control" name="{{ $fieldname }}" style="width: 100%" id="location_id" required>
            <option value="">--Select--</option>
            @foreach($locations as $location)
            <option value="{{ $location->id }}" {{ $user_location_id == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
            @endforeach
        </select>
    </div>
</div>