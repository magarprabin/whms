<div class="form-group {{ $errors->has((isset($fieldname) ? $fieldname : 'image')) ? 'has-error' : '' }}">
    <label class="col-md-3 control-label" for="image">{{ trans('general.image_upload') }}<span class="req">*</span></label>
    <div class="col-md-9">

        <input type="file" id="image" name="{{ (isset($fieldname) ? $fieldname : 'image1') }}" aria-label="image" class="sr-only">

        <label class="btn btn-default" aria-hidden="true">
            {{ trans('button.select_file')  }}
            <input type="file" name="{{ (isset($fieldname) ? $fieldname : 'image1') }}" class="js-uploadFile" id="uploadFile1" data-maxsize="{{ \App\Helpers\Helper::file_upload_max_size() }}" accept="image/gif,image/jpeg,image/webp,image/png,image/svg,image/svg+xml" style="display:none; max-width: 90%" aria-label="image" aria-hidden="true">
        </label>
        <span class='label label-default' id="uploadFile-info"></span>

        <p class="help-block" id="uploadFile-status">{{ trans('general.image_filetypes_help', ['size' => \App\Helpers\Helper::file_upload_max_size_readable()]) }}</p>
        {!! $errors->first('image', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
    </div>
    <div class="col-md-4 col-md-offset-3" aria-hidden="true">
        <img id="uploadFile1-imagePreview" style="max-width: 200px; display: none;" alt="Uploaded image thumbnail">
    </div>
</div>

