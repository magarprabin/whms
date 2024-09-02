@extends('layouts/edit-form-create-inventory', [
    'createText' => trans('admin/accessories/general.create') ,
    'updateText' => trans('admin/accessories/general.update'),
    'helpPosition'  => 'right',
    'helpText' => trans('help.accessories'),
    'formAction' => (isset($item->id)) ? route('accessories.update', ['accessory' => $item->id]) : route('accessories.store'),
])

{{-- Page content --}}
@section('inputFields')

@include ('partials.forms.edit.company-select', ['translated_name' => trans('general.company'), 'fieldname' => 'company_id'])
@include ('partials.forms.edit.similar')
<style type="text/css">
    .req {
        color: red;
    }
</style>
<!-- Inventory Tag -->
<div class="form-group {{ $errors->has('inventory_tag') ? ' has-error' : '' }}">
    <label for="inventory_tag" class="col-md-3 control-label">{{ trans('general.inventory_tag') }}</label>

    <!-- we are editing an existing inventory -->
    @if  ($item->id)
        <div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'inventory')) ? ' required' : '' }}">
            <input readonly class="form-control" type="text" name="inventory_tags[1]" id="inventory_tag" value="{{ Request::old('inventory_tag', $item->inventory_tag) }}" data-validation="required">
            {!! $errors->first('inventory_tags', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
            {!! $errors->first('inventory_tag', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
        </div>
    @else
    <!-- we are creating a new inventory - let people use more than one asset tag -->
        <div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'inventory_tag')) ? ' required' : '' }}">
            <input readonly class="form-control" type="text" name="inventory_tags[1]" id="inventory_tag" value="{{ Request::old('inventory_tag', \App\Models\Accessory::autoincrement_inventory()) }}" data-validation="required">
            {!! $errors->first('inventory_tags', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
            {!! $errors->first('inventory_tag', '<span class="alert-msg"><i class="fa fa-times"></i> :message</span>') !!}
        </div>
        <div class="col-md-2 col-sm-12">
            <button class="add_inventory_button btn btn-default btn-sm">
                <i class="fa fa-plus"></i>
            </button>
        </div>
    @endif
</div>
@include ('partials.forms.edit.accessory_name', ['translated_name' => trans('admin/accessories/general.accessory_name')])
<input type="hidden" name="category_id" value="19">
@include ('partials.forms.edit.model_number_create_accessory')
@include ('partials.forms.edit.accessory-upload')
<div class="input_wrap"></div>
@include ('partials.forms.edit.supplier-select-inventory', ['translated_name' => trans('general.supplier'), 'fieldname' => 'supplier_id'])
{{--@include ('partials.forms.edit.manufacturer-select', ['translated_name' => trans('general.manufacturer'), 'fieldname' => 'manufacturer_id'])--}}
@include ('partials.forms.edit.location-select-accessory', ['translated_name' => trans('general.location'), 'fieldname' => 'location_id'])
{{--@include ('partials.forms.edit.order_number')--}}
@include ('partials.forms.edit.purchase_date_accessory')
{{--@include ('partials.forms.edit.purchase_cost')--}}
{{--@include ('partials.forms.edit.quantity')--}}
{{--@include ('partials.forms.edit.minimum_quantity')--}}
<!-- Image -->
@if ($item->image)
    <div class="form-group {{ $errors->has('image_delete') ? 'has-error' : '' }}">
        <label class="col-md-3 control-label" for="image_delete">{{ trans('general.image_delete') }}</label>
        <div class="col-md-5">
            {{ Form::checkbox('image_delete') }}
            <img src="{{  Storage::disk('public')->url('accessories/'.e($item->image)) }}" class="img-responsive" />
            {!! $errors->first('image_delete', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
        </div>
    </div>
@endif

@include ('partials.forms.edit.notes')
@stop

@section('moar_scripts')

<script nonce="{{ csrf_token() }}">
    // Add another inventory tag + serial combination if the plus sign is clicked
    $(document).ready(function() {
        var max_fields      = 100; //maximum input boxes allowed
        var wrapper_input = $(".input_wrap");
        var wrapper         = $(".input_inventory_wrap"); //Fields wrapper
        var add_button      = $(".add_inventory_button"); //Add button ID
        var x               = 1; //initial text box count
        var name_field = $(".input_name_wrap");
        var category_field = $(".input_category_wrap");
        var model_number_field = $(".input_model_number_wrap");
        var image_field = $(".input_image_wrap");

        $(add_button).click(function(e){ //on add input button click

            e.preventDefault();

            var auto_tag        = $("#inventory_tag").val().replace(/[^\d]/g, '');
            var box_html        = '';
            var name_html = '';
            var category_html = '';
            var model_number_html = '';
            var image_html = '';
            const zeroPad 		= (num, places) => String(num).padStart(places, '0');

            // Check that we haven't exceeded the max number of asset fields
            if (x < max_fields) {

                if (auto_tag!='') {
                    auto_tag = zeroPad(parseInt(auto_tag) + parseInt(x),auto_tag.length);
                } else {
                    auto_tag = '';
                }

                x++; //text box increment

                box_html += '<span class="fields_wrapper">';
                box_html += '<div class="form-group" id="inventory_tag_form_'+x+'"><label for="inventory_tag" class="col-md-3 control-label">{{ trans('general.inventory_tag') }} ' + x + '</label>';
                box_html += '<div class="col-md-7 col-sm-12 required">';
                box_html += '<input readonly type="text"  class="form-control" name="inventory_tags[' + x + ']" value="{{ (($snipeSettings->auto_increment_inventory_prefix!='') && ($snipeSettings->auto_increment_inventory=='1')) ? $snipeSettings->auto_increment_inventory_prefix : '' }}'+ auto_tag +'" data-validation="required">';
                box_html += '</div>';
                box_html += '<div class="col-md-2 col-sm-12">';
                box_html += '<a href="#" class="remove_field btn btn-default btn-sm" tag_id="'+x+'"><i class="fa fa-minus"></i></a>';
                box_html += '</div>';
                box_html += '</div>';
                box_html += '</div>';
                {{--box_html += '<div class="form-group"><label for="serial" class="col-md-3 control-label">{{ trans('admin/hardware/form.serial') }} ' + x + '</label>';--}}
                {{--box_html += '<div class="col-md-7 col-sm-12">';--}}
                // box_html += '<input type="text"  class="form-control" name="serials[' + x + ']">';
                box_html += '</div>';
                box_html += '</div>';
                box_html += '</span>';
                //inventory name
                box_html += '<div class="form-group {{ $errors->has('name') ? ' has-error' : '' }}" id="inventory_name_'+x+'">';
                box_html += '<label for="name" class="col-md-3 control-label">{{ trans('admin/accessories/general.accessory_name') }} ' + x + '</label>';
                box_html += '<div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'name')) ? ' required' : '' }}">';
                box_html += '<input class="form-control" type="text" name="name[' + x + ']" aria-label="name" id="name' + x + '" value="{{ old('name', $item->name) }}"{!!  (\App\Helpers\Helper::checkIfRequired($item, 'name')) ? ' data-validation="required" required' : '' !!} />';
                box_html += '{!! $errors->first('name', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}';
                box_html += '</div>';
                box_html += '</div>';
                //Product Code
                box_html += '<div class="form-group {{ $errors->has('model_number') ? ' has-error' : '' }}" id="model_number_form_'+x+'">';
                box_html += '<label for="model_number" class="col-md-3 control-label">{{ trans('general.model_no') }} ' + x + '</label>';
                box_html += '<div class="col-md-7">';
                box_html += '<input class="form-control" type="text" name="model_number[' + x + ']" aria-label="model_number" id="model_number' + x + '" value="{{ old('model_number', $item->model_number) }}" />';
                box_html += '{!! $errors->first('model_number', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}';
                box_html += '</div>';
                box_html += '</div>';
                //Image Upload
                box_html += '<div class="form-group imag" id="image_upload_form_'+x+'">';
                box_html += '<label class="col-md-3 control-label" for="image">Upload Image' + x + '</label>';
                box_html += '<div class="col-md-9">';
                box_html += '<input type="file" id="image' + x + '" name="image' + x + '" aria-label="image" class="sr-only">';
                box_html += '<label class="btn btn-default" aria-hidden="true">';
                box_html += '{{ trans('button.select_file')  }}';
                box_html += '<input type="file" name="image' + x + '" class="js-uploadFile-add" id="uploadFile' + x + '" data-maxsize="{{ \App\Helpers\Helper::file_upload_max_size() }}" accept="image/gif,image/jpeg,image/webp,image/png,image/svg,image/svg+xml" style="display:none; max-width: 90%" aria-label="image" aria-hidden="true">';
                box_html += '</label>';
                box_html += '<span class="label label-default" id="uploadFile' + x + '-info"></span>';
                box_html += '<p class="help-block" id="uploadFile' + x + '-status">{{ trans('general.image_filetypes_help', ['size' => \App\Helpers\Helper::file_upload_max_size_readable()]) }}</p>';
                box_html += `{!! $errors->first('image', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}`;
                box_html += '</div>';
                box_html += '<div class="col-md-4 col-md-offset-3" aria-hidden="true">';
                box_html += '<img id="uploadFile' + x + '-imagePreview" style="max-width: 200px; display: none;" alt="Uploaded image thumbnail">';
                box_html += '</div>';
                box_html += '</div>';
                $(wrapper_input).append(box_html);
                function htmlEntities(str) {
                    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                }
                function formatBytes(bytes) {
                    if(bytes < 1024) return bytes + " Bytes";
                    else if(bytes < 1048576) return(bytes / 1024).toFixed(2) + " KB";
                    else if(bytes < 1073741824) return(bytes / 1048576).toFixed(2) + " MB";
                    else return(bytes / 1073741824).toFixed(2) + " GB";
                }
                function readURL(input, $preview) {
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            $preview.attr('src', e.target.result);
                        };
                        reader.readAsDataURL(input.files[0]);
                    }
                }
                $(".js-uploadFile-add").bind("change",function(){
                    var $this = $(this);
                    var id = '#' + $this.attr('id');
                    var status = id + '-status';
                    var $status = $(status);
                    $status.removeClass('text-success').removeClass('text-danger');
                    $(status + ' .goodfile').remove();
                    $(status + ' .badfile').remove();
                    $(status + ' .previewSize').hide();
                    $(id + '-info').html('');

                    var max_size = $this.data('maxsize');
                    var total_size = 0;

                    for (var i = 0; i < this.files.length; i++) {
                        total_size += this.files[i].size;
                        $(id + '-info').append('<span class="label label-default">' + htmlEntities(this.files[i].name) + ' (' + formatBytes(this.files[i].size) + ')</span> ');
                    }

                    console.log('Max size is: ' + max_size);
                    console.log('Real size is: ' + total_size);
                    if (total_size > max_size) {
                        $status.addClass('text-danger').removeClass('help-block').prepend('<i class="badfile fa fa-times"></i> ').append('<span class="previewSize"> Upload is ' + formatBytes(total_size) + '.</span>');
                    } else {

                        $status.addClass('text-success').removeClass('help-block').prepend('<i class="goodfile fa fa-check"></i> ');
                        var $preview =  $(id + '-imagePreview');
                        readURL(this, $preview);
                        $preview.fadeIn();
                    }

                });
                var similar = $("input[type='radio'][name='similar_name']:checked").val();
                if(similar == '1'){
                    $("#inventory_name_"+x).hide();
                    $("#image_upload_form_"+x).hide();
                    $("#name"+x).attr('required',false);
                }
                $(".remove_field").click(function( event ) {
                    event.stopPropagation();
                    event.stopImmediatePropagation();
                    $(".add_inventory_button").removeAttr('disabled');
                    $(".add_inventory_button").removeClass('disabled');
                    var tag_id = $(this).attr('tag_id');
                    $("#inventory_tag_form_"+tag_id).remove();
                    $("#inventory_name_"+tag_id).remove();
                    $("#model_number_form_"+tag_id).remove();
                    $("#image_upload_form_"+tag_id).remove();
                    var similar = $("input[type='radio'][name='similar_name']:checked").val();
                    if(similar == '0'){
                        $("#name"+tag_id).parent('div').parent('div').remove();
                        $("#image"+tag_id).parent('div').parent('div').remove();
                    }
                    x--;
                    console.log(x);
                });
            } else {
                $(".add_inventory_button").attr('disabled');
                $(".add_inventory_button").addClass('disabled');
            }
        });

        $("#similar_name_yes").click(function( event ) {
            for(var i = 2; i <= x; i++){
                $("#inventory_name_"+i).hide();
                $("#image_upload_form_"+i).hide();
                $("#name"+i).attr('required',false);
            }
        });

        $("#similar_name_no").click(function( event ) {
            for(var i = 2; i <= x; i++){
                $("#inventory_name_"+i).show();
                $("#image_upload_form_"+i).show();
                $("#name"+i).attr('required',true);
            }
        });

        //add rule form submission
        $("#create-form").submit(function( event ) {
            $("#loading-gif-submit").show();
            //validation for mandatory order number entry
            $(".btn-create-new-inventory").attr('disabled',true);
            var vendor = $("#supplier_select").val();
            if(vendor == ''){
                $("#loading-gif-submit").hide();
                alert("Vendor required");return false;
            }
            var image_1 = $('#uploadFile1-imagePreview').attr('src');
            if(image_1 == undefined){
                $("#loading-gif-submit").hide();
                alert("Image required");
                return false;
            }
            for (i = 2; i <= x; i++) {
                var image = $('#uploadFile'+i+'-imagePreview').attr('src');
                var similar = $("input[type='radio'][name='similar_name']:checked").val();
                if(image == undefined && similar == '0'){
                    $("#loading-gif-submit").hide();
                    alert("Image required");return false;
                }
            } 
        });
    });
</script>

@stop
