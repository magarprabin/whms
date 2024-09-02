<!-- Name -->
<div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}">
    <label for="similar_name" class="col-md-3 control-label">Same Inventory name and image</label>
    <div class="col-md-7 col-sm-12 required">
        <input type="radio" name="similar_name" id="similar_name_yes" value="1" checked />&nbsp;Yes
        <input type="radio" name="similar_name" id="similar_name_no" value="0" />&nbsp;No
    </div>
</div>
